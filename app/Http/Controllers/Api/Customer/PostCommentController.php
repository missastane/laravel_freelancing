<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CommentRequest;
use App\Http\Services\Comment\CommentService;
use App\Models\Content\Post;
use App\Models\Market\Comment;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected CommentService $commentService)
    {
    }

     /**
     * @OA\Post(
     *     path="/api/post/{post}/submit-comment",
     *     summary="Store a new comment for a post by customers",
     *     description="In this method customers can Store a new comment for a post",
     *     tags={"PostComment","Customer-Comment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="ID of the Post to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="comment", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این نظر منه"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Post Comment Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="نظر شما با موفقیت ثبت شد و پس از تأیید ناظران نمایش داده خواهد شد"),
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
     *         )
     *     )
     * )
     */
    public function store(Post $post, CommentRequest $request)
    {
        try {
            $this->commentService->addComment($request->all(), Post::class, $post->id, null, null, null);
            return $this->success(null, 'نظر شما با موفقیت ثبت شد و پس از تأیید ناظران نمایش داده خواهد شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

}
