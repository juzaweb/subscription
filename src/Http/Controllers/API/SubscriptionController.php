<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Subscription\Http\Resources\SubscriptionResource;
use Juzaweb\Modules\Subscription\Models\Subscription;
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

        return response()->json(
            SubscriptionResource::collection($subscriptions)->response()->getData(true)
        );
    }
}
