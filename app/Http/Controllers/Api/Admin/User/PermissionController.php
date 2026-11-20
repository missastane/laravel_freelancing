<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\PermissionRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\Permission\PermissionService;
use App\Models\User\Permission;
use App\Traits\ApiResponseTrait;
use Exception;
use OpenApi\Attributes as OA;

class PermissionController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected PermissionService $permissionService)
    {
    }
    #[OA\Get(
        path: '/api/admin/user/permission',
        summary: 'List all permissions',
        description: 'Retrieve a paginated list of all permissions available in the system.',
        operationId: 'listPermissions',
        tags: ['Permission'],
        security: [['bearerAuth' => []]],
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
                description: 'Paginated list of permissions',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/PermissionSchema'
                                    )
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
        ]
    )]
    public function index(SearchRequest $request)
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->validated('search');
        $permissions = $this->permissionService->getPermissions($perPage, $search);
        return $this->success($permissions);
    }

    #[OA\Get(
        path: '/api/admin/user/permission/show/{permission}',
        summary: 'Show a specific permission',
        description: 'Retrieve the details of a specific permission along with the roles which have this permission.',
        operationId: 'showPermission',
        tags: ['Permission', 'Permission/Form'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'permission',
                in: 'path',
                required: true,
                description: 'ID of the permission to retrieve',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permission details with assigned roles',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/PermissionSchema'
                                    )
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
                description: 'Permission not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function show(Permission $permission)
    {
        $permission = $this->permissionService->showPermission($permission);

        return $this->success($permission);
    }

    #[OA\Post(
        path: '/api/admin/user/permission/store',
        summary: 'Create a new permission',
        description: "Store a new permission with the given name and assign the 'api' guard.",
        operationId: 'storePermission',
        tags: ['Permission'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/PermissionStoreRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Permission created successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/PermissionSchema'
                                    )
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
    public function store(PermissionRequest $request)
    {
        try {
            $permission = $this->permissionService->storePermission($request->all());

            return $this->success($permission, __('messages.permission_created'), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Patch(
        path: '/api/admin/user/permission/update/{permission}',
        summary: 'Update an existing permission',
        description: 'Update the name of an existing permission by its ID.',
        operationId: 'updatePermission',
        tags: ['Permission'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'permission',
                in: 'path',
                required: true,
                description: 'ID of the permission to update',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/PermissionStoreRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permission updated successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/PermissionSchema'
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
                description: 'Permission not found',
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
    public function update(Permission $permission, PermissionRequest $request)
    {
        try {
            $permission = $this->permissionService->updatePermission($permission, $request->all());

            return $this->success($permission, __('messages.permission_updated'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/admin/user/permission/sync-roles/{permission}',
        summary: 'Sync roles to a specific permission',
        description: 'Replace all roles associated with the given permission by the provided list of role IDs.',
        operationId: 'syncPermissionToRoles',
        tags: ['Permission'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'permission',
                in: 'path',
                required: true,
                description: 'The ID of the permission',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/PermissionSyncRolesRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permission successfully synced to the provided roles',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    allOf: [
                                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                                        new OA\Schema(
                                            properties: [
                                                new OA\Property(
                                                    property: 'data',
                                                    ref: '#/components/schemas/PermissionSchema'
                                                ),
                                            ]
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
                description: 'You are not authorized to do this action.',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Permission not found',
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
    public function syncPermissionToRoles(Permission $permission, PermissionRequest $request)
    {
        try {
            $permission = $this->permissionService->syncPermissionToRoles($permission, $request->all());

            return $this->success(
                $permission->fresh('roles'),
                __('messages.roles_synced_permission')
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/admin/user/permission/delete/{permission}',
        summary: 'Delete a permission',
        description: 'Delete a permission and detach all its related users and roles.',
        operationId: 'deletePermission',
        tags: ['Permission'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'permission',
                in: 'path',
                required: true,
                description: 'ID of the permission to delete',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permission and its relationships deleted successfully',
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
                description: 'You are not authorized to do this action.',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Permission not found',
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
    public function delete(Permission $permission)
    {
        try {
            $this->permissionService->deletePermission($permission);

            return $this->success(
                null,
                __('messages.permission_deleted')
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
