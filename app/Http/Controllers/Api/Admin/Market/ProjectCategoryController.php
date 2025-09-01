<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\ProjectCategoryRequest;
use App\Http\Services\ProjectCategory\ProjectCategoryService;
use App\Models\Market\ProjectCategory;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class ProjectCategoryController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected ProjectCategoryService $projectCategoryService
    ) {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/market/project-category",
     *     summary="List all ProjectCategories",
     *     description="Retrieve a paginated list of all ProjectCategories available in the system.",
     *     tags={"ProjectCategory"},
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
     *         description="Paginated list of ProjectCategories",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/ProjectCategory"
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     ))
     * )
     */
    public function index()
    {
        $projectCategories = $this->projectCategoryService->getCategories();
        return $this->success($projectCategories);
    }
    /**
     * @OA\Get(
     *     path="/api/admin/market/project-category/search",
     *     summary="Searchs among ProjectCategories by name and description",
     *     description="This endpoint allows users to search for `ProjectCategory` by name and description. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"ProjectCategory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="ProjectCategory name or description",
     *         @OA\Schema(type="string", example="ترجمه")
     *     ),
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
     *         description="Paginated list of ProjectCategories",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/ProjectCategory"
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     ))
     * )
     */
    public function search(Request $request)
    {
        $projectCategories = $this->projectCategoryService->searchCategory($request->search);
        return $this->success($projectCategories);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/project-category/show/{projectCategory}",
     *     summary="Show a specific projectCategory",
     *     description="Retrieve the details of a specific projectCategory",
     *     tags={"ProjectCategory", "ProjectCategory/Form"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="projectCategory",
     *         in="path",
     *         required=true,
     *         description="ID of the projectCategory to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="projectCategory details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/ProjectCategory"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(ProjectCategory $projectCategory)
    {
        return $this->success($this->projectCategoryService->showCategory($projectCategory));
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/market/project-category/status/{projectCategory}",
     *     summary="Change the status of a ProjectCategory",
     *     description="This endpoint `toggles the status of a ProjectCategory` (active/inactive)",
     *     security={{"bearerAuth": {}}},
     *     tags={"ProjectCategory"},
     *     @OA\Parameter(
     *         name="projectCategory",
     *         in="path",
     *         description="ProjectCategory id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="ProjectCategory status state updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت با موفقیت فعال شد"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *         )
     *     ),
     *   @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function toggleStatus(ProjectCategory $projectCategory)
    {
        try {
            $message = $this->projectCategoryService->toggleStatus($projectCategory);
            if ($message) {
                return $this->success(null, $message);
            }
            return $this->error();
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/market/project-category/show-in-menu/{projectCategory}",
     *     summary="Change show in menu status of a ProjectCategory",
     *     description="This endpoint `toggles show in menu status of a ProjectCategory` (active/inactive)",
     *     security={{"bearerAuth": {}}},
     *     tags={"ProjectCategory"},
     *     @OA\Parameter(
     *         name="projectCategory",
     *         in="path",
     *         description="ProjectCategory id to change show in menu status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="ProjectCategory show in menu state updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="وضعیت نمایش در منو با موفقیت فعال شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *   @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function toggleShowInMenu(ProjectCategory $projectCategory)
    {
        try {
            $message = $this->projectCategoryService->toggleShowInMenu($projectCategory);
            if ($message) {
                return $this->success(null, $message);
            }
            return $this->error();
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/market/project-category/store",
     *     summary="Create new ProjectCategory",
     *     description="This method creates a new `ProjectCategory` and stores it.",
     *     tags={"ProjectCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="name", type="string", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="ترجمه"),
     *             @OA\Property(property="description", type="string", description="This field can only contain Persian and English letters and numbers and space and symbols(?.!،-,؟). Any other characters will result in a validation error.", example="این گروه مربوط به ترجمه می باشد."),
     *             @OA\Property(property="image", type="string", format="binary"),
     *             @OA\Property(property="parent_id", type="integer", description="This field only take integers that exist in project categories table", nullable="true", example="1"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="show_in_menu",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = yes"),
     *                     @OA\Schema(type="integer", example=2, description="2 = no")
     *                 }
     *             ),
     *              @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *                       ),
     *            encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Project Category creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسته بندی پروژه با موفقیت ایجاد شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *   @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
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
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *)
     * )
     */
    public function store(ProjectCategoryRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['image'] = $request->file('image') ?? null;
            $inputs['tags'] = $request->filled('tags') ? $request->tags : [];
            $category = $this->projectCategoryService->storeCategory($inputs);
            return $this->success(null,'دسته بندی پروژه با موفقیت افزوده شد', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * @OA\Post(
     *     path="/api/admin/market/project-category/update/{projectCategory}",
     *     summary="Updates a ProjectCategory",
     *     description="This method updates a `ProjectCategory` and save it.",
     *     tags={"ProjectCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="projectCategory",
     *         in="path",
     *         required=true,
     *         description="ProjectCategory Id to fetch",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(type="object",
     *             @OA\Property(property="name", type="string", description="This field can only contain Persian and English letters and space. Any other characters will result in a validation error.", example="ترجمه"),
     *             @OA\Property(property="description", type="string", description="This field can only contain Persian and English letters and numbers and space and symbols(?.!،-,؟). Any other characters will result in a validation error.", example="این گروه مربوط به ترجمه می باشد."),
     *             @OA\Property(property="image", type="string", format="binary", nullable="true"),
     *             @OA\Property(property="parent_id", type="integer", description="This field only take integers that exist in project categories table", nullable="true", example="1"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="show_in_menu",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = yes"),
     *                     @OA\Schema(type="integer", example=2, description="2 = no")
     *                 }
     *             ),
     *             
     *                        @OA\Property(
     *                 property="tags[]",
     *                 type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="لوازم ورزشی"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *            @OA\Property(property="_method", type="string", example="PUT"),
     *                       ),
     *            encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Project Category update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="دسته بندی پروژه با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *   @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * @OA\Response(
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
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *     )
     *)
     * )
     */
    public function update(ProjectCategory $projectCategory, ProjectCategoryRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['image'] = $request->file('image') ?? null;
            $inputs['tags'] = $request->filled('tags') ? $request->tags : [];
            $this->projectCategoryService->updateCategory($projectCategory, $inputs);
            return $this->success(null,'دسته بندی پروژه با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/admin/market/project-category/delete/{projectCategory}",
     *     summary="Delete a ProjectCategory",
     *     description="This endpoint allows the user to `delete an existing ProjectCategory`.",
     *     tags={"ProjectCategory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="projectCategory",
     *         in="path",
     *         description="The ID of the ProjectCategory to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProjectCategory deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="دسته بندی پروژه با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *   @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function delete(ProjectCategory $projectCategory)
    {
        try {
            $this->projectCategoryService->deleteCategory($projectCategory);
            return $this->success(null,'دسته بندی پروژه با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }

    }
}
