<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Subscription\Http\Resources\PlanResource;
use Juzaweb\Modules\Subscription\Models\Plan;
use OpenApi\Annotations as OA;

class PlanController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/subscription/plans",
     *      tags={"Subscription"},
     *      summary="Get active plans",
     *      description="Returns active plans.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PlanResource"))
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $plans = Plan::with(['features'])->where('active', true)->paginate($this->getLimitRequest());

        return $this->restSuccess($plans);
    }
}
