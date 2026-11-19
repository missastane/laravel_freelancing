<?php

namespace App\Http\Controllers\Api\Admin\Locale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Locale\CityRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\City\CityService;
use App\Models\Locale\City;
use App\Models\Locale\Province;
use App\Traits\ApiResponseTrait;
use Exception;
use OpenApi\Attributes as OA;


class CityController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected CityService $cityService)
    {
    }
    #[OA\Get(
        path: '/api/admin/locale/city/{province}',
        summary: 'Retrieve list of all Cities of a province',
        description: 'Retrieve list of all `Cities` of a province.',
        operationId: 'listCities',
        tags: ['City'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'province',
                in: 'path',
                required: true,
                description: 'Province ID to fetch its Cities',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
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
                description: 'A list of `Cities` of a specific province',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/CitySchema'
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
                description: 'Province not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function index(Province $province, SearchRequest $request)
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->validated('search');
        $cities = $this->cityService->getCities($province, $perPage, $search);
        return $cities;
    }

    #[OA\Get(
        path: '/api/admin/locale/city/show/{city}',
        summary: 'Returns City details for edit form',
        description: 'Returns `City` details with its province for the edit form.',
        operationId: 'showCity',
        tags: ['City', 'City/Form'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'city',
                in: 'path',
                required: true,
                description: 'ID of the City to show',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A City with its province',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/CitySchema'
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
                description: 'City not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function show(City $city)
    {
        $city = $this->cityService->showCity($city);

        return $this->success($city);
    }

    #[OA\Post(
        path: '/api/admin/locale/city/store/{province}',
        summary: 'Create new City for a specific province',
        description: 'This method creates a new `City` for the province and stores it.',
        operationId: 'storeCity',
        tags: ['City'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'province',
                in: 'path',
                required: true,
                description: 'ID of the Province to add the City to',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/CityRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful City creation',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/CitySchema'
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
    public function store(CityRequest $request, Province $province)
    {
        try {
            $inputs = $request->all();
            $inputs['province_id'] = $province->id;
            $city = $this->cityService->storeCity($inputs);

            return $this->success($city, __('messages.city_created') , 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/admin/locale/city/update/{city}',
        summary: 'Updates an existing City',
        description: 'This method updates an existing `City` and saves the changes.',
        operationId: 'updateCity',
        tags: ['City'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'city',
                in: 'path',
                required: true,
                description: 'ID of the City to update',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/CityRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful City update',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/CitySchema'
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
                description: 'City not found',
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
    public function update(CityRequest $request, City $city)
    {
        try {
            $inputs = $request->all();
            $this->cityService->updateCity($city, $inputs);

            return $this->success($city->fresh(), __('messages.city_updated'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/admin/locale/city/delete/{city}',
        summary: 'Delete a City',
        description: 'This endpoint allows the user to `delete an existing City`.',
        operationId: 'deleteCity',
        tags: ['City'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'city',
                in: 'path',
                required: true,
                description: 'The ID of the City to be deleted',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'City deleted successfully',
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
                description: 'City not found',
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
    public function delete(City $city)
    {
        try {
            $this->cityService->deleteCity($city);

            return $this->success(null, __('messages.city_deleted'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
