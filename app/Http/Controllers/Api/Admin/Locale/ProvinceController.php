<?php

namespace App\Http\Controllers\Api\Admin\Locale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Locale\ProvinceRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\Province\ProvinceService;
use App\Models\Locale\Province;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProvinceController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected ProvinceService $provinceService)
    {
    }

    #[OA\Get(
        path: '/api/admin/locale/province',
        summary: 'Retrieve list of Provinces',
        description: 'Retrieve list of all `Provinces`',
        operationId: 'listProvinces',
        tags: ['Province'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', example: 1)
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
                description: 'A list of Provinces',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/ProvinceSchema'
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
        $provinces = $this->provinceService->getProvinces($perPage, $search);
        return $this->success($provinces);
    }

    #[OA\Post(
        path: '/api/admin/locale/province/store',
        summary: 'Create new Province',
        description: 'This method creates a new `Province`.',
        operationId: 'storeProvince',
        tags: ['Province'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ProvinceRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful Province creation',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/ProvinceSchema'
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
                    ref: '#/components/schemas/ErrorResponse'
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
    public function store(ProvinceRequest $request)
    {
        try {
            $inputs = $request->all();
            $province = $this->provinceService->storeProvince($inputs);
            return $this->success($province, __('messages.province_created'), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/admin/locale/province/show/{province}',
        summary: 'Get details of a specific Province',
        description: 'Returns the `Province` details and provides details for the edit method.',
        operationId: 'showProvince',
        tags: ['Province', 'Province/Form'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'province',
                in: 'path',
                required: true,
                description: 'ID of the Province to fetch',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully fetched Province details for editing',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/ProvinceSchema'
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
                description: 'Province not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function show(Province $province)
    {
        return $this->success($this->provinceService->showProvince($province));
    }

    #[OA\Put(
        path: '/api/admin/locale/province/update/{province}',
        summary: 'Updates an existing Province',
        description: 'This method updates an existing `Province`.',
        operationId: 'updateProvince',
        tags: ['Province'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'province',
                in: 'path',
                required: true,
                description: 'The ID of the Province to be updated',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ProvinceRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful Province update',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/ProvinceSchema'
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
                description: 'Province not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
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
    public function update(ProvinceRequest $request, Province $province)
    {
        try {
            $inputs = $request->all();
            $this->provinceService->updateProvince($province, $inputs);

            return $this->success($province, __('messages.province_updated'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/admin/locale/province/delete/{province}',
        summary: 'Delete a Province',
        description: 'This endpoint allows the user to `delete an existing Province`.',
        operationId: 'deleteProvince',
        tags: ['Province'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'province',
                in: 'path',
                required: true,
                description: 'The ID of the Province to be deleted',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Province deleted successfully',
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
                description: 'Province not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
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
    public function delete(Province $province)
    {
        try {
            $this->provinceService->deleteProvince($province);

            return $this->success(null, __('messages.province_deleted'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
