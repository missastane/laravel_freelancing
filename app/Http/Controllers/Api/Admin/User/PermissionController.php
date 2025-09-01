<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\PermissionRequest;
use App\Http\Services\Permission\PermissionService;
use App\Models\User\Permission;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected PermissionService $permissionService)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/permission",
     *     summary="List all permissions",
     *     description="Retrieve a paginated list of all permissions available in the system.",
     *     tags={"Permission"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="edit-posts"),
     *                         @OA\Property(property="guard_name", type="string", example="api"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             )
     *         )
     *     ),
     *   @OA\Response(
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
     *         )
     *     )
     * )
     */
    public function index()
    {
        $permissions = $this->permissionService->getPermissions();
        return $this->success($permissions);
    }
    /**
     * @OA\Get(
     *     path="/api/admin/user/permission/show/{permission}",
     *     summary="Show a specific permission",
     *     description="Retrieve the details of a specific permission along with the users who have this permission.",
     *     tags={"Permission", "Permission/Form"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         description="ID of the permission to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permission details with assigned users",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="edit-posts"),
     *                 @OA\Property(property="guard_name", type="string", example="api"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                 @OA\Property(property="users", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=10),
     *                         @OA\Property(property="username", type="string", example="John003"),
     *                     )
     *                 )
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
     *     ))
     * )
     */
    public function show(Permission $permission)
    {
        $permission = $this->permissionService->
            showPermission($permission);
        return $this->success($permission);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/user/permission/store",
     *     summary="Create a new permission",
     *     description="Store a new permission with the given name and assign the 'api' guard.",
     *     tags={"Permission"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="only english alphabet with (-,. )", example="edit-comments")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="سطح دسترسی با موفقیت ایجاد شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
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
     *             @OA\Property(property="message", type="string", example="شما مجوز انجام این عملیات را ندارید")
     *         )
     *     ),
     *       @OA\Response(
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
     *      @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */

    public function store(PermissionRequest $request)
    {
        try {
            $this->permissionService->storePermission($request->all());
            return $this->success(null, 'سطح دسترسی با موفقیت ایجاد شد', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * @OA\Patch(
     *     path="/api/admin/user/permission/update/{permission}",
     *     summary="Update an existing permission",
     *     description="Update the name of an existing permission by its ID.",
     *     tags={"Permission"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         description="ID of the permission to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="only english alphabet with (-,. )", example="delete-posts")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="سطح دسترسی با موفقیت بروزرسانی شد"),
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
     *             @OA\Property(property="message", type="string", example="شما مجوز انجام این عملیات را ندارید")
     *         )
     *     ),
     *   
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
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function update(Permission $permission, PermissionRequest $request)
    {
        try {
            $this->permissionService->updatePermission($permission, $request->all());
            return $this->success(null, 'سطح دسترسی با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/user/permission/sync-roles/{permission}",
     *     summary="Sync roles to a specific permission",
     *     description="Replace all roles associated with the given permission by the provided list of role IDs.",
     *     tags={"Permission"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         description="The ID of the permission",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission successfully synced to the provided roles",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="سطح دسترسی به نقش های وارد شده اختصاص داده شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
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
     *         description="Forbidden - You are not authorized as an admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="شما اجازه انجام این عملیات را ندارید")
     *     )),
     *    @OA\Response(
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
     *         description="Internal server error",
     *             @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *           )
     *     )
     * )
     */
    public function syncPermissionToRoles(Permission $permission, PermissionRequest $request)
    {
        try {
            $this->permissionService->syncPermissionToRoles($permission, $request->all());
            return $this->success(null, 'سطح دسترسی به نقش های وارد شده اختصاص داده شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/user/permission/delete/{permission}",
     *     summary="Delete a permission",
     *     description="Delete a permission and detach all its related users and roles.",
     *     tags={"Permission"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         description="ID of the permission to delete",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permission and its relationships deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="سطوح دسترسی و وابستگی های آن با موفقیت حذف شدند"),
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
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجوز انجام این عملیات را ندارید")
     *         )
     *     ),
     *    
     *      @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *      @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function delete(Permission $permission)
    {
        try {
            $this->permissionService->deletePermission($permission);
            return $this->success(null, 'سطوح دسترسی و وابستگی های آن با موفقیت حذف شدند');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
