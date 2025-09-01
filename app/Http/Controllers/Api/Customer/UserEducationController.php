<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\UserEducationRequest;
use App\Http\Services\User\UserEducationService;
use App\Models\Locale\Province;
use App\Models\Market\UserEducation;
use App\Traits\ApiResponseTrait;
use Exception;

class UserEducationController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected UserEducationService $userEducationService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/user-education",
     *     summary="Retrieve list of freelancer's Educations",
     *     description="Retrieve list of `freelancer's Educations`",
     *     tags={"Customer-Education"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of freelancer's Educations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/UserEducation"
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
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
        return $this->success($this->userEducationService->getUserEducations());
    }

    /**
     * @OA\Get(
     *     path="/api/user-education/options",
     *     summary="Retrieve list of Provinces",
     *     description="Retrieve list of all `Provinces` that used for store and update method",
     *     tags={"Customer-Education","Customer-Education/Form","Customer-Experience","Customer-Experience/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Provinces",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *                 @OA\Property(property="data", type="array",
     *                   @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="تهران")
     *                    )
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
    public function options()
    {
        return $this->success($this->userEducationService->options());
    }

    /**
     * @OA\Post(
     *     path="/api/user-education/store",
     *     summary="Store a new User Education by freelancer",
     *     description="In this method freelancers can Store a new User Education",
     *     tags={"Customer-Education"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="university_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="دانشگاه تهران"),
     *             @OA\Property(property="field_of_study", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="مهندسی کامپیوتر"),
     *             @OA\Property(property="province_id", type="integer", description="this field only accepted ids that exists in provinces table", example=2),
     *             @OA\Property(property="start_year", type="integer", example=1754377390),
     *             @OA\Property(property="end_year", type="integer", example=1754377390),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful User Education Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="سابقه تحصیلات کاربر با موفقیت ثبت شد"),
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
    public function store(UserEducationRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->userEducationService->storeEducation($inputs);
            return $this->success(null, 'سابقه تحصیلات کاربر با موفقیت ثبت شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user-education/show/{userEducation}",
     *     summary="Get details of a specific User Education",
     *     description="Returns the `User Education` details",
     *     tags={"Customer-Education","Customer-Education/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userEducation",
     *         in="path",
     *         description="ID of the User Education to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched User Education details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/UserEducation"
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
    public function show(UserEducation $userEducation)
    {
        return $this->success($this->userEducationService->showEducation($userEducation));
    }

    /**
     * @OA\Put(
     *     path="/api/user-education/update/{userEducation}",
     *     summary="Update an existing User Education by freelancer",
     *     description="In this method freelancers can Update an existing",
     *     tags={"Customer-Education"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userEducation",
     *         in="path",
     *         description="ID of the user Education to fetch",
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
     *             @OA\Property(property="university_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="دانشگاه تهران"),
     *             @OA\Property(property="field_of_study", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="مهندسی کامپیوتر"),
     *             @OA\Property(property="province_id", type="integer", description="this field only accepted ids that exists in provinces table", example=2),
     *             @OA\Property(property="start_year", type="integer", example=1754377390),
     *             @OA\Property(property="end_year", type="integer", example=1754377390),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful User Education Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="سابقه تحصیلات کاربر با موفقیت بروزرسانی شد"),
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
     *      @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
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
    public function update(UserEducation $userEducation, UserEducationRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->userEducationService->updateEducation($userEducation, $inputs);
            return $this->success(null, 'سابقه تحصیلات کاربر با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/user-education/delete/{userEducation}",
     *     summary="Delete a User Education",
     *     description="This endpoint allows the user to `delete an existing User Education`.",
     *     tags={"Customer-Education"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userEducation",
     *         in="path",
     *         description="The ID of the User Education to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User Education deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="سابقه تحصیلات کاربر با موفقیت حذف شد"),
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
    public function delete(UserEducation $userEducation)
    {
        try {
            $this->userEducationService->deleteEducation($userEducation);
            return $this->success(null, 'سابقه تحصیلات کاربر با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
