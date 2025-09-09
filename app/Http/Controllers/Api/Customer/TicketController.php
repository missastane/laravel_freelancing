<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\TicketMessageRequest;
use App\Http\Requests\Admin\Ticket\TicketRequest;
use App\Http\Services\File\FileService;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Ticket\TicketService;
use App\Models\Market\File;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketMessage;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected TicketService $ticketService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/ticket",
     *     summary="Retrieve list of auth user's Tickets",
     *     description="Retrieve list of auth user's `Tickets`",
     *     tags={"Customer-Ticket"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="status of ticket: `open`,`closed`,`answered`",
     *         required=false,
     *         @OA\Schema(type="string", enum={"open", "closed", "answered"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Tickets",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/Ticket"
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
    public function index(Request $request)
    {
        return $this->ticketService->getUserTickets($request->query('status'));
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/options",
     *     summary="Retrieve list of Ticket Departments and Ticket Priorities",
     *     description="Retrieve list of all `Tickets Departments and Ticket Priorities` that used for store and update method",
     *     tags={"Customer-Ticket","Customer-Ticket/Form"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Ticket Departments and Ticket Priorities",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="array",
     *                   @OA\Items(type="object",
     *                      @OA\Property(property="departments", type="array",
     *                        @OA\Items(type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="مالی"),
     *                       )
     *                     ),
     *                     @OA\Property(property="priorities", type="array",
     *                        @OA\Items(type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="بسیار زیاد"),
     *                      )
     *                      )
     *                    )
     *               ),
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
    public function options()
    {
        return $this->success($this->ticketService->options());
    }

    /**
     * @OA\Post(
     *     path="/api/ticket/store",
     *     summary="Store a Ticket by customer",
     *     description="In this method customers can Store a Ticket",
     *     tags={"Customer-Ticket"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="subject", type="string", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (-). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="ticket_type", type="integer", enum={1,2,3}, description="1 => support, 2 => report , 3 => financial", example="support"),
     *             @OA\Property(property="priority_id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.-). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="files[]", type="array", 
     *                  @OA\Items(type="string", format="binary"), description="Upload a single media file.")
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Ticket Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تیکت با موفقیت ثبت شد"),
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
    public function store(TicketRequest $request)
    {
        try {
            $author_type = auth()->user()->active_role === 'freelancer' ? 2 : 1;
            $this->ticketService->newTicketStore($request->all(), $author_type);
            return $this->success(null, 'تیکت با موفقیت ثبت شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/show/{ticket}",
     *     summary="Get details of a specific Ticket",
     *     description="Returns the `Ticket` details",
     *     tags={"Customer-Ticket"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticket",
     *         in="path",
     *         description="ID of the Ticket to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Ticket details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Ticket"
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
    public function show(Ticket $ticket)
    {
        return $this->success($this->ticketService->showTicket($ticket));
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/show-message/{ticketMessage}",
     *     summary="Get details of a specific TicketMessage",
     *     description="Returns the `TicketMessage` details",
     *     tags={"Customer-Ticket","Customer-Ticket/Form"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketMessage",
     *         in="path",
     *         description="ID of the TicketMessage to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched TicketMessage details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/TicketMessage"
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
    public function showMessage(TicketMessage $ticketMessage)
    {
        return $this->success($this->ticketService->showTicketMessage($ticketMessage));
    }

    /**
     * @OA\Post(
     *     path="/api/ticket/reply/{ticketMessage}",
     *     summary="Reply to Ticket Message by customer",
     *     description="In this method customers can Reply to an existing Ticket Message",
     *     tags={"Customer-Ticket"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketMessage",
     *         in="path",
     *         description="ID of the TicketMessage to fetch",
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
     *         description="successful Ticket Reply",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پاسخ تیکت با موفقیت ثبت شد"),
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
    public function replyTicketMessage(TicketMessage $ticketMessage, TicketMessageRequest $request)
    {
         if(Gate::denies('reply',$ticketMessage)){
            return $this->error('به دلیل بسته شدن تیکت شما اجازه ارسال پیام ندارید',403);
        }
        try {
            $inputs = $request->all();
            $authorType = auth()->user()->active_role === 'freelancer' ? 2 : 1;
            $this->ticketService->replyToTicket($ticketMessage, $inputs, $authorType);
            return $this->success(null, 'پاسخ تیکت با موفقیت ثبت شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

}
