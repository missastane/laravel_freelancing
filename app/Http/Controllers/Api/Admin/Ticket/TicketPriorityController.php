<?php

namespace App\Http\Controllers\Api\Admin\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\TicketPriorityRequest;
use App\Http\Services\Ticket\TicketPriorityService;
use App\Models\Ticket\TicketPriority;
use App\Traits\ApiResponseTrait;
use Exception;

class TicketPriorityController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected TicketPriorityService $ticketPriorityService){}
    /**
     * @OA\Get(
     *     path="/api/admin/ticket/priority",
     *     summary="Retrieve list of Ticket Priorities",
     *     description="Retrieve list of all `Ticket Priorities`",
     *     tags={"TicketPriority"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Ticket Priorities",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/TicketPriority"
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
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     )
     * )
     */
    public function index()
    {
        return $this->success($this->ticketPriorityService->getPriorities());
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ticke/priority/store",
     *     summary="Store a new Ticket Priority by admin",
     *     description="In this method admins can Store A new Ticket Priority",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF ]+$", description="This field can only contain Persian and English letters. Any other characters will result in a validation error.", example="بسیار کم"),
     *          )
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Ticket Priority Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="اولویت تیکت با موفقیت افزوده شد"),
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
    public function store(TicketPriorityRequest $request)
    {
        try {
            $inputs = $request->all();
            $priority = $this->ticketPriorityService->storePriority($inputs);
            return $this->success(null, 'اولویت تیکت با موفقیت افزوده شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ticket/priority/show/{ticketPriority}",
     *     summary="Get details of a specific Ticket Priority",
     *     description="Returns the `Ticket Priority` details",
     *     operationId="getTicketPriorityDetails",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="ID of the Ticket Priority to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Ticket Priority details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/TicketPriority"
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
    public function show(TicketPriority $ticketPriority)
    {
        return $this->success($this->ticketPriorityService->showPriority($ticketPriority));
    }

      /**
     * @OA\Post(
     *     path="/api/admin/ticke/priority/update/{ticketPriority}",
     *     summary="Update an existing Ticket Priority by admin",
     *     description="In this method admins can Update an existing Ticket Priority",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="ID of the Ticket Priority to fetch",
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
     *             @OA\Property(property="name", type="string", pattern="^[a-zA-Z\u0600-\u06FF ]+$", description="This field can only contain Persian and English letters. Any other characters will result in a validation error.", example="بسیار کم"),
     *          )
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Ticket Priority Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="اولویت تیکت با موفقیت بروزرسانی شد"),
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
    public function update(TicketPriority $ticketPriority, TicketPriorityRequest $request)
    {
        try {
            $inputs = $request->all();
            $this->ticketPriorityService->updatePriority($ticketPriority,$inputs);
            return $this->success(null, 'اولویت تیکت با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

     /**
     * @OA\Patch(
     *     path="/api/admin/ticket/priority/status/{ticketPriority}",
     *     summary="Change the status of a Ticket Priority",
     *     description="This endpoint `tggle the status of a Ticket Priority` (active/inactive)",
     *     operationId="updateTicketPriorityStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"TicketPriority"},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="Ticket Priority id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Ticket Priority status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="اولویت تیکت با موفقیت فعال شد"),
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
    public function changeStatus(TicketPriority $ticketPriority)
    {
       try {
            $message = $this->ticketPriorityService->changeStatus($ticketPriority);
            if ($message) {
                return $this->success(null, $message);
            }
            return $this->error();
        } catch (Exception $e) {
            return $this->error();
        }
    }

     /**
     * @OA\Delete(
     *     path="/api/admin/ticket/priority/delete/{ticketPriority}",
     *     summary="Delete a Ticket Priority",
     *     description="This endpoint allows the user to `delete an existing Ticket Priority`.",
     *     operationId="deleteTicketPriority",
     *     tags={"TicketPriority"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketPriority",
     *         in="path",
     *         description="The ID of the Ticket Priority to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket Priority deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="اولویت تیکت با موفقیت حذف شد"),
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
    public function delete(TicketPriority $ticketPriority)
    {
        try {
            $this->ticketPriorityService->deletePriority($ticketPriority);
            return $this->success(null, 'اولویت تیکت با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
