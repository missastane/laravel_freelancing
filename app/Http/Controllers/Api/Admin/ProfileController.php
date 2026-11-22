<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\InvalidMobileNumberException;
use App\Exceptions\User\LimitChangeUsernameException;
use App\Exceptions\WrongCurrentPasswordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\AboutMeRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\ChangerUsernameRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\User\AdminUserResource;
use App\Http\Services\Profile\ProfileService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;


class ProfileController extends Controller
{
   use ApiResponseTrait;

   public function __construct(protected ProfileService $profileService)
   {
   }
   #[OA\Post(
      path: '/api/admin/profile/update',
      summary: 'Updates profile information',
      description: 'This method updates `Profile Information` and saves it.',
      operationId: 'updateProfile',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
               type: 'object',
               properties: [
                  new OA\Property(property: 'first_name', type: 'string', description: 'This field can only contain Persian letters and space. Any other characters will result in a validation error.', example: 'ایمان'),
                  new OA\Property(property: 'last_name', type: 'string', description: 'This field can only contain Persian letters and space. Any other characters will result in a validation error.', example: 'مدائنی'),
                  new OA\Property(property: 'national_code', type: 'string', example: '2730154896'),
                  new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '1990-01-01'),
                  new OA\Property(property: 'profile_photo_path', type: 'string', format: 'binary', nullable: true),
                  new OA\Property(property: '_method', type: 'string', example: 'PUT')
               ]
            )
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'successful Profile update',
            content: new OA\JsonContent(
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/AdminUser')
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
         )
      ]
   )]
   public function updateProfile(UpdateProfileRequest $request)
   {
      try {
         $inputs = $request->all();
         $user = $this->profileService->updateProfile($inputs);

         return $this->success($user, __('messages.profile_updated'), resource: AdminUserResource::class);
      } catch (Exception $e) {
         return $this->error($e->getMessage());
      }
   }

   #[OA\Post(
      path: '/api/admin/profile/change-mobile',
      summary: 'Updates Mobile number',
      description: 'This method updates the mobile number and initiates the verification process.',
      operationId: 'changeMobile',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            type: 'object',
            required: ['id'],
            properties: [
               new OA\Property(
                  property: 'id',
                  type: 'string',
                  description: 'The mobile number to update.',
                  example: '09112356987'
               ),
            ]
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Successful change phone number',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'status', type: 'boolean', example: true),
                  new OA\Property(
                     property: 'message',
                     type: 'string',
                     example: 'جهت ویرایش موبایل یا ایمیل خود با وارد کردن کد تأیید 6 رقمی ارسال شده لطفا آن را تأیید نمایید'
                  ),
                  new OA\Property(
                     property: 'data',
                     properties: [
                        new OA\Property(
                           property: 'token',
                           type: 'string',
                           example: '345dcugdbhcjolsfdtfsgh...'
                        ),
                        new OA\Property(
                           property: 'meta',
                           properties: [
                              new OA\Property(
                                 property: 'next_step',
                                 type: 'string',
                                 example: 'redirect_to_/confirm_otp'
                              ),
                           ],
                           type: 'object'
                        ),
                     ],
                     type: 'object'
                  ),
               ],
               type: 'object'
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
   public function changeMobile(Request $request)
   {
      try {
         $inputs = $request->all();
         $result = $this->profileService->changeMobile($inputs);

         return $this->success($result['data'], $result['message'], 200);
      } catch (InvalidMobileNumberException $e) {
         throw $e;
      } catch (Exception $e) {
         return $this->error($e->getMessage());
      }
   }

   #[OA\Patch(
      path: '/api/admin/profile/change-username',
      summary: "Change auth user's username",
      description: 'In this method authenticated users can change their username up to 2 times.',
      operationId: 'changeUsername',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            type: 'object',
            required: ['username'],
            properties: [
               new OA\Property(property: 'username', type: 'string', example: '12v34Kf56'),
            ]
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Username is updated successfully',
            content: new OA\JsonContent(
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/AdminUser'),
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
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تغییر نام کاربری بیش از ۲ بار مجاز نیست'),
                     ]
                  ),
               ]
            )
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
   public function changeUsername(ChangerUsernameRequest $request)
   {
      try {
         $user = $this->profileService->changeUsername($request->all());

         return $this->success($user, __('messages.username_updated'), resource: AdminUserResource::class);
      } catch (LimitChangeUsernameException $e) {
         throw $e;
      } catch (Exception $e) {
         return $this->error($e->getMessage());
      }
   }

   #[OA\Put(
      path: '/api/admin/profile/confirm-mobile/{token}',
      summary: 'Confirm OTP to change Mobile',
      description: 'This method updates the mobile number if the OTP code is valid. For testing, call `POST /api/admin/profile/change-mobile` first to obtain the token.',
      operationId: 'confirmMobile',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      parameters: [
         new OA\Parameter(
            name: 'token',
            in: 'path',
            required: true,
            description: 'Token from the OTP record',
            schema: new OA\Schema(type: 'string')
         ),
      ],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            type: 'object',
            required: ['otp'],
            properties: [
               new OA\Property(property: 'otp', type: 'string', example: '123456'),
            ]
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Mobile is updated successfully',
            content: new OA\JsonContent(
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/AdminUser'),
                     ]
                  ),
               ]
            )
         ),
         new OA\Response(
            response: 401,
            description: 'OTP is invalid',
            content: new OA\JsonContent(
               type: 'object',
               properties: [
                  new OA\Property(property: 'status', type: 'boolean', example: false),
                  new OA\Property(property: 'message', type: 'string', example: 'کد وارد شده معتبر نیست'),
                  new OA\Property(
                     property: 'data',
                     type: 'object',
                     properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'kdhbvjnjcxiygfuhdijh...'),
                        new OA\Property(
                           property: 'meta',
                           type: 'object',
                           properties: [
                              new OA\Property(property: 'next_step', type: 'string', example: 'redirect_back'),
                           ]
                        ),
                     ]
                  ),
               ]
            )
         ),
         new OA\Response(
            response: 403,
            description: 'You are not authorized to do this action.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
         ),
         new OA\Response(
            response: 500,
            description: 'Internal server error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
         ),
      ]
   )]
   public function confirmMobile($token, Request $request)
   {
      try {
         $inputs = $request->all();
         $result = $this->profileService->confirmMobile($token, $inputs);

         if ($result['status']) {
            return $this->success($result['data'], $result['message'], $result['code'], AdminUserResource::class);
         }

         return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'data' => $result['data'],
         ], $result['code']);
      } catch (Exception $e) {
         return $this->error($e->getMessage());
      }
   }

   #[OA\Put(
      path: '/api/admin/profile/about-me',
      summary: 'Store or update about-me details by admin',
      description: 'In this method admins can store or update their about-me details.',
      operationId: 'updateAboutMe',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            ref: '#/components/schemas/AboutMeRequestSchema'
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Successful profile update',
            content: new OA\JsonContent(
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/AdminUser'),
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
   public function aboutMe(AboutMeRequest $request)
   {
      try {
         $user = $this->profileService->aboutMe($request->all());

         return $this->success($user, __('messages.about_me_updated'), resource: AdminUserResource::class);
      } catch (Exception $e) {
         return $this->error($e->getMessage());
      }
   }

   #[OA\Get(
      path: '/api/admin/profile',
      summary: 'Get Auth User details',
      description: 'Returns the authenticated admin user details.',
      operationId: 'getAuthUserDetails',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      responses: [
         new OA\Response(
            response: 200,
            description: 'Successfully fetched Auth User details',
            content: new OA\JsonContent(
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/AdminUser'),
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
      ]
   )]
   public function getProfile()
   {
      return $this->success($this->profileService->getBasicInfo(), resource: AdminUserResource::class);
   }

   #[OA\Patch(
      path: '/api/admin/profile/change-password',
      summary: 'Update password by admin',
      description: 'In this method admins can update their password.',
      operationId: 'changePassword',
      tags: ['Profile'],
      security: [['bearerAuth' => []]],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            ref: '#/components/schemas/ChangePasswordRequestSchema'
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Successful password update',
            content: new OA\JsonContent(
               allOf: [
                  new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                  new OA\Schema(
                     properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/AdminUser'),
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
   public function changePassword(ChangePasswordRequest $request)
   {
      try {
         $user = $this->profileService->changePassword($request->all());

         return $this->success($user, __('messages.password_changed'), resource: AdminUserResource::class);
      } catch (WrongCurrentPasswordException $e) {
         throw $e;
      } catch (Exception $e) {
         return $this->error($e->getMessage());
      }
   }

}
