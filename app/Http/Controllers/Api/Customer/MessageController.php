<?php

namespace App\Http\Controllers\Api\Customer;

use App\Exceptions\Market\NotAllowedToSetFinalFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Services\Chat\ChatService;
use App\Models\Market\Conversation;
use App\Models\Market\File;
use App\Models\Market\Message;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected ChatService $chatService)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/message/{conversation}",
     *     summary="Retrieve list of Messages of a conversation",
     *     description="Retrieve list of all `Messages of a conversation`",
     *     tags={"Customer-Message"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="conversation",
     *         in="path",
     *         description="ID of the Conversation to fetch its messages",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Messages of a conversation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/Message"
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
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
     *             @OA\Property(property="message", type="string", example="امکان ارسال پیام به این مکالمه برای شما وجود ندارد")
     *         )
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
    public function index(Conversation $conversation)
    {
        if (Gate::denies('checkMembership', $conversation)) {
            return $this->error('امکان ارسال پیام به این مکالمه برای شما وجود ندارد', 403);
        }
        return $this->chatService->getConversationMessages($conversation);
    }

    /**
     * @OA\Post(
     *     path="/api/message/send/{conversation}",
     *     summary="Send a message to a conversation by customers",
     *     description="In this method customers can send a Message to a conversation",
     *     tags={"Customer-Message"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="conversation",
     *         in="path",
     *         description="ID of the Conversation to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="message", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file.")
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Message sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیام با موفقیت ارسال شد"),
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
     *             @OA\Property(property="message", type="string", example="امکان ارسال پیام به این مکالمه برای شما وجود ندارد")
     *         )
     *     ),
     *      @OA\Response(
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
    public function send(Conversation $conversation, SendMessageRequest $request)
    {
        if (Gate::denies('checkSendable', $conversation)) {
            return $this->error('امکان ارسال پیام به این مکالمه برای شما وجود ندارد', 403);
        }
        try {
            $newMessage = $this->chatService->sendMessage($conversation, $request->all());
            return $this->success($newMessage, 'پیام با موفقیت ارسال شد', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

     /**
     * @OA\Post(
     *     path="/api/message/set-final-file/{file}",
     *     summary="Set a file as a Final File of an order item by freelancer",
     *     description="In this method freelancers can set a file as a Final File of an order item",
     *     tags={"Customer-Message"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         description="ID of the File to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Final File Set",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت برای کارفرما ارسال شد"),
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
     *             @OA\Property(property="message", type="string", example="امکان ارسال پیام به این مکالمه برای شما وجود ندارد")
     *         )
     *     ),
     *      @OA\Response(
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
    public function setAsFinalFile(File $file)
    {
        if(Gate::denies('setAsFinalFile',$file)){
            return $this->error('عملیات غیرمجاز',403);
        }
        try {
            $this->chatService->setAsFinalFile($file);
            return $this->success(null, 'فایل با موفقیت برای کارفرما ارسال شد', 201);
        } catch (NotAllowedToSetFinalFile $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * @OA\Post(
     *     path="/api/message/reply/{message}",
     *     summary="Reply to Message by customers",
     *     description="In this method customers can Reply to an existing Message",
     *     tags={"Customer-Message"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="ID of the Message to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="message", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file.")
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Message Reply",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیام با موفقیت پاسخ داده شد"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Message")
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
     *             @OA\Property(property="message", type="string", example="امکان ارسال پیام به این مکالمه برای شما وجود ندارد")
     *         )
     *     ),
     *      @OA\Response(
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
    public function replyTo(Message $message, SendMessageRequest $reuest)
    {
        if (Gate::denies('checkSendable', [$message->conversation])) {
            return $this->error('امکان ارسال پیام به این مکالمه برای شما وجود ندارد', 403);
        }
        try {
            $answered = $this->chatService->replyToMessage($message, $reuest->all());
            return $this->success($answered, 'پیام با موفقیت پاسخ داده شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/message/delete-file/{file}",
     *     summary="Delete a File of a Message",
     *     description="This endpoint allows the user to `delete an existing File of a Message`.",
     *     tags={"Customer-Message"},
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
     *         description="Message's File deleted successfully",
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
            $this->chatService->deleteMessageFile($file);
            return $this->success(null, 'فایل با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/message/delete/{message}",
     *     summary="Delete a Message",
     *     description="This endpoint allows the user to `delete an existing Message`.",
     *     tags={"Customer-Message"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="The ID of the Message to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پیام با موفقیت حذف شد"),
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
    public function delete(Message $message)
    {
        if (Gate::denies('delete', $message)) {
            return $this->error('شما مجاز به انجام این عملیات نیستید', 403);
        }
        try {
            $this->chatService->deleteMessage($message);
            return $this->success(null, 'پیام با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

}
