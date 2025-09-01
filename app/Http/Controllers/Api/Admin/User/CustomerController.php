<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CustomerRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Services\User\CustomerService;
use App\Models\User\User;
use App\Repositories\Contracts\User\CustomerRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected CustomerService $customerService,
    ) {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/customer",
     *     summary="Retrieve list of Customers",
     *     description="Retrieve list of all `Customers`",
     *     tags={"Customer"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="List Of Customer With Pagination Details",
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
     *     ),
     *     @OA\Response(
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
     *         ),
     *     ),
     *  )
     */
    public function index()
    {
        $customers = $this->customerService->getCustomers(null);
        return $customers;
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/search",
     *     summary="Searchs among Customers by first name or last name",
     *     description="This endpoint allows users to search for `Customers` by first name or last name. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Customer"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Type first name or last name of Customer which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Customers",
     *           @OA\JsonContent(
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
     *     ),
     *       @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *       @OA\Response(
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

    public function search(SearchRequest $request)
    {
        $customers = $this->customerService->searchCustomers($request->search, null);
        return $customers;
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/show/{customer}",
     *     summary="Get details of a specific Customer",
     *     description="Returns the `Customer` details and provide details for edit method.",
     *     operationId="getCustomerDetails",
     *     tags={"Customer", "Customer/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="ID of the Customer to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Customer details for editing",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(
     *                     property="data",
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
     *                   )
     *             )
     *     ),
     * @OA\Response(
     *         response=400,
     *         description="Bad Request - customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *     )),
     *  @OA\Response(
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
     *     ),
     *    @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * ) 
     */
    public function show(User $customer)
    {
        $this->customerService->showCustomer($customer);
        return $this->success($customer);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/customer/store",
     *     summary="Create new Customer",
     *     description="This method creates a new `Customer` and stores it.",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *                 @OA\Property(property="email", type="string", example="example@gmail.com"),
     *                 @OA\Property(property="activation", type="integer", enum={1, 2}, description="1 = active, 2 = inactive", example=1),
     *                 @OA\Property(property="role", type="integer", enum={1,2}, description="1 = employer, 2 = freelancer", example=1),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Customer creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="لینک تغییر رمز عبور با موفقیت ارسال شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     * 
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
     *          )
     *      )
     *  )
     */
    public function store(CustomerRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->customerService->storeNewCustomer($inputs);
            return $this->success(null, 'لینک تغییر رمز عبور به ایمیل ارسال شد', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/user/customer/activation/{customer}",
     *     summary="Change the activation of a Customer",
     *     description="This endpoint `toggles the activation of a Customer` (active/inactive)",
     *     operationId="updateCustomerActivation",
     *     security={{"bearerAuth": {}}},
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="Customer id to change the activation",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Customer activation state updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت فعال شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *     )),
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
    public function activation(User $customer)
    {
        try {
            $message = $this->customerService->toggleActivation($customer);
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
     *     path="/api/admin/user/customer/update/{customer}",
     *     summary="Update an existing Customer",
     *     description="This method Update an existing `Customer` and stores it.",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="Customer id to fetch",
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
     *         description="successful Customer update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Bad Request - customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *     )),
     *      @OA\Response(
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
     *     ),
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
    public function update(CustomerRequest $request, User $customer)
    {
        try {
            $inputs = $request->all();
            $this->customerService->update($customer, $inputs);
            return $this->success(null, 'کاربر با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/user/customer/delete/{customer}",
     *     summary="Delete a Customer",
     *     description="This endpoint allows the user to `delete an existing Customer`.",
     *     operationId="deleteCustomer",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="The ID of the Customer to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Bad Request - customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *     )),
     *      @OA\Response(
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
    public function delete(User $customer)
    {
        try {
            $this->customerService->delete($customer);
            return $this->success(null, 'کاربر با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/projects/{customer}",
     *     summary="Get projects list of a specific Customer",
     *     description="Returns the `Customer's Projects` details",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="ID of the Customer to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Customer Projects",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data",type="array",
     *               @OA\Items(type="object",ref="#/components/schemas/Project")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *     )),
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
     *    @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     * ) 
     */
    public function getEmployerProjects(User $customer)
    {
        $result = $this->customerService->getEployerProjects($customer);
        return $this->success($result);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/customer/proposals/{customer}",
     *     summary="Get prposals list of a specific Customer",
     *     description="Returns the `Customer's Proposals` details",
     *     tags={"Customer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="ID of the Customer to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Customer Proposals",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data",type="array",
     *               @OA\Items(type="object",ref="#/components/schemas/Proposal")
     *             )
     *        )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - customer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *     )),
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
     * ) 
     */
    public function getFreelancerProposals(User $customer)
    {
        $result = $this->customerService->getFreelancerProposals($customer);
        return $this->success($result);
    }
}
