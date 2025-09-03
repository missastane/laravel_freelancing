<?php

namespace App\Http\Controllers\Api\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Http\Services\Setting\SettingService;
use App\Traits\ApiResponseTrait;
use Exception;

class SettingController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected SettingService $settingService)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/setting",
     *     summary="Retrieve Setting Details",
     *     description="Retrieve `Setting Detail`",
     *     tags={"Setting"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Details Of site Setting",
     *        @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Setting"
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
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     )
     * )
     */
    public function index()
    {
        return $this->success($this->settingService->getSetting());
    }

    /**
     * @OA\Post(
     *     path="/api/admin/setting/update",
     *     summary="Create an new or Update an existing Setting",
     *     description="this method Create an new or update an existing `Setting` and stores it.",
     *     tags={"Setting"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\,]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-.,). Any other characters will result in a validation error.", example="آمازون"),
     *             @OA\Property(property="description", type="string", example="توضیحات آمازون"),
     *             @OA\Property(property="icon", type="string", format="binary"),
     *             @OA\Property(property="logo", type="string", format="binary"),
     *             @OA\Property(
     *                 property="keywords[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="آیا api خوب است؟"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *                       ),
     *             encoding={
     *                 "keywords[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Setting update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تنظیمات با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     * @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     ),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *    @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="فیلد وارد شده نامعتبر است"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="x", type="array",
     *                     @OA\Items(type="string", example="فیلد x اجباری است")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function update(SettingRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->settingService->update($inputs);
            return $this->success(null, 'تنظیمات با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
