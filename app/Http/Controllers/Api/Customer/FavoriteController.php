<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\Favorite\FavoriteService;
use App\Models\User\Favorite;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
   
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

     /**
     * @OA\Get(
     *     path="/api/favorite",
     *     summary="Get a paginated list of auth user's favorites",
     *     description="Returns a list of the auth user's favorites pagination (15 per page).",
     *     tags={"Favorite"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of the auth user's favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object", ref="#/components/schemas/Favorite")
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *    @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجوز انجام این عملیات را ندارید")
     *         )
     *     ),
     * )
     */
    public function index()
    {
       return $this->favoriteService->getUserFavorites();
    }

    
}
