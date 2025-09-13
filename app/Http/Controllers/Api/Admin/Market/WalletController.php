<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Services\Payment\WalletService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    
    use ApiResponseTrait;
    public function __construct(protected WalletService $walletService)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/market/wallet",
     *     summary="Get details of a auth admin's Wallet Dtails",
     *     description="Returns the auth admin's `Wallet` details",
     *     tags={"Wallet"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Wallet details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Wallet"
     *             )
     *         )
     *   ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     )
     * )
     */
    public function showWallet()
    {
        return $this->success($this->walletService->showWallet());
    }
}
