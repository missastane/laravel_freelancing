<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Services\User\UserService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Laravel_Freelancing API",
    version: "1.0.0",
    description: "API For Freelancing Projects",
    contact: new OA\Contact(
        email: "missastaneh@gmail.com"
    ),
    license: new OA\License(
        name: "Missastaneh",
        url: "https://opensource.org/licenses/MIT"
    )
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer"
)]
#[OA\Components(
    parameters: [
        new OA\Parameter(
            parameter: "XLocale",
            name: "X-Locale",
            in: "header",
            required: false,
            description: "Preferred language for the request.",
            schema: new OA\Schema(
                type: "string",
                enum: ["fa", "en"],
                default: "en"
            )
        )
    ]
)]
class AuthController extends Controller
{
    use ApiResponseTrait;


    public function __construct(protected UserService $userService)
    {
    }

    #[OA\Post(
        path: "/api/auth/register",
        summary: "Register a new user",
        description: "This method registers a new user and sends an approval link to verify the email.",
        tags: ["Authentication"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XLocale"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/RegisterRequest"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Registration successful and verification link sent",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation Error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server Error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function register(AuthRequest $request)
    {
        try {
            $user = $this->userService->registerUser($request->all());
            return $this->success(null, __('messages.register_success'), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    #[OA\Post(
        path: "/api/auth/login",
        summary: "User Login",
        description: "Authenticate a user and create an authentication session. Web clients receive the access token in JSON and refresh/CSRF tokens through cookies. Mobile clients receive both access and refresh tokens in JSON.",
        tags: ["Authentication"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XLocale"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "missastane@gmail.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "r@mZ4Ob00r"
                    ),
                    new OA\Property(
                        property: "client_type",
                        type: "string",
                        enum: ["web", "mobile"],
                        default: "web",
                        description: "Client type. Defaults to web if omitted.",
                        example: "web"
                    ),
                    new OA\Property(
                        property: "remember_me",
                        type: "boolean",
                        default: false,
                        description: "Whether to create a persistent authentication session.",
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login. Web clients receive refresh_token and XSRF-TOKEN cookies. The refresh_token cookie is HttpOnly; the XSRF-TOKEN cookie is accessible to JavaScript. Mobile clients receive refresh_token in the JSON response instead.",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "access_token",
                                            type: "string",
                                            description: "JWT access token.",
                                            example: "eyJ0eXAiOiJK..."
                                        ),
                                        new OA\Property(
                                            property: "token_type",
                                            type: "string",
                                            example: "bearer"
                                        ),
                                        new OA\Property(
                                            property: "expires_in",
                                            type: "integer",
                                            description: "Access token lifetime in seconds.",
                                            example: 3600
                                        ),
                                        new OA\Property(
                                            property: "refresh_token",
                                            type: "string",
                                            description: "Returned only to mobile clients. Web clients receive this token through an HttpOnly cookie.",
                                            example: "random_refresh_token_value"
                                        ),
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid credentials or authentication failure.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation Error",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ValidationErrorResponse"
                )
            ),
        ]
    )]
    public function login(AuthRequest $request)
    {
        try {
            $data = $request->validated();

            $data['client_type'] = $data['client_type'] ?? 'web';
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $result = $this->userService->loginUser($data);

            if (!$result['status']) {
                return $this->error($result['message'], $result['code']);
            }

            $responseData = $result['data'];

            // Mobile clients receive the refresh token in JSON.
            if ($data['client_type'] === 'mobile') {
                return $this->success(
                    $responseData,
                    $result['message'],
                    $result['code']
                );
            }

            // Web clients receive the refresh token through an HttpOnly cookie.
            $refreshToken = $responseData['refresh_token'];
            unset($responseData['refresh_token']);

            $response = $this->success(
                $responseData,
                $result['message'],
                $result['code']
            );

            $rememberMe = (bool) ($data['remember_me'] ?? false);

            $cookieMinutes = $rememberMe
                ? 30 * 24 * 60
                : null;

            // Generate a signed CSRF token.
            $csrfToken = app(\App\Support\CsrfToken::class)->generate();

            // Refresh token cookie: HttpOnly.
            $refreshCookie = cookie(
                'refresh_token',
                $refreshToken,
                $cookieMinutes,
                '/',
                null,
                (bool) config('session.secure'),
                true,
                false,
                config('session.same_site', 'lax')
            );

            // CSRF cookie: accessible to JavaScript.
            $csrfCookie = cookie(
                'XSRF-TOKEN',
                $csrfToken,
                $cookieMinutes,
                '/',
                null,
                (bool) config('session.secure'),
                false,
                false,
                config('session.same_site', 'lax')
            );

            return $response
                ->withCookie($refreshCookie)
                ->withCookie($csrfCookie);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    #[OA\Post(
        path: "/api/auth/logout",
        summary: "User Logout",
        description: "Log out the authenticated user from the current device. The current authentication session and its associated refresh tokens are revoked, and the current JWT access token is invalidated. For web clients, the refresh_token and XSRF-TOKEN cookies are also cleared.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XLocale"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful logout. For web clients, the refresh_token and XSRF-TOKEN cookies are expired.",
                headers: [
                    new OA\Header(
                        header: "Set-Cookie",
                        description: "Web clients receive expired refresh_token (HttpOnly) and XSRF-TOKEN cookies. Mobile clients do not use these cookies.",
                        schema: new OA\Schema(
                            type: "string",
                            example: "refresh_token=; Max-Age=0; HttpOnly"
                        )
                    )
                ],
                content: new OA\JsonContent(
                    ref: "#/components/schemas/SuccessResponse"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated - Invalid or expired access token.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            )
        ]
    )]
    public function logout()
    {
        try {
            $this->userService->logoutUser();

            $response = $this->success(
                null,
                __('messages.logout_successfull')
            );

            // Clear the refresh token cookie.
            $refreshCookie = cookie(
                'refresh_token',
                '',
                -1,
                '/',
                null,
                (bool) config('session.secure'),
                true,
                false,
                config('session.same_site', 'lax')
            );

            $csrfCookie = cookie(
                'XSRF-TOKEN',
                '',
                -1,
                '/',
                null,
                (bool) config('session.secure'),
                false,
                false,
                config('session.same_site', 'lax')
            );

            return $response
                ->withCookie($refreshCookie)
                ->withCookie($csrfCookie);

        } catch (Exception $e) {
            return $this->error();
        }
    }


    #[OA\Post(
        path: "/api/auth/refresh",
        summary: "Refresh Access Token",
        description: "Refresh the access token using an independent refresh token. Web clients must send the refresh_token HttpOnly cookie, the XSRF-TOKEN cookie, the matching X-CSRF-TOKEN header, and an allowed Origin header. Mobile clients must send the refresh token through the Authorization Bearer header. Exactly one refresh token source must be provided. Every successful refresh rotates the refresh token. The CSRF token remains unchanged during refresh.",
        tags: ["Authentication"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XLocale"),
            new OA\Parameter(
                name: "refresh_token",
                in: "cookie",
                required: false,
                description: "Web only. HttpOnly refresh token cookie. Do not send this together with the Authorization Bearer header.",
                schema: new OA\Schema(
                    type: "string",
                    example: "random_refresh_token_value"
                )
            ),
            new OA\Parameter(
                name: "XSRF-TOKEN",
                in: "cookie",
                required: false,
                description: "Web only. JavaScript-readable signed CSRF cookie.",
                schema: new OA\Schema(
                    type: "string",
                    example: "csrf_nonce.csrf_signature"
                )
            ),
            new OA\Parameter(
                name: "Authorization",
                in: "header",
                required: false,
                description: "Mobile only. Send the refresh token as a Bearer token.",
                schema: new OA\Schema(
                    type: "string",
                    example: "Bearer random_refresh_token_value"
                )
            ),
            new OA\Parameter(
                name: "X-CSRF-TOKEN",
                in: "header",
                required: false,
                description: "Web only. Must exactly match the XSRF-TOKEN cookie.",
                schema: new OA\Schema(
                    type: "string",
                    example: "csrf_nonce.csrf_signature"
                )
            ),
            new OA\Parameter(
                name: "Origin",
                in: "header",
                required: false,
                description: "Web only. Must match the configured frontend origin.",
                schema: new OA\Schema(
                    type: "string",
                    example: "http://localhost:5173"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Access token refreshed successfully. Mobile clients receive the rotated refresh token in JSON. Web clients receive it through a new HttpOnly cookie. The existing XSRF-TOKEN cookie is not rotated.",
                headers: [
                    new OA\Header(
                        header: "Set-Cookie",
                        description: "Web only. Contains the rotated refresh_token cookie with HttpOnly enabled.",
                        schema: new OA\Schema(
                            type: "string",
                            example: "refresh_token=new_random_refresh_token; HttpOnly; Path=/; SameSite=Lax"
                        )
                    )
                ],
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(
                            ref: "#/components/schemas/SuccessResponse"
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: "data",
                                    type: "object",
                                    properties: [
                                        new OA\Property(
                                            property: "access_token",
                                            type: "string",
                                            description: "New JWT access token.",
                                            example: "eyJ0eXAiOiJK..."
                                        ),
                                        new OA\Property(
                                            property: "token_type",
                                            type: "string",
                                            example: "bearer"
                                        ),
                                        new OA\Property(
                                            property: "expires_in",
                                            type: "integer",
                                            description: "Access token lifetime in seconds.",
                                            example: 3600
                                        ),
                                        new OA\Property(
                                            property: "refresh_token",
                                            type: "string",
                                            description: "Rotated refresh token. Returned only to mobile clients; web clients receive it through an HttpOnly cookie.",
                                            example: "new_random_refresh_token"
                                        ),
                                    ]
                                )
                            ]
                        )
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
                description: "Invalid or disallowed request Origin.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 419,
                description: "CSRF token mismatch. The XSRF-TOKEN cookie and X-CSRF-TOKEN header must contain the same valid signed token.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
            new OA\Response(
                response: 500,
                description: "Unexpected server error.",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/ErrorResponse"
                )
            ),
        ]
    )]
    public function refresh(Request $request)
    {
        try {
            $cookieToken = $request->cookie('refresh_token');
            $bearerToken = $request->bearerToken();

            // Exactly one refresh token source must be provided.
            if (
                (empty($cookieToken) && empty($bearerToken)) ||
                (!empty($cookieToken) && !empty($bearerToken))
            ) {
                throw new \RuntimeException(
                    'Invalid refresh token request.',
                    401
                );
            }

            // Identify the client by the refresh token transport.
            if ($cookieToken) {
                $plainRefreshToken = $cookieToken;
                $clientType = 'web';
            } else {
                $plainRefreshToken = $bearerToken;
                $clientType = 'mobile';
            }

            $result = $this->userService->refresh(
                $plainRefreshToken,
                $clientType
            );

            $responseData = [
                'access_token' => $result['access_token'],
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ];

            // Mobile clients receive the rotated refresh token in JSON.
            if ($clientType === 'mobile') {
                $responseData['refresh_token'] = $result['refresh_token'];

                return $this->success(
                    $responseData,
                    __('messages.refresh_token_success')
                );
            }

            // Web clients receive the rotated refresh token in an HttpOnly cookie.
            $session = $result['session'];

            $cookieMinutes = $session->remember_me
                ? max(1, now()->diffInMinutes($session->expires_at, false))
                : null;

            $response = $this->success(
                $responseData,
                __('messages.refresh_token_success')
            );

            $cookie = cookie(
                'refresh_token',
                $result['refresh_token'],
                $cookieMinutes,
                '/',
                null,
                (bool) config('session.secure'),
                true,
                false,
                config('session.same_site', 'lax')
            );

            return $response->withCookie($cookie);

        } catch (\RuntimeException $e) {
            \Log::error('Refresh Controller RuntimeException', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return $this->error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        } catch (Exception $e) {
            \Log::error('Refresh Controller Exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return $this->error(
                __('errors.server_error'),
                500
            );
        }
    }

}
