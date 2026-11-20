<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\RoleRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\Role\RoleService;
use App\Models\User\Role;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class RoleController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected RoleService $roleService)
    {
    }

    #[OA\Get(
        path: "/api/admin/user/role",
        summary: "Get a paginated list of all roles",
        description: "Returns a list of roles with pagination (15 per page).",
        operationId: "getRolesList",
        tags: ["Role"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number for pagination',
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 15)
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search by name',
                schema: new OA\Schema(type: 'string', minimum: 1, example: null)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of roles",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/RoleSchema")
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
    public function index(SearchRequest $request)
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->validated('search');
        $roles = $this->roleService->getRoles($perPage, $search);
        return $this->success($roles);
    }

    #[OA\Get(
        path: '/api/admin/user/role/show/{role}',
        summary: 'Get a specific role',
        description: 'Returns detailed information about a specific role.',
        operationId: 'showRole',
        tags: ['Role', 'Role/Form'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'path',
                required: true,
                description: 'Role ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Role details',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/RoleSchema'
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
                description: 'You are not authorized to do this action.',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Role not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function show(Role $role)
    {
        $role = $this->roleService->showRole($role);

        return $this->success($role);
    }

    #[OA\Post(
        path: '/api/admin/user/role/store',
        summary: 'Create a new role',
        description: "Creates a new role with a name and assigns it to the 'api' guard.",
        operationId: 'storeRole',
        tags: ['Role'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/RoleStoreRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Role created successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/RoleSchema'
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
                description: 'You are not authorized to do this action.',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ValidationErrorResponse'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function store(RoleRequest $request)
    {
        try {
            $role = $this->roleService->storeRole($request->all());
            return $this->success($role, __('messages.role_created'), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Patch(
        path: '/api/admin/user/role/update/{role}',
        summary: 'Update an existing role',
        description: 'Updates the name of an existing role. Only the name is updatable.',
        operationId: 'updateRole',
        tags: ['Role'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'path',
                required: true,
                description: 'ID of the role to be updated',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/RoleStoreRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Role updated successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/RoleSchema'
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
                description: 'You are not authorized to do this action.',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Role not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ValidationErrorResponse'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function update(Role $role, RoleRequest $request)
    {
        try {
            $role = $this->roleService->updateRole($role, $request->all());

            return $this->success($role, __('messages.role_updated'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/admin/user/role/sync-permissions/{role}',
        summary: 'Sync permissions to a specific role',
        description: 'Replace all permissions associated with the given role by the provided list of permission IDs.',
        operationId: 'syncPermissionsToRole',
        tags: ['Role'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'path',
                required: true,
                description: 'The ID of the role',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/RoleSyncPermissionsRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permissions successfully synced to the role',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/RoleSchema'
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
                description: 'You are not authorized to do this action.',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Role not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ValidationErrorResponse'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function syncPermissionsToRole(Role $role, RoleRequest $request)
    {
        try {
            $this->roleService->syncPermissionsToRole($role, $request->all());

            return $this->success(
                $role,
                __('messages.permissions_synced_role')
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/admin/user/role/delete/{role}',
        summary: 'Delete a role',
        description: 'Deletes a role and detaches all associated users and permissions. The admin role cannot be deleted.',
        operationId: 'deleteRole',
        tags: ['Role'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'path',
                required: true,
                description: 'ID of the role to be deleted',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Role and its associations deleted successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
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
                description: 'Admin role cannot be deleted',
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Role not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function delete(Role $role)
    {
        try {
            if (Gate::denies('delete', $role)) {
                return $this->error(__('errors.admin_role_can_not_be_deleted'), 403);
            }

            $this->roleService->deleteRole($role);

            return $this->success(
                null,
                __('messages.role_deleted')
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
