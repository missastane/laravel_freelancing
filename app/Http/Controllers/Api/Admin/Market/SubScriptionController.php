<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\SubscriptionRequest;
use App\Http\Requests\Admin\Market\UpdateSubscriptionRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\Market\SubscriptionWithFeatureResource;
use App\Http\Services\Subscription\SubscriptionService;
use App\Models\Market\Subscription;
use App\Models\Market\SubscriptionFeature;
use App\Traits\ApiResponseTrait;
use Exception;
use OpenApi\Attributes as OA;

class SubScriptionController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    #[OA\Get(
        path: '/api/admin/market/subscription',
        summary: 'Retrieve list of Subscriptions',
        description: 'Retrieve list of all `Subscriptions`',
        operationId: 'listSubscriptions',
        tags: ['Subscription'],
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
                description: 'Number of items per page (default: 15)',
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search term to filter subscriptions',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of Subscriptions',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        ref: '#/components/schemas/SubscriptionSchema'
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
        $subscriptions = $this->subscriptionService->getAllSubscriptions($search, $perPage);
        return $this->success($subscriptions, resource: SubscriptionWithFeatureResource::class);
    }

    #[OA\Post(
        path: '/api/admin/market/subscription/store',
        summary: 'Store a new Subscription by admin',
        description: 'In this method admins can store a new `Subscription`.',
        operationId: 'storeSubscription',
        tags: ['Subscription'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/SubscriptionRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful Subscription creation',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'data', type: 'object', ref: '#/components/schemas/SubscriptionSchema'),
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
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(SubscriptionRequest $request)
    {
        try {
            $subscription = $this->subscriptionService->storeSubscription($request->all());
            return $this->success($subscription, __('messages.subscription_created'), 201, SubscriptionWithFeatureResource::class);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/admin/market/subscription/show/{subscription}',
        summary: 'Get details of a specific Subscription',
        description: 'Returns the `Subscription` details',
        operationId: 'showSubscription',
        tags: ['Subscription', 'Subscription/Form'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'subscription',
                in: 'path',
                required: true,
                description: 'ID of the Subscription to fetch',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully fetched Subscription details',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/SubscriptionSchema'
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
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Subscription not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(Subscription $subscription)
    {
        $subscription = $this->subscriptionService->showSubscription($subscription);
        return $this->success($subscription, resource: SubscriptionWithFeatureResource::class);
    }

    #[OA\Put(
        path: '/api/admin/market/subscription/update/{subscription}',
        summary: 'Update an existing Subscription by admin',
        description: 'In this method admins can update an existing `Subscription`.',
        operationId: 'updateSubscription',
        tags: ['Subscription'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'subscription',
                in: 'path',
                required: true,
                description: 'The ID of the Subscription to be updated',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/UpdateSubscriptionRequestSchema'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful Subscription update',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'data', ref: '#/components/schemas/SubscriptionSchema'),
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
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Subscription not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function update(Subscription $subscription, UpdateSubscriptionRequest $request)
    {
        try {
            $subscription = $this->subscriptionService->updateSubscription($subscription, $request->all());

            return $this->success($subscription, __('messages.subscription_updated'), resource: SubscriptionWithFeatureResource::class);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/admin/market/subscription/delete-feature/{subscriptionFeature}',
        summary: 'Delete a SubscriptionFeature',
        description: 'This endpoint allows the admin to delete an existing `SubscriptionFeature`.',
        operationId: 'deleteSubscriptionFeature',
        tags: ['Subscription'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'subscriptionFeature',
                in: 'path',
                required: true,
                description: 'The ID of the SubscriptionFeature to be deleted',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'SubscriptionFeature deleted successfully',
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
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'SubscriptionFeature not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function deleteFeature(SubscriptionFeature $subscriptionFeature)
    {
        try {
            $this->subscriptionService->deleteFeature($subscriptionFeature);

            return $this->success(null, __('messages.subscription_feature_deleted'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/admin/market/subscription/delete/{subscription}',
        summary: 'Delete a Subscription',
        description: 'This endpoint allows the admin to delete an existing `Subscription`.',
        operationId: 'deleteSubscription',
        tags: ['Subscription'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'subscription',
                in: 'path',
                required: true,
                description: 'The ID of the Subscription to be deleted',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subscription deleted successfully',
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
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Subscription not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function delete(Subscription $subscription)
    {
        try {
            $this->subscriptionService->deleteSubscription($subscription);

            return $this->success(null, __('messages.subscription_deleted'));
        } catch (Exception $e) {
            return $this->error();
        }
    }

}
