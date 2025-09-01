<?php

namespace App\Http\Controllers\Api\Customer;

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
     *     path="/api/wallet",
     *     summary="Get details of a auth user's Wallet Dtails",
     *     description="Returns the auth user's `Wallet` details",
     *     tags={"Customer-Wallet"},
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
     *     ))
     * )
     */
    public function showWallet()
    {
        return $this->success($this->walletService->showWallet());
    }
}
