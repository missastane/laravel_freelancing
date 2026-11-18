<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\SkillRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\Skill\SkillService;
use App\Models\Market\Skill;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SkillController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected SkillService $skillService)
    {
    }
    #[OA\Get(
        path: '/api/admin/market/skill',
        summary: 'List all Skills',
        description: 'Retrieve a paginated list of all Skills available in the system.',
        operationId: 'listSkills',
        tags: ['Skill'],
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
                description: 'Search bu skill name',
                schema: new OA\Schema(type: 'string', example: null)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of Skills',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/SuccessResponse"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/SkillSchema")
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
    public function index()
    {
        $perPage = request()->input('per_page', 15);
        $search = request()->input('search');

        return $this->skillService->getSkills($perPage, $search);
    }

    #[OA\Get(
        path: '/api/admin/market/skill/show/{skill}',
        summary: 'Show a specific Skill',
        description: 'Retrieve the details of a specific Skill.',
        operationId: 'showSkill',
        tags: ['Skill', 'Skill/Form'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'skill',
                in: 'path',
                required: true,
                description: 'ID of the skill to retrieve',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skill details',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/SkillSchema'
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
                description: 'Skill not found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function show(Skill $skill)
    {
        return $this->success($this->skillService->showSkill($skill));
    }

    #[OA\Post(
        path: '/api/admin/market/skill/store',
        summary: 'Create new Skill',
        description: 'This method creates a new `Skill` and stores it.',
        operationId: 'storeSkill',
        tags: ['Skill'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/SkillRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful Skill creation',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/SkillSchema'
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
                description: 'Internal server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse'
                )
            ),
        ]
    )]
    public function store(SkillRequest $request)
    {
        try {
            $inputs = $request->all();
            $skill = $this->skillService->storeSkill($inputs);

            return $this->success($skill, __('messages.skill_created'), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/admin/market/skill/update/{skill}',
        summary: 'Updates a Skill',
        description: 'This method updates a `Skill` and saves it.',
        operationId: 'updateSkill',
        tags: ['Skill'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'skill',
                in: 'path',
                required: true,
                description: 'Skill ID to update',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/SkillRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful Skill update',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/SkillSchema'
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
                description: 'Skill not found',
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
    public function update(Skill $skill, SkillRequest $request)
    {
        try {
            $inputs = $request->all();
            $skill = $this->skillService->updateSkill($skill, $inputs);

            return $this->success($skill, __('messages.skill_updated'));
        } catch (Exception $e) {
            return $this->error();
        }
    }

    #[OA\Delete(
        path: '/api/admin/market/skill/delete/{skill}',
        summary: 'Delete a Skill',
        description: 'This endpoint allows the user to `delete an existing Skill`.',
        operationId: 'deleteSkill',
        tags: ['Skill'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'skill',
                in: 'path',
                required: true,
                description: 'The ID of the Skill to be deleted',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skill deleted successfully',
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
                description: 'Skill not found',
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
    public function delete(Skill $skill)
    {
        try {
            $this->skillService->deleteSkill($skill);

            return $this->success(null, __('messages.skill_deleted'));
        } catch (Exception $e) {
            return $this->error();
        }
    }

}
