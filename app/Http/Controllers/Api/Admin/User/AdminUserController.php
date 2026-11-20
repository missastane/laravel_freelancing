<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\AdminUserRequest;
use App\Http\Requests\Admin\User\PermissionStoreRequest;
use App\Http\Requests\Admin\User\RoleStoreRequest;
use App\Http\Requests\Admin\User\SyncDepartmentRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Services\User\AdminUserService;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected AdminUserService $adminUserService
    ) {
    }
    #[OA\Get(
        path: "/api/admin/user/admin-user",
        summary: "Retrieve list of Admins",
        description: "Retrieve list of all `Admins`. Search among admins by `first_name`, `last_name`, `username` and `email`",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of items per page',
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 100,
                    example: 15
                )
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "First name or last name to search for",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "A list of Admins",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/AdminUser")
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function index(SearchRequest $request)
    {
        $perPage = $request->integer('per_page', 15);
        $admins = $this->adminUserService->getAdmins($request->validated('search'), $perPage);
        return $this->success($admins, resource: UserResource::class);
    }

    #[OA\Get(
        path: "/api/admin/user/admin-user/show/{admin}",
        summary: "Get details of a specific Admin",
        description: "Returns the `Admin` details and provides data for viewing or editing.",
        operationId: "getAdminDetails",
        tags: ["Admin", "Admin/Form"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "ID of the Admin to fetch",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully fetched Admin details",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - Admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route or Admin not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function show(User $admin)
    {
        $admin = $this->adminUserService->showAdmin($admin);
        return $this->success($admin, resource: UserResource::class);
    }

    #[OA\Get(
        path: "/api/admin/user/admin-user/options",
        summary: "Get necessary options for admin forms",
        description: "This endpoint returns all `Roles` and `Permissions` and `Departments`  to be used for admin forms.",
        tags: ["Admin", "Admin/Form"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully fetched Permissions and Roles and Departments for admin forms",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "roles",
                                            type: "array",
                                            items: new OA\Items(ref: "#/components/schemas/RoleOption")
                                        ),
                                        new OA\Property(
                                            property: "permissions",
                                            type: "array",
                                            items: new OA\Items(ref: "#/components/schemas/PermissionOption")
                                        ),
                                        new OA\Property(
                                            property: "departments",
                                            type: "array",
                                            items: new OA\Items(ref: "#/components/schemas/DepartmentOption")
                                        ),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function options()
    {
        $data = $this->adminUserService->options();
        return $this->success([
            'roles' => $data['roles'],
            'permissions' => $data['permissions'],
            'departments' => $data['departments']
        ]);
    }

    #[OA\Post(
        path: "/api/admin/user/admin-user/store",
        summary: "Create new Admin",
        description: "Creates and stores a new `Admin`.",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    ref: "#/components/schemas/AdminStoreRequestSchema"
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Successful Admin creation",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function store(AdminUserRequest $request)
    {
        try {
            $inputs = $request->all();
            $admin = $this->adminUserService->storeNewAdmin($inputs);
            return $this->success($admin, __('messages.change_password_link_sent'), 201, resource: UserResource::class);
        } catch (Exception $e) {
            return $this->error();
        }
    }
    #[OA\Patch(
        path: "/api/admin/user/admin-user/activation/{admin}",
        summary: "Change the activation of a Admin",
        description: "This endpoint `toggles the activation of a Admin` (active/inactive)",
        operationId: "updateAdminActivation",
        security: [["bearerAuth" => []]],
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "Admin id to change the activation",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Admin activation state updated successfully",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function activation(User $admin)
    {
        try {
            $message = $this->adminUserService->toggleActivation($admin);
            if ($message) {
                return $this->success($admin->fresh(), $message, resource: UserResource::class);
            }
            return $this->error();
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Post(
        path: "/api/admin/user/admin-user/update/{admin}",
        summary: "Update an existing Admin",
        description: "Updates an existing `Admin` and returns the updated details.",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "Admin id to fetch",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    ref: "#/components/schemas/AdminUpdateRequestSchema"
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful Admin update",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function update(AdminUserRequest $request, User $admin)
    {
        try {
            $inputs = $request->validated();
            $this->adminUserService->update($admin, $inputs);
            return $this->success($admin->fresh(), __('messages.user_updated'), resource: UserResource::class);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Delete(
        path: "/api/admin/user/admin-user/delete/{admin}",
        summary: "Delete an Admin",
        description: "This endpoint allows the user to `delete an existing Admin`.",
        operationId: "deleteAdmin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "The ID of the Admin to be deleted",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Admin deleted successfully",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function delete(User $admin)
    {
        try {
            $this->adminUserService->delete($admin);
            return $this->success(null, __('messages.user_deleted'));
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Post(
        path: "/api/admin/user/admin-user/roles/{admin}/store",
        summary: "Update Admin Roles",
        description: "This endpoint assigns new roles to an admin user.",
        operationId: "rolesStore",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "Admin user ID",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/AdminRolesStoreRequestSchema"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Roles successfully updated",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function rolesStore(User $admin, RoleStoreRequest $request)
    {
        if (Gate::denies('assignRole', [$admin])) {
            return $this->error(__('errors.unauthorized'), 403);
        }
        try {
            $this->adminUserService->syncRoles($admin, $request->validated());
            return $this->success($admin->fresh('roles'), __('messages.admin_roles_updated'), resource: UserResource::class);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/api/admin/user/admin-user/permissions/{admin}/store",
        summary: "Update Admin Permissions",
        description: "This endpoint assigns new Permissions to an admin user.",
        operationId: "permissionsStore",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "Admin user ID",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/AdminPermissionsStoreRequestSchema"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Permissions successfully updated",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function permissionsStore(User $admin, PermissionStoreRequest $request)
    {
        if (Gate::denies('assignRole', [$admin])) {
            return $this->error(__('errors.unauthorized'), 403);
        }
        try {
            $this->adminUserService->permissionsStore($admin, $request->validated());
            return $this->success($admin->fresh('permissions'), __('messages.admin_permissions_updated'), resource: UserResource::class);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/api/admin/user/admin-user/departments/{admin}/store",
        summary: "Update Admin Departments",
        description: "This endpoint assigns new Departments to an admin user to manage it.",
        operationId: "departmentsStore",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "admin",
                in: "path",
                description: "Admin user ID",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/AdminDepartmentsStoreRequestSchema"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Departments successfully updated",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    ref: "#/components/schemas/AdminUser"
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request - admin does not exist",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Invalid, expired, revoked, reused, or missing refresh token. Also returned when both refresh token sources are provided.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 403,
                description: "You are not authorized to do this action.",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Route not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Server error",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function departmentsStore(User $admin, SyncDepartmentRequest $request)
    {
        // if (Gate::denies('assignDepartment', [$admin])) {
        //     return $this->error(__('errors.unauthorized'), 403);
        // }
        try {
            $this->adminUserService->syncDepartments($admin, $request->departments);
            return $this->success($admin->fresh('departments'), __('messages.admin_departments_updated'), resource: UserResource::class);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
