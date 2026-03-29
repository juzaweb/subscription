<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Subscription\Http\Resources\SubscriptionMethodResource;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;
use OpenApi\Annotations as OA;

class SubscriptionMethodController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/subscription/methods",
     *      tags={"Subscription"},
     *      summary="Get active subscription methods",
     *      description="Returns active subscription methods.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubscriptionMethodResource"))
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $methods = SubscriptionMethod::where('active', true)->paginate($this->getLimitRequest());

        return $this->restSuccess($methods);
    }
}
