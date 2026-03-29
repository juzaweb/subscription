<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Facades\Subscription as SubscriptionFacade;
use Juzaweb\Modules\Subscription\Http\Resources\SubscriptionResource;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\Subscription;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;
use OpenApi\Annotations as OA;

class SubscriptionController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/subscription/subscriptions",
     *      tags={"Subscription"},
     *      summary="Get user subscriptions",
     *      description="Returns the authenticated user's subscriptions.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubscriptionResource"))
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with(['plan', 'method'])
            ->where('billable_id', $request->user()->id)
            ->where('billable_type', get_class($request->user()))
            ->paginate($this->getLimitRequest());

        return $this->restSuccess($subscriptions);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/subscription/{module}/subscribe",
     *      tags={"Subscription"},
     *      summary="Create a subscription",
     *      description="Create a subscription for the authenticated user.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="module",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              type="object",
     *              required={"method_id", "plan_id", "token"},
     *
     *              @OA\Property(property="method_id", type="string", example="method_id"),
     *              @OA\Property(property="plan_id", type="string", example="plan_id"),
     *              @OA\Property(property="token", type="string", example="encrypted_token")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Subscription created successfully")
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=422, description="Unprocessable Entity")
     * )
     */
    public function subscribe(Request $request, string $module): JsonResponse
    {
        /** @var SubscriptionMethod $method */
        $method = SubscriptionMethod::withTranslation()->findOrFail($request->input('method_id'));
        $plan = Plan::find($request->input('plan_id'));
        $user = $request->user();
        $token = $request->input('token');

        try {
            $billable = decrypt($token);

            if (! is_array($billable)
                || ! isset($billable['billable_id'], $billable['billable_type'])
            ) {
                throw new \Exception('Invalid bill token');
            }

            $billableId = $billable['billable_id'];
            $billableType = $billable['billable_type'];

            if (! class_exists($billableType)) {
                throw new \Exception('Invalid bill token');
            }

            $billable = $billableType::findOrFail($billableId);
        } catch (\Throwable $e) {
            return $this->restFail(__('Invalid bill token'));
        }

        try {
            $payment = DB::transaction(
                fn () => SubscriptionFacade::create($user, $billable, $module, $plan, $method, $request->all())
            );
        } catch (SubscriptionException $e) {
            return $this->restFail($e->getMessage());
        }

        if ($payment->isRedirect()) {
            if ($method->paymentDriver()->isReturnInEmbed()) {
                return $this->restSuccess(
                    [
                        'type' => 'embed',
                        'embed_url' => $payment->getRedirectUrl(),
                        'payment_history_id' => $payment->getPaymentHistory()->id,
                    ]
                );
            }

            return $this->restSuccess(
                [
                    'type' => 'redirect',
                    'redirect' => $payment->getRedirectUrl(),
                ],
                __('Redirecting to payment gateway...')
            );
        }

        return $this->restSuccess([], __('Subscription created successfully'));
    }

    /**
     * @OA\Get(
     *      path="/api/v1/subscription/{module}/return/{transactionId}",
     *      tags={"Subscription"},
     *      summary="Return after subscription",
     *      description="Return endpoint after subscription payment process.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="module",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="transactionId",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Payment completed successfully!"),
     *              @OA\Property(property="data", type="object", @OA\Property(property="redirect", type="string"))
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function return(Request $request, string $module, string $transactionId): JsonResponse
    {
        $returnUrl = SubscriptionFacade::module($module)->getReturnUrl();

        try {
            $payment = DB::transaction(
                function () use ($request, $transactionId) {
                    $history = SubscriptionHistory::lockForUpdate()->find($transactionId);

                    throw_if($history === null, SubscriptionException::class, __('Subscription not found'));

                    return SubscriptionFacade::complete($history, $request->all());
                }
            );
        } catch (SubscriptionException $e) {
            return $this->restFail($e->getMessage(), 422, ['redirect' => $returnUrl]);
        }

        if ($payment->isSuccessful()) {
            return $this->restSuccess(
                ['redirect' => $returnUrl],
                __('Payment completed successfully!')
            );
        }

        return $this->restFail(__('Payment failed!'), 422, ['redirect' => $returnUrl]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/subscription/{module}/cancel/{transactionId}",
     *      tags={"Subscription"},
     *      summary="Cancel a subscription",
     *      description="Cancel a subscription payment.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="module",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="transactionId",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Subscription cancelled successfully"),
     *              @OA\Property(property="data", type="object", @OA\Property(property="redirect", type="string"))
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function cancel(Request $request, string $module, string $transactionId): JsonResponse
    {
        $returnUrl = SubscriptionFacade::module($module)->getReturnUrl();

        try {
            $payment = DB::transaction(
                function () use ($request, $transactionId) {
                    $history = SubscriptionHistory::lockForUpdate()->find($transactionId);

                    throw_if($history === null, SubscriptionException::class, __('Subscription not found'));

                    return SubscriptionFacade::cancel($history, $request->all());
                }
            );
        } catch (SubscriptionException $e) {
            return $this->restFail($e->getMessage(), 422, ['redirect' => $returnUrl]);
        }

        return $this->restSuccess(
            ['redirect' => $returnUrl],
            __('Subscription cancelled successfully')
        );
    }
}
