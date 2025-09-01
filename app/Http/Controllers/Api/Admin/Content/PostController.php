<?php

namespace App\Http\Controllers\Api\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\PostRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Post\PostService;
use App\Models\Content\Post;
use App\Models\Market\File;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected PostService $postService,
        protected FileManagementService $fileManagementService
    ) {
    }
    /**
     * @OA\Get(
     *     path="/api/admin/content/post",
     *     summary="Retrieve list of Posts",
     *     description="Retrieve list of all `Posts`",
     *     tags={"Post"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Posts with their Tags",
     *   @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="title", type="string", example="دنیای جدید دیجیتال"),
     *                         @OA\Property(property="slug", type="string", example="digital-news"),
     *                         @OA\Property(property="summary", type="string", example="خلاصه مطلب"),
     *                         @OA\Property(property="content", type="string", example="بدنه اصلی پست"),
     *                         @OA\Property(property="image", type="string", example="path/image.jpg"),
     *                         @OA\Property(property="study_time", type="string", example="2 دقیقه"),
     *                         @OA\Property(property="view", type="integer", example="2"),
     *                         @OA\Property(property="status", type="string", example="فعال"),
     *                         @OA\Property(property="related_posts", type="array",
     *                             @OA\Items(type="object",
     *                              @OA\Property(property="id", type="integer", example=2),
     *                              @OA\Property(property="name", type="string", example="برنامه نویسی"),
     *                              @OA\Property(property="slug", type="string", example="برنامه-نویسی")
     *                             )
     *                         ),
     *                         @OA\Property(property="post_category", type="object",
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="name", type="string", example="آموزشی")
     *                           ),
     *                         @OA\Property(property="author", type="object",
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="first_name", type="string", example="ایمان"),
     *                            @OA\Property(property="last_name", type="string", example="مدائنی")
     *                           ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                         @OA\Property(
     *                             property="tags",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="name", type="string", example="برنامه نویسی")
     *                             )
     *                         ),
     *                          @OA\Property(
     *                             property="files",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="file_name", type="string", example="name.extension"),
     *                                 @OA\Property(property="file_path", type="string", example="path/.."),
     *                                 @OA\Property(property="file_size", type="string", example="1245525"),
     *                             )
     *                         )
     *                    )
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
    public function index()
    {
        $posts = $this->postService->getPosts();
        return $posts;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/search",
     *     summary="Searchs among Posts by title",
     *     description="This endpoint allows users to search for `Posts` by title. The search is case-insensitive and returns results that contain the given keyword. The results are paginated for better performance",
     *     tags={"Post"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *   @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="type title of Post which you're searching for",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Posts with their Tags",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="title", type="string", example="دنیای جدید دیجیتال"),
     *                         @OA\Property(property="slug", type="string", example="digital-news"),
     *                         @OA\Property(property="summary", type="string", example="خلاصه مطلب"),
     *                         @OA\Property(property="content", type="string", example="بدنه اصلی پست"),
     *                         @OA\Property(property="image", type="string", example="path/image.jpg"),
     *                         @OA\Property(property="study_time", type="string", example="2 دقیقه"),
     *                         @OA\Property(property="view", type="integer", example="2"),
     *                         @OA\Property(property="status", type="string", example="فعال"),
     *                         @OA\Property(property="related_posts", type="array",
     *                             @OA\Items(type="object",
     *                              @OA\Property(property="id", type="integer", example=2),
     *                              @OA\Property(property="name", type="string", example="برنامه نویسی"),
     *                              @OA\Property(property="slug", type="string", example="برنامه-نویسی")
     *                             )
     *                         ),
     *                         @OA\Property(property="post_category", type="object",
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="name", type="string", example="آموزشی")
     *                           ),
     *                         @OA\Property(property="author", type="object",
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="first_name", type="string", example="ایمان"),
     *                            @OA\Property(property="last_name", type="string", example="مدائنی")
     *                           ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                         @OA\Property(
     *                             property="tags",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="name", type="string", example="برنامه نویسی")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="files",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="file_name", type="string", example="name.extension"),
     *                                 @OA\Property(property="file_path", type="string", example="path/.."),
     *                                 @OA\Property(property="file_size", type="string", example="1245525"),
     *                             )
     *                         )
     *                    )
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
    public function search(SearchRequest $request)
    {
        $posts = $this->postService->searchPosts($request->search);
        return $posts;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/show/{post}",
     *     summary="Get details of a specific Post",
     *     description="Returns the `Post` details along with tags and provide details for edit method.",
     *     operationId="getPostDetails",
     *     tags={"Post", "Post/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="ID of the Post to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Post details with tags for editing",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="title", type="string", example="دنیای جدید دیجیتال"),
     *                         @OA\Property(property="slug", type="string", example="digital-news"),
     *                         @OA\Property(property="summary", type="string", example="خلاصه مطلب"),
     *                         @OA\Property(property="content", type="string", example="بدنه اصلی پست"),
     *                         @OA\Property(property="image", type="string", example="path/image.jpg"),
     *                         @OA\Property(property="study_time", type="string", example="2 دقیقه"),
     *                         @OA\Property(property="view", type="integer", example="2"),
     *                         @OA\Property(property="status", type="string", example="فعال"),
     *                         @OA\Property(property="related_posts", type="array",
     *                             @OA\Items(type="object",
     *                              @OA\Property(property="id", type="integer", example=2),
     *                              @OA\Property(property="name", type="string", example="برنامه نویسی"),
     *                              @OA\Property(property="slug", type="string", example="برنامه-نویسی")
     *                             )
     *                         ),
     *                         @OA\Property(property="post_category", type="object",
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="name", type="string", example="آموزشی")
     *                           ),
     *                         @OA\Property(property="author", type="object",
     *                            @OA\Property(property="id", type="integer", example=2),
     *                            @OA\Property(property="first_name", type="string", example="ایمان"),
     *                            @OA\Property(property="last_name", type="string", example="مدائنی")
     *                           ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-09T07:51:06.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-09T07:53:17.000000Z"),
     *                         @OA\Property(
     *                             property="tags",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="name", type="string", example="برنامه نویسی")
     *                             )
     *                       ),
     *                         @OA\Property(
     *                             property="files",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=2),
     *                                 @OA\Property(property="file_name", type="string", example="name.extension"),
     *                                 @OA\Property(property="file_path", type="string", example="path/.."),
     *                                 @OA\Property(property="file_size", type="string", example="1245525"),
     *                             )
     *                         )
     *                 )
     *          )
     *   ),
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
     *     ),
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(Post $post)
    {
        $post = $this->postService->showPost($post);
        return $this->success($post);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/content/post/options",
     *     summary="Get necessary options for Post forms",
     *     description="This endpoint returns all `PostCategories` and `Posts` which can be used to create a new post",
     *     tags={"Post", "Post/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched post categories and posts that you may need to make a post create form",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="postCategories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="posts",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="title", type="string")
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *    @OA\Response(
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
        $data = $this->postService->options();
        return $this->success([
            'postCategories' => $data['postCategories'],
            'posts' => $data['posts']
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/content/post/store",
     *     summary="create new category",
     *     description="this method creates a new `Post` and stores its related tags.",
     *     tags={"Post"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="image", type="string", format="binary" ),
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="summary", type="string", example="خلاصه تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="content", type="string", example="توضیح تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="study_time", type="string", description="This field can only contain Persian and English letters and numbers and space. Any other characters will result in a validation error.", example="2 دقیقه"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file."
     *              ),
     *             @OA\Property(property="related_posts[]", description="Ids Of Relation Posts", type="array", nullable="true", 
     *                 @OA\Items(type="integer", example=1)
     *             ),
     *             @OA\Property(property="category_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     *                 @OA\Property(property="published_at", type="integer", example=1677030400),
     *             @OA\Property(property="tags[]", type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="تازه های دیجیتال"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             )
     *                       ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Post and tags creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پست با موفقیت افزوده شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *          )
     *     ),
     *      @OA\Response(
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
    public function store(PostRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['image'] = $request->file('image') ?? null;
            $inputs['tags'] = $request->filled('tags') ? $request->tags : [];
            $inputs['related_posts'] = $request->filled('related_posts') ? $request->related_posts : [];
            $this->postService->storePost($inputs);
            return $this->success(null, 'پست با موفقیت افزوده شد', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/content/post/status/{post}",
     *     summary="Change the status of a Post",
     *     description="This endpoint `toggles the status of a Post` (active/inactive)",
     *     operationId="updatePostStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="Post id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Post status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پست با موفقیت فعال شد"),
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
    public function status(Post $post)
    {
        try {
            $message = $this->postService->changeStatus($post);
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
     *     path="/api/admin/content/post/update/{post}",
     *     summary="Update an existing post",
     *     description="this method updates an existing `Post` and stores its related tags.",
     *     tags={"Post"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="Post id to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="image", type="string", format="binary" ),
     *             @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and hyphens (-). Any other characters will result in a validation error.", example="تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="summary", type="string", example="خلاصه تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="content", type="string", example="توضیح تأثیر هوش مصنوعی بر دنیای دیجیتال"),
     *             @OA\Property(property="study_time", type="string", description="This field can only contain Persian and English letters and numbers and space. Any other characters will result in a validation error.", example="2 دقیقه"),
     *             @OA\Property(
     *                 property="status",
     *                 oneOf={
     *                     @OA\Schema(type="integer", example=1, description="1 = active"),
     *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
     *                 }
     *             ),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file."
     *              ),
     *             @OA\Property(property="related_posts[]", description="Ids Of Relation Posts", type="array", nullable="true", 
     *                 @OA\Items(type="integer", example=1)
     *             ),
     *             @OA\Property(property="category_id",description="ParentID.This field is optional when creating or updating the category.", type="integer", nullable="true", example=5),
     *                 @OA\Property(property="published_at", type="integer", example=1677030400),
     *             @OA\Property(property="tags[]", type="array",
     *                 @OA\Items(type="string",pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\.\?]+$", example="تازه های دیجیتال"),
     *              description="This field can only contain Persian and English letters, Persian and English numbers, hyphens (-),question marks (?), and periods (.). Any other characters will result in a validation error.",
     *             ),
     *            @OA\Property(property="_method", type="string", example="PUT" )
     *                       ),
     *             encoding={
     *                 "tags[]": {
     *                     "style": "form",
     *                     "explode": true
     *                 }
     *             }
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Post and tags update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پست با موفقیت بروزرسانی شد"),
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
    public function update(Post $post, PostRequest $request)
    {
        try {
            $inputs = $request->all();
            $inputs['image'] = $request->file('image') ?? null;
            $inputs['tags'] = $request->filled('tags') ? $request->tags : [];
            $inputs['related_posts'] = $request->filled('related_posts') ? $request->related_posts : [];
            $this->postService->updatePost($post,$inputs);
            return $this->success(null, 'پست با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/content/post/delete/{post}",
     *     summary="Delete a Post",
     *     description="This endpoint allows the user to `delete an existing Post`.",
     *     operationId="deletePost",
     *     tags={"Post"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="The ID of the Post to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پست با موفقیت حذف شد"),
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
    public function delete(Post $post)
    {
        try {
            $this->postService->deletePost($post);
            return $this->success(null, 'پست ' . $post->title . ' با موفقیت حذف شد');
        } catch (Exception $e) {
           return $this->error();
        }
    }

      /**
     * @OA\Delete(
     *     path="/api/admin/content/post/delete-file/{file}",
     *     summary="Delete a File of a Post",
     *     description="This endpoint allows the user to `delete an existing File of a Post`.",
     *     tags={"Post"},
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
     *         description="Post's File deleted successfully",
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
        try {
            $this->fileManagementService->deleteFile($file);
            return $this->success(null, 'فایل با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
