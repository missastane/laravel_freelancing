<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;

use App\Http\Requests\Auth\ResendEmailVerificationRequest;
use App\Http\Services\User\UserService;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected UserService $userService)
    {
    }
    /**
     * @OA\Post(
     *     path="/api/email/verification-notification", 
     *     summary="Resend email verification link", 
     *     description="Resends the email verification link to the user if their email is not already verified.", 
     *     operationId="resendVerificationEmail", 
     *     tags={"Authentication"}, 
     *     @OA\RequestBody( 
     *         required=true, 
     *         @OA\JsonContent( 
     *             required={"email"}, 
     *            @OA\Property(property="email", type="string", format="email", example="user@example.com") 
     *         ) 
     *     ),
     *     @OA\Response( 
     *         response=200, 
     *         description="Verification link sent successfully", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example=true), 
     *             @OA\Property(property="message", type="string", example="لینک تأیید ایمیل برای شما ارسال شد") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=400, 
     *         description="Email already verified", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example=false), 
     *             @OA\Property(property="message", type="string", example="ایمیل شما قبلا تأیید شده است") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=404, 
     *         description="User not found", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example=false), 
     *             @OA\Property(property="message", type="string", example="User not found") 
     *         ) 
     *     ) 
     * ) 
     */
    public function resendVerificationEmail(ResendEmailVerificationRequest $request)
    {
        try {
            $bool = $this->userService->resendVerificationEmail($request->all());
            if ($bool) {
                return $this->success(null, 'لینک تأیید ایمیل برای شما ارسال شد');
            } else {
                return $this->error('ایمیل شما قبلا تأیید شده است', 400);
            }
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/email/verify", 
     *     summary="Check email verification status", 
     *     description="Checks whether the authenticated user's email is verified or not.", 
     *     operationId="checkVerificationStatus", 
     *     tags={"Authentication"}, 
     *     security={{ "bearerAuth":{} }}, 
     *     @OA\Response( 
     *         response=200, 
     *         description="Email is already verified", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example=true), 
     *             @OA\Property(property="message", type="string", example="ایمیل شما قبلا تأیید شده است") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=403, 
     *         description="Email is not verified", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example=false), 
     *             @OA\Property(property="message", type="string", example="این ایمیل قبلا تأیید نشده است") 
     *         ) 
     *     ) 
     * ) 
     */
    public function checkVerificationStatus(Request $request)
    {
        $result = $this->userService->checkVerificationStatus($request->user());
        if(!$result['status']){
            return $this->error($result['message'],$result['code']);
        }
        return $this->success(null,$result['message'],$result['code']);
    }

    /**
     * @OA\Get(
     *     path="/api/email/verify/{id}/{hash}",
     *     summary="Verify user email",
     *     description="Verifies the user's email address using the ID and hash provided in the verification link.",
     *     operationId="verifyEmail",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Hashed email token",
     *         @OA\Schema(type="string", example="c4ca4238a0b923820dcc509a6f75849b")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ایمیل شما با موفقیت تأیید شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="این ایمیل قبلا تأیید شده است")
     *         )
     *     ),
     *   @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid verification link",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="تأیید ایمیل معتبر نیست")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $result = $this->userService->verifyEmail($request->all(), $id, $hash);
        if ($result['status']) {
            return $this->success($result['data'], $result['message'], $result['code']);
        }
        return $this->error($result['message'],$result['code']);
    }
}
