<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Subscription\Http\Resources\SubscriptionHistoryResource;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use OpenApi\Annotations as OA;

class SubscriptionHistoryController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/subscription/histories",
     *      tags={"Subscription"},
     *      summary="Get user subscription histories",
     *      description="Returns the authenticated user's subscription histories.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubscriptionHistoryResource"))
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $histories = SubscriptionHistory::with(['plan', 'method'])
            ->where('billable_id', $request->user()->id)
            ->where('billable_type', get_class($request->user()))
            ->paginate($this->getLimitRequest());

        return $this->restSuccess($histories);
    }
}
