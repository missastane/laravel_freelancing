<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\RejectFileItemRequest;
use App\Http\Requests\Employer\RevisionFileItemRequest;
use App\Http\Services\FinalFile\FinalFileService;
use App\Models\Market\FinalFile;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class FinalFileController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected FinalFileService $finalFileService,
    ) {
    }
    /**
     * @OA\Put(
     *     path="/api/final-file/approve/{finalFile}",
     *     summary="Approve final submitted file",
     *     description="This endpoint is used by the client to approve the final file submitted by the freelancer. Upon approval, the orderItem status will be marked as `approved`. The project payment will also be released to the freelancer.",
     *     tags={"Customer-FinalFiles","Customer-Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="finalFile",
     *         in="path",
     *         required=true,
     *         description="The ID of the Final File",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FinalFile approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="این مرحله با موفقیت تایید و مبلغ آن برای فریلنسر آزاد شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
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
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *   @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function approveFileItem(FinalFile $finalFile)
    {
        try {
            $this->finalFileService->approveFileItem($finalFile);
            return $this->success(null, 'این مرحله با موفقیت تایید و مبلغ آن برای فریلنسر آزاد شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Put(
     *     path="/api/final-file/reject/{finalFile}",
     *     summary="Reject final submitted file",
     *     description="This endpoint is used by the client to reject the final file submitted by the freelancer. Upon approval, the orderItem status will be marked as `locked` and created a dispute request. The project will be locked until admin arbitration",
     *     tags={"Customer-FinalFiles","Customer-Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="finalFile",
     *         in="path",
     *         required=true,
     *         description="The ID of the Final File",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FinalFile locked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فایل سفارش توسط کارفرما قفل شد و اختلاف نظر ثبت شد. لطفا منتظر نتیجه داوری بمانید"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
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
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *   @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function rejectFileItem(FinalFile $finalFile, RejectFileItemRequest $request)
    {
        try {
            $this->finalFileService->rejectFileItem($finalFile, $request->all());
            return $this->success(null, 'فایل سفارش توسط کارفرما قفل شد و اختلاف نظر ثبت شد. لطفا منتظر نتیجه داوری بمانید');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Put(
     *     path="/api/final-file/revision/{finalFile}",
     *     summary="Revision final submitted file",
     *     description="This endpoint is used by the client to revision the final file submitted by the freelancer. Upon revision, the FinalFile status will be marked as `revision` and created a dispute request. The project will be continued by freelancer",
     *     tags={"Customer-FinalFiles","Customer-Order"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="finalFile",
     *         in="path",
     *         required=true,
     *         description="The ID of the Final File",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FinalFile revisioned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت جهت بازبینی فریلنسر ارجاع شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
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
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *   @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function revisionFileItem(FinalFile $finalFile, RevisionFileItemRequest $request)
    {
        try {
            $this->finalFileService->revisionFileItem($finalFile, $request->all());
            return $this->success(null, 'فایل با موفقیت جهت بازبینی فریلنسر ارجاع شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
