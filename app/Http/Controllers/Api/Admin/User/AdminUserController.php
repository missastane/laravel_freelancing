<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\AdminUserRequest;
use App\Http\Requests\Admin\User\PermissionStoreRequest;
use App\Http\Requests\Admin\User\RoleStoreRequest;
use App\Http\Requests\Admin\User\SyncDepartmentRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\User\AdminUserService;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminUserController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected AdminUserService $adminUserService
    ) {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user",
     *     summary="Retrieve list of Admins",
     *     description="Retrieve list of all `Admins`",
     *     tags={"Admin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Admins",
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
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="username", type="string", example="8uIZLvdTYrtMDWcJ"),
     *                         @OA\Property(property="email", type="string", example="missastan@gmail.com"),
     *                         @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-07-09T07:53:16.000000Z"),
     *                         @OA\Property(property="mobile", type="string", nullable=true, example=null),
     *                         @OA\Property(property="mobile_verified_at", type="string", format="date-time", nullable=true, example=null),
     *                         @OA\Property(property="first_name", type="string", nullable=true, example="ایمان"),
     *                         @OA\Property(property="last_name", type="string", nullable=true, example="مدائنی"),
     *                         @OA\Property(property="gender", type="string", nullable=true, example=null),
     *                         @OA\Property(property="birth_date", type="string", nullable=true, example=null),
     *                         @OA\Property(property="avatar_photo", type="string", nullable=true, example=null),
     *                         @OA\Property(property="activation", type="string", example="کاربر فعال"),
     *                         @OA\Property(property="active_role", type="string", example="employer"),
     *                         @OA\Property(property="about_me", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                         @OA\Property(
     *                             property="roles",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="name", type="string", example="employer")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="permissions",
     *                             type="array",
     *                             @OA\Items(type="string")
     *                         )
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
     *   ),
     * @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     ))
     * )
     */
    public function index()
    {
        $admins = $this->adminUserService->getAdmins(null);
        return $admins;

    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/search",
     *     summary="Searchs among Admins by first name or last name",
     *     description="This endpoint allows users to search for `Admins` by first name or last name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Admin"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="typefirst name or last name of Admin which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Admins",
     *          @OA\JsonContent(
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
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="username", type="string", example="8uIZLvdTYrtMDWcJ"),
     *                         @OA\Property(property="email", type="string", example="missastan@gmail.com"),
     *                         @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-07-09T07:53:16.000000Z"),
     *                         @OA\Property(property="mobile", type="string", nullable=true, example=null),
     *                         @OA\Property(property="mobile_verified_at", type="string", format="date-time", nullable=true, example=null),
     *                         @OA\Property(property="first_name", type="string", nullable=true, example="ایمان"),
     *                         @OA\Property(property="last_name", type="string", nullable=true, example="مدائنی"),
     *                         @OA\Property(property="gender", type="string", nullable=true, example=null),
     *                         @OA\Property(property="birth_date", type="string", nullable=true, example=null),
     *                         @OA\Property(property="avatar_photo", type="string", nullable=true, example=null),
     *                         @OA\Property(property="activation", type="string", example="کاربر فعال"),
     *                         @OA\Property(property="active_role", type="string", example="employer"),
     *                         @OA\Property(property="about_me", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                         @OA\Property(
     *                             property="roles",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="name", type="string", example="employer")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="permissions",
     *                             type="array",
     *                             @OA\Items(type="string")
     *                         )
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
     *   ),
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
     *         )
     *     )
     *     )
     *   
     */
    public function search(SearchRequest $request)
    {
        $admins = $this->adminUserService->searchAdmins($request->search,null);
        return $admins;
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/show/{admin}",
     *     summary="Get details of a specific Admin",
     *     description="Returns the `Admin` details and provide details for edit method.",
     *     operationId="getAdminDetails",
     *     tags={"Admin", "Admin/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="ID of the Admin to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Admin details for editing",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                       allOf={
     *                      @OA\Schema(
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="username", type="string", example="8uIZLvdTYrtMDWcJ"),
     *                         @OA\Property(property="email", type="string", example="missastan@gmail.com"),
     *                         @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-07-09T07:53:16.000000Z"),
     *                         @OA\Property(property="mobile", type="string", nullable=true, example=null),
     *                         @OA\Property(property="mobile_verified_at", type="string", format="date-time", nullable=true, example=null),
     *                         @OA\Property(property="first_name", type="string", nullable=true, example="ایمان"),
     *                         @OA\Property(property="last_name", type="string", nullable=true, example="مدائنی"),
     *                         @OA\Property(property="gender", type="string", nullable=true, example=null),
     *                         @OA\Property(property="birth_date", type="string", nullable=true, example=null),
     *                         @OA\Property(property="avatar_photo", type="string", nullable=true, example=null),
     *                         @OA\Property(property="activation", type="string", example="کاربر فعال"),
     *                         @OA\Property(property="active_role", type="string", example="employer"),
     *                         @OA\Property(property="about_me", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                      ),
     *                          @OA\Schema(
     *                             @OA\Property(property="roles",type="object",
     *                                @OA\Property(property="id", type="integer", example=1),
     *                                @OA\Property(property="name", type="string", example="superadmin")
     *                              )
     *                           ),
     *                          @OA\Schema(
     *                             @OA\Property(property="permissions",type="object",
     *                                @OA\Property(property="id", type="integer", example=4),
     *                                @OA\Property(property="name", type="string", example="delete-post")
     *                              )
     *                           ),
     *                         }
     *                 ),   
     *      )
     *   ),
     *  @OA\Response(
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
     *     )),
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
     *    
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     *     )
     */
    public function show(User $admin)
    {
        $this->adminUserService->showAdmin($admin);
        return $this->success($admin);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/admin-user/options",
     *     summary="Get necessary options for admin forms",
     *     description="This endpoint returns all `Roles` and `Permissions`, which can be used to set role or permission for admins in roleStore and permissionStore methods",
     *     tags={"Admin", "Admin/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Permissions and Roles that you may need to set role or permission for admin forms",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
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
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     ))
     * )
     */
    public function options()
    {
        $data = $this->adminUserService->options();
        return $this->success([
            'roles' => $data['roles'],
            'permissions' => $data['permissions']
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/store",
     *     summary="Create new Admin",
     *     description="This method creates a new `Admin` and stores it.",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="first_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="ایمان"),
     *             @OA\Property(property="last_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="مدائنی"),
     *             @OA\Property(property="email", type="string", example="example@gmail.com"),
     *             @OA\Property(property="mobile", type="string", example="09123654789"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Admin creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="لینک تغییر رمز عبور با موفقیت ارسال شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
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
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
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
    public function store(AdminUserRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->adminUserService->storeNewAdmin($inputs);
            return $this->success(null, 'لینک تغییر رمز عبور به ایمیل ارسال شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }
    /**
     * @OA\Patch(
     *     path="/api/admin/user/admin-user/activation/{admin}",
     *     summary="Change the activation of a Admin",
     *     description="This endpoint `toggles the activation of a Admin` (active/inactive)",
     *     operationId="updateAdminActivation",
     *     security={{"bearerAuth": {}}},
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="Admin id to change the activation",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Admin activation state updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت فعال شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
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
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
     *     )),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
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
    public function activation(User $admin)
    {
        try {
            $message = $this->adminUserService->toggleActivation($admin);
            if ($message) {
                return $this->success(null, $message);
            }
            return $this->error();
        } catch (Exception $e) {
            return $this->error();
        }
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/update/{admin}",
     *     summary="Update an existing Admin",
     *     description="This method Update an existing `Admin` and stores it.",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="Admin id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="first_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="ایمان"),
     *             @OA\Property(property="last_name", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\ ]+$", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="مدائنی"),
     *             @OA\Property(
     *                 property="activation",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(property="_method",type="string",example="PUT"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Admin update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
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
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
     *     )),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
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
     *     )
     *   )
     * )
     */
    public function update(AdminUserRequest $request, User $admin)
    {
        try {
            $inputs = $request->all();
            $this->adminUserService->update($admin, $inputs);
            return $this->success('کاربر با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/user/admin-user/delete/{admin}",
     *     summary="Delete an Admin",
     *     description="This endpoint allows the user to `delete an existing Admin`.",
     *     operationId="deleteAdmin",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         description="The ID of the Admin to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     * @OA\Response(
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
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
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
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
    public function delete(User $admin)
    {
        try {
            $this->adminUserService->delete($admin);
            return $this->success('ادمین با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/roles/{admin}/store",
     *     summary="Update Admin Roles",
     *     description="This endpoint assigns new roles to an admin user.",
     *     operationId="rolesStore",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 description="List of role IDs",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="نقش های ادمین با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     * @OA\Response(
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
     *     )),
     *   @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function rolesStore(User $admin, RoleStoreRequest $request)
    {
        if (Gate::denies('assignRole', [$admin])) {
            return $this->error('اجازه دسترسی ندارید', 403);
        }
        try {
            $this->adminUserService->syncRoles($admin, $request->roles);
            return $this->success(null, 'نقش های ادمین با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/permissions/{admin}/store",
     *     summary="Update Admin Permissions",
     *     description="This endpoint assigns new Permissions to an admin user.",
     *     operationId="PermissionsStore",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 description="List of permission IDs",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسترسی های ادمین با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *    @OA\Response(
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
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
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */

    public function permissionsStore(User $admin, PermissionStoreRequest $request)
    {
        if (Gate::denies('assignRole', [$admin])) {
            return $this->error('اجازه دسترسی ندارید', 403);
        }
        try {
            $this->adminUserService->permissionsStore($admin, $request->permissions);
            return $this->success(null, 'دسترسی های ادمین با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }


     /**
     * @OA\Post(
     *     path="/api/admin/user/admin-user/departments/{admin}/store",
     *     summary="Update Admin Departments",
     *     description="This endpoint assigns new Departments to an admin user to manage it.",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"departments"},
     *             @OA\Property(
     *                 property="departments",
     *                 type="array",
     *                 description="List of department IDs",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departments successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دپارتمان های ادمین با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *    @OA\Response(
     *         response=400,
     *         description="Bad Request - admin does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="ادمین با این مشخصات یافت نشد")
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
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
     public function departmentsStore(User $admin, SyncDepartmentRequest $request)
    {
        // if (Gate::denies('assignDepartment', [$admin])) {
        //     return $this->error('اجازه دسترسی ندارید', 403);
        // }
        try {
            $this->adminUserService->syncDepartments($admin,$request->departments);
            return $this->success(null, 'دپارتمان های ادمین با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
