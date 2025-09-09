<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\WorkExperienceRequest;
use App\Http\Services\User\WorkExperienceService;
use App\Models\Locale\Province;
use App\Models\Market\WorkExperience;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class UserExperienceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected WorkExperienceService $workExperienceService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/user-experience",
     *     summary="Retrieve list of freelancer's Experiences",
     *     description="Retrieve list of `freelancer's Experiences`",
     *     tags={"Customer-Experience"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of freelancer's Experiences",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/UserExperience"
     *                     )
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
     * )
     */
    public function index()
    {
        return $this->workExperienceService->getUserExperiences();
    }

    /**
     * @OA\Post(
     *     path="/api/user-experience/store",
     *     summary="Store a new User Experience by freelancer",
     *     description="In this method freelancers can Store a new User Experience",
     *     tags={"Customer-Experience"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="company_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="دانشگاه تهران"),
     *             @OA\Property(property="position", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="مهندسی کامپیوتر"),
     *             @OA\Property(property="province_id", type="integer", description="this field only accepted ids that exists in provinces table", example=2),
     *             @OA\Property(property="start_year", type="integer", example=1754377390),
     *             @OA\Property(property="end_year", type="integer", example=1754377390),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful User Experience Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="سابقه کاری کاربر با موفقیت ثبت شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
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
     *         )
     *     )
     * )
     */
    public function store(WorkExperienceRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->workExperienceService->storeExperience($inputs);
            return $this->success(null, 'سابقه کاری کاربر با موفقیت ثبت شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user-experience/show/{userExperience}",
     *     summary="Get details of a specific User Experience",
     *     description="Returns the `User Experience` details",
     *     tags={"Customer-Experience","Customer-Experience/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userExperience",
     *         in="path",
     *         description="ID of the User Experience to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched User Experience details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/UserExperience"
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
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(WorkExperience $workExperience)
    {
        return $this->success($this->workExperienceService->showExperience($workExperience));
    }

    /**
     * @OA\Put(
     *     path="/api/user-experience/update/{userExperience}",
     *     summary="Update an existing User Experience by freelancer",
     *     description="In this method freelancers can Update an existing User Experience",
     *     tags={"Customer-Experience"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userExperience",
     *         in="path",
     *         description="ID of the User Experience to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="company_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="دانشگاه تهران"),
     *             @OA\Property(property="position", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="مهندسی کامپیوتر"),
     *             @OA\Property(property="province_id", type="integer", description="this field only accepted ids that exists in provinces table", example=2),
     *             @OA\Property(property="start_year", type="integer", example=1754377390),
     *             @OA\Property(property="end_year", type="integer", example=1754377390),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful User Experience Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="سابقه کاری کاربر با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
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
     *         )
     *     )
     * )
     */
    public function update(WorkExperience $workExperience, WorkExperienceRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->workExperienceService->updateExperience($workExperience,$inputs);
            return $this->success(null, 'سابقه کاری کاربر با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/user-experience/delete/{userExperience}",
     *     summary="Delete a User Experience",
     *     description="This endpoint allows the user to `delete an existing User Experience`.",
     *     tags={"Customer-Experience"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userExperience",
     *         in="path",
     *         description="The ID of the User Experience to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User Experience deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="سابقه کاری کاربر با موفقیت حذف شد"),
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
    public function delete(WorkExperience $workExperience)
    {
        try {
            $this->workExperienceService->deleteExperience($workExperience);
            return $this->success(null, 'سابقه کاری کاربر با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
