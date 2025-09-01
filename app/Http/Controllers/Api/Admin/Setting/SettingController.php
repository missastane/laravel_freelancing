<?php

namespace App\Http\Controllers\Api\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Http\Services\Image\ImageService;
use App\Models\Content\Tag;
use App\Models\Setting\Setting;
use App\Traits\ApiResponseTrait;
use Database\Seeders\SettingSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use ApiResponseTrait;
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
        $setting = Setting::first(); {
            $default = new SettingSeeder();
            $default->run();
            $setting = Setting::first();
        }
        return response()->json([
            'data' => $setting->load('keywords')
        ], 200);
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
    public function update(SettingRequest $request, ImageService $imageService)
    {
        try {
            DB::beginTransaction();
            $setting = Setting::first();
            $inputs = $request->all();
            if ($request->hasFile('icon')) {

                if (!empty($setting->icon)) {
                    $imageService->deleteImage($setting->icon);
                }

                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'setting');
                $imageService->setImageName('icon');
                $icon = $imageService->save($request->file('icon'));

                if ($icon === false) {
                    return $this->error('بارگذاری آیکن با خطا مواجه شد', 422);
                }
                $inputs['icon'] = $icon;
            }
            if ($request->hasFile('logo')) {
                if (!empty($setting->logo)) {
                    $imageService->deleteImage($setting->logo);
                }
                $imageService->setExclusiveDirectory('images' . DIRECTORY_SEPARATOR . 'setting');
                $imageService->setImageName('logo');
                $logo = $imageService->save($request->file('logo'));
                
                if ($logo === false) {
                    return $this->error('بارگذاری لوگو با خطا مواجه شد', 422);
                }
                $inputs['logo'] = $logo;
            }
            $setting->update($inputs);
            if ($request->has('keywords')) {
                $keywordIds = [];
                foreach ($request->keywords as $keywordName) {
                    $keyword = Tag::firstOrCreate(['name' => $keywordName]);
                    $keywordIds[] = $keyword->id;
                }

                $setting->keywords()->sync($keywordIds);
            }
            DB::commit();
            return $this->success(null,'تنظیمات با موفقیت بروزرسانی شد');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error();
        }
    }
}
