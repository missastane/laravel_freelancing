<?php

namespace App\Http\Controllers\Api\Customer;

use App\Exceptions\FavoriteNotExistException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Market\ProjectFilterRequest;
use App\Http\Requests\Employer\ProjectRequest;
use App\Http\Requests\Market\ProjectCheckStatusRequest;
use App\Http\Services\Project\ProjectService;
use App\Models\Market\File;
use App\Models\Market\Project;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected ProjectService $projectService,
    ) {

    }

    /**
     * @OA\Get(
     *     path="/api/project",
     *     summary="Retrieve list of Projects",
     *     description="Retrieve list of all `Projects` with optional filtering",
     *     tags={"Customer-Project"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category. Possible values: ",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Projects",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *               @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/Project"
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/admin/user/customer"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=4)
     *             ),
     *             @OA\Property(property="total", type="integer", example=4),
     *             @OA\Property(property="last_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example=null)
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/admin/user/customer"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=4),
     *                 @OA\Property(property="total", type="integer", example=4)
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * )
     */
    public function index(ProjectFilterRequest $request)
    {
        $inputs = $request->all();
        $inputs['status'] = 'pending';
        $inputs['category_id'] = $request->category_id;
        return $this->projectService->getProjects($inputs);
    }

    /**
     * @OA\Get(
     *     path="/api/project/user-projects",
     *     summary="Retrieve list of the auth user's Projects",
     *     description="Retrieve list of all `auth user's Projects` with optional filtering",
     *     tags={"Customer-Project"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *  @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by project status. Possible values: pending, processing , completed, canceled",
     *         required=false,
     *         @OA\Schema(type="string", example="processing,canceled")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category. Possible values: ",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of auth user's Projects",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *               @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/Project"
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/admin/user/customer"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=4)
     *             ),
     *             @OA\Property(property="total", type="integer", example=4),
     *             @OA\Property(property="last_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://127.0.0.1:8000/api/admin/user/customer?page=1"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example=null)
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/admin/user/customer"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=4),
     *                 @OA\Property(property="total", type="integer", example=4)
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     )
     * )
     */
    public function userProjects(ProjectFilterRequest $request)
    {
        return $this->projectService->getUserPrjects(null, $request->all());
    }

    /**
     * @OA\Get(
     *     path="/api/project/options",
     *     summary="Retrieve list of Project Categories and Skills",
     *     description="Retrieve list of all `Project Categories` and `Skills` that used for store and update method",
     *     tags={"Customer-Project","Customer-Project/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Project Categories and skills",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *                 @OA\Property(property="data", type="array",
     *                   @OA\Items(
     *                      @OA\Property(property="categories", type="array", 
     *                          @OA\Items(
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="name", type="string", example="ترجمه"),
     *                           )                
     *                        ),
     *                      @OA\Property(property="skills", type="array",
     *                        @OA\Items(
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="persian_title", type="string", example="ترجمه"),
     *                            @OA\Property(property="original_title", type="string", example="translate"),
     *                           )        
     *                       )
     *                    )
     *               )
     *          )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     )
     * )
     */
    public function options()
    {
        return $this->success($this->projectService->options());
    }

    /**
     * @OA\Post(
     *     path="/api/project/store",
     *     summary="Store a new Project by employer",
     *     description="In this method employers can Store a new Project",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="project_category_id", type="integer", description="this field only accepted ids that exists in project cetegories table", example=2),
     *             @OA\Property(property="duration_time", type="integer", description="per day", example=5),
     *             @OA\Property(property="amount", type="integer", description="currency is tooman", example=700000),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file."),
     *             @OA\Property(property="skills[]", description="Id of skills to sync with project.", type="array", 
     *                  @OA\Items(type="integer",example=2),
     *               )
     *              ),
     *                encoding={
     *                 "skills[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Project Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پروژه با موفقیت ثبت شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     ),
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
     *    @OA\Response(
     *         response=429,
     *         description="Too Many Requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما به حداکثر تعداد مجاز ایجاد پروژه‌ رسیده‌اید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function store(ProjectRequest $request)
    {
        try {
            $inputs = $request->all();
            $project = $this->projectService->storeProject($inputs);
            return $this->success(null, 'پروژه با موفقیت ثبت شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/project/show/{project}",
     *     summary="Get details of a specific Project",
     *     description="Returns the `Project` details",
     *     tags={"Customer-Project","Customer-Project/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the Project to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Project details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Project"
     *             )
     *         )
     *   ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(Project $project)
    {
        return $this->success($this->projectService->showProject($project));
    }

    /**
     * @OA\Get(
     *     path="/api/project/details/{project}",
     *     summary="Get details of a specific Project",
     *     description="Returns the `Project` details",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the Project to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Project details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="project",
     *                     ref="#/components/schemas/Project"
     *                 ),
     *                 @OA\Property(
     *                     property="stats",
     *                     type="object",
     *                     @OA\Property(property="min_days", type="integer", example=5),
     *                     @OA\Property(property="max_days", type="integer", example=30),
     *                     @OA\Property(property="min_price", type="number", format="float", example=2000000),
     *                     @OA\Property(property="max_price", type="number", format="float", example=12000000)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too Many Requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما به حداکثر تعداد مجاز مشاهده جزئیات پروژه‌ها رسیده‌اید")
     *         )
     *     ),
     * )
     */

    public function viewDetails(Project $project)
    {
        return $this->success($this->projectService->viewProjectDetails($project));
    }

    /**
     * @OA\Post(
     *     path="/api/project/add-to-favorite/{project}",
     *     summary="Add a Project to Favorites by freelancer",
     *     description="In this method freelancers can Add a Project to Favorites",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the Project to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Add to Favorite was successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پروژه با موفقیت به لیست علاقمندی ها اضافه شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     ),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function addToFavorite(Project $project)
    {
        try {
            $this->projectService->addToFavorite($project);
            return $this->success(null, "پروژه با موفقیت به لیست علاقمندی ها اضافه شد", 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/project/delete-favorite/{project}",
     *     summary="Delete a Project From Favorites",
     *     description="This endpoint allows the freelancer to `delete a Project from favorites list`.",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="The ID of the Project to be deleted from favorites",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project deleted successfully form favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پروژه با موفقیت از لیست علاقمندی ها حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
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
    public function removeFromFavorite(Project $project)
    {
        try {
            $this->projectService->removeFavorite($project);
            return $this->success(null, 'پروژه با موفقیت از لیست علاقمندی ها حذف شد');
        } catch (FavoriteNotExistException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/project/toggle-full-time/{project}",
     *     summary="Tuggle a project fulltime status by employer",
     *     description="In this method employers can Update Fulltime status of a Project",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the Project to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Project fulltime status Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پروژه به حالت تمام وقت درآمد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     ),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function toggleFulltime(Project $project)
    {
        // if (Gate::denies('toggleFullTime')) {
        //     return $this->error('شما مجاز به انجام این عملیات نیستید. برای استفاده از این حالت باید ابتدا اشتراک خریداری فرمایید');
        // }
        // try {
        //     $message = $this->projectService->toggleFullTime($project);
        //     if ($message) {
        //         return $this->success(null, $message);
        //     }
        //     return $this->error();
        // } catch (Exception $e) {
        //     return $this->error();
        // }
    }

    /**
     * @OA\Post(
     *     path="/api/project/update/{project}",
     *     summary="Update a Project by employer",
     *     description="In this method employers can Update an existing Project",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the Project to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *            @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="project_category_id", type="integer", description="this field only accepted ids that exists in project cetegories table", example=2),
     *             @OA\Property(property="duration_time", type="integer", description="per day", example=5),
     *             @OA\Property(property="amount", type="integer", description="currency is tooman", example=700000),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file."),
     *                @OA\Property(property="skills[]", description="Id of skills to sync with project.", type="array", 
     *                  @OA\Items(type="integer",example=2),
     *               ),
     *             @OA\Property(property="_method", type="string", example="PUT")
     *              ),
     *                encoding={
     *                 "skills[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Project Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پروژه با موفقیت بروزرسانی شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     ),
     *   @OA\Response(
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
     *         )
     *     )
     * )
     */
    public function update(Project $project, ProjectRequest $request)
    {
        if (Gate::denies('update', $project)) {
            return $this->error('شما مجاز به انجام این عملیات نیستید',403);
        }
        try {
            $inputs = $request->all();
            $this->projectService->updateProject($project, $inputs);
            return $this->success(null, 'پروژه با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/project/delete-file/{file}",
     *     summary="Delete a File of a Project",
     *     description="This endpoint allows the user to `delete an existing File of a Project`.",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         description="The ID of the File to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project's File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
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
    public function deleteFile(File $file)
    {
        if (Gate::denies('deleteFile', $file)) {
            return $this->error('شما مجاز به حذف این فایل نیستید', 403);
        }
        try {
            $this->projectService->deleteProjectFile($file);
            return $this->success(null, 'فایل با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/project/delete/{project}",
     *     summary="Delete a Project",
     *     description="This endpoint allows the user to `delete an existing Project`.",
     *     tags={"Customer-Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="The ID of the Project to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پروژه با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
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
    public function delete(Project $project)
    {
        try {
            if (Gate::allows('delete', $project)) {
                $this->projectService->deleteProject($project);
                return $this->success(null, 'پروژه با موفقیت حذف شد');
            } else {
                return $this->error('شما مجاز به انجام این عملیات نیستید',403);
            }
        } catch (Exception $e) {
            return $this->error();
        }
    }


}
