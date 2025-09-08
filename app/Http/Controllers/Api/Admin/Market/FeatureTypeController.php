<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\FeatureTypeStoreRequest;
use App\Http\Requests\Admin\Market\FeatureTypeUpdateRequest;
use App\Http\Services\FeatureType\FeatureTypeService;
use App\Models\Market\FeatureType;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class FeatureTypeController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected FeatureTypeService $featureTypeService)
    {
    }
     /**
     * @OA\Get(
     *     path="/api/admin/market/feature",
     *     summary="Retrieve list of FeatureType",
     *     description="Retrieve list of all `FeatureType`",
     *     tags={"FeatureType"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of FeatureType",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                             @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="name", type="string", example="highlight-proposal"),
     *                         @OA\Property(property="display_name", type="string", example="برجسته کردن پیشنهاد"),
     *                         @OA\Property(property="description", type="string", example="توضیح برجسته کردن پیشنهاد"),
     *                         @OA\Property(property="target_type", type="string", example="project"),
     *                         @OA\Property(property="price", type="decimal", example=20000),
     *                         @OA\Property(property="duration_days", type="integer", example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                         @OA\Property(property="is_active_value", type="string", example="فعال"),
     *                    )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/admin/user/customer"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=4)
     *             ),
     *             @OA\Property(property="total", type="integer", example=4),
     *             @OA\Property(property="last_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example=null)
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/admin/user/customer"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=4),
     *                 @OA\Property(property="total", type="integer", example=4)
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
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
        return $this->success($this->featureTypeService->getFeatures());
    }

     /**
     * @OA\Post(
     *     path="/api/admin/market/feature/store",
     *     summary="create new FeatureType",
     *     description="this method creates a new `FeatureType` and stores it",
     *     tags={"FeatureType"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", description="This field can only contain English letters and numbers, and hyphens (-). Any other characters will result in a validation error.", example="highlight-proposal"),
     *             @OA\Property(property="display_name", type="string", example="برجسته کردن پیشنهاد"),
     *             @OA\Property(property="description", type="string", example="توضیح برجسته کردن پیشنهاد"),
     *             @OA\Property(property="target_type", type="string", enum={"project","proposal"}, example="project"),
     *             @OA\Property(property="price", type="decimal", example=20000),
     *             @OA\Property(property="duration_days", type="integer", example=null),
     *             @OA\Property(property="is_active", type="integer", enum={1,2}, description="1 => active, 2 => disactive", example=1),
     *                       ),
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful FeatureType creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="ویژگی با موفقیت ثبت شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *          )
     *     ),
     *      @OA\Response(
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
     *     @OA\Response(
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
     *     )
     *)
     * )
     */
    public function store(FeatureTypeStoreRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->featureTypeService->store($inputs);
            return $this->success(null, 'ویژگی با موفقیت ثبت شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

     /**
     * @OA\Get(
     *     path="/api/admin/market/feature/show/{featureType}",
     *     summary="Get details of a specific FeatureType",
     *     description="Returns the `FeatureType` details and provide details for edit method.",
     *     tags={"FeatureType", "FeatureType/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="featureType",
     *         in="path",
     *         description="ID of the FeatureType to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched FeatureType details for editing",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="name", type="string", example="highlight-proposal"),
     *                 @OA\Property(property="display_name", type="string", example="برجسته کردن پیشنهاد"),
     *                 @OA\Property(property="description", type="string", example="توضیح برجسته کردن پیشنهاد"),
     *                 @OA\Property(property="target_type", type="string", example="project"),
     *                 @OA\Property(property="price", type="decimal", example=20000),
     *                 @OA\Property(property="duration_days", type="integer", example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                 @OA\Property(property="is_active_value", type="string", example="فعال"),          
     *           )
     *       )
     *   ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(FeatureType $featureType)
    {
        return $this->success($this->featureTypeService->show($featureType));
    }

     /**
     * @OA\Put(
     *     path="/api/admin/market/feature/update/{featureType}",
     *     summary="update an existing FeatureType",
     *     description="this method Updates an existing `FeatureType`",
     *     tags={"FeatureType"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="featureType",
     *         in="path",
     *         description="ID of the FeatureType to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="name", type="string", description="This field can only contain English letters and numbers, and hyphens (-). Any other characters will result in a validation error.", example="highlight-proposal"),
     *             @OA\Property(property="display_name", type="string", example="برجسته کردن پیشنهاد"),
     *             @OA\Property(property="description", type="string", example="توضیح برجسته کردن پیشنهاد"),
     *             @OA\Property(property="target_type", type="string", enum={"project","proposal"}, example="project"),
     *             @OA\Property(property="price", type="decimal", example=20000),
     *             @OA\Property(property="duration_days", type="integer", example=null),
     *             @OA\Property(property="is_active", type="integer", enum={1,2}, description="1 => active, 2 => disactive", example=1),
     *                       ),
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful FeatureType update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="ویژگی با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *          )
     *     ),
     *      @OA\Response(
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
     *     @OA\Response(
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
     *     )
     *)
     * )
     */
    public function update(FeatureType $featureType, FeatureTypeUpdateRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->featureTypeService->update($featureType, $inputs);
            return $this->success(null, 'ویژگی با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

      /**
     * @OA\Delete(
     *     path="/api/admin/market/feature/delete/{featureType}",
     *     summary="Delete a FeatureType",
     *     description="This endpoint allows the user to `delete an existing FeatureType`.",
     *     operationId="deleteFeatureType",
     *     tags={"FeatureType"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="featureType",
     *         in="path",
     *         description="The ID of the FeatureType to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FeatureType deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ویژگی با موفقیت حذف شد"),
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
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function delete(FeatureType $featureType)
    {
        try {
            $this->featureTypeService->delete($featureType);
            return $this->success(null, 'ویژگی با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
