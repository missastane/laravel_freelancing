<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ticket\DisputeTicketRequest;
use App\Http\Requests\Admin\User\ArbitrationRequest;
use App\Http\Services\DisputeRequest\DisputeJudgementService;
use App\Http\Services\DisputeRequest\DisputeRequestService;
use App\Models\Ticket\Ticket;
use App\Models\User\DisputeRequest;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DisputeRequestController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected DisputeRequestService $disputeRequestService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/dispute-request",
     *     summary="Retrieve list of Dispute Requests",
     *     description="Retrieve list of all `Dispute Requests`",
     *     tags={"DisputeRequest"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="status of request to fetch",
     *         @OA\Schema(type="string", enum={"pending","resolved","withdrawn","rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Dispute Requests",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *               @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/DisputeRequest"
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
        return $this->disputeRequestService->getDisputeRequests($request->query('status'));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/user/dispute-request/create-ticket/{disputeRequest}",
     *     summary="Store a new Ticket by admin with complain type",
     *     description="In this method admins can Create a new complain Ticket",
     *     tags={"DisputeRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="disputeRequest",
     *         in="path",
     *         description="ID of the DisputeRequest to fetch",
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
     *             @OA\Property(property="department_id", type="integer", description="this value must be exist in ticket_departments, otherwise it returns 422"),
     *             @OA\Property(property="priority_id", type="integer", description="this value must be exist in ticket_priorities, otherwise it returns 422"),
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Complain Ticket Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="تیکت داوری با موفقیت ثبت شد و به طرفین در این رابطه اطلاع داده شد"),
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
    public function createTicket(DisputeRequest $disputeRequest, DisputeTicketRequest $request)
    {
        if(Gate::denies('createTicket',$disputeRequest)){
            return $this->error('عملیات غیرمجاز',403);
        }
        try {
            $this->disputeRequestService->createDisputeTicket($disputeRequest, $request->all());
            return $this->success(null, 'تیکت داوری ثبت شد و به طرفین در این رابطه اطلاع داده شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/user/dispute-request/judge/{disputeRequest}",
     *     summary="Judge a dispute request by admin",
     *     description="In this method admins can Judge a DisputeRequest",
     *     tags={"DisputeRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="disputeRequest",
     *         in="path",
     *         description="ID of the DisputeRequest to fetch",
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
     *             @OA\Property(property="status", type="integer", enum={2,3,4,5}, description="2 => For the benefit of the employer(cancel), 3 => For the benefit of the freelancer(approve delivery), 4 => Money distribution, 5 => without change", example=1),
     *             @OA\Property(property="description", type="string", description="this value must only include english and persian letters and space and numbers and symbols(?!.,، ) , otherwise it returns 422"),
     *             @OA\Property(property="freelancer_percent", type="integer", example=20),
     *             @OA\Property(property="employer_percent", type="integer", example=80),
     *          )
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Judgement Submition",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="نتیجه داوری با موفقیت ثبت و برای طرفین ارسال شد"),
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
    public function judge(DisputeRequest $disputeRequest, ArbitrationRequest $request)
    {
        if(Gate::denies('judge',$disputeRequest)){
            return $this->error('عملیات غیرمجاز',403);
        }
        try {
            $this->disputeRequestService->judgeDisputeRequest($disputeRequest, $request->all());
            return $this->success(null, 'نتیجه داوری با موفقیت ثبت و برای طرفین ارسال شد');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/dispute-request/show/{disputeRequest}",
     *     summary="Get details of a specific disputeRequest",
     *     description="Returns the `disputeRequest` details",
     *     operationId="getdisputeRequestDetails",
     *     tags={"DisputeRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="disputeRequest",
     *         in="path",
     *         description="ID of the DisputeRequest to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched DisputeRequest details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/DisputeRequest"
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
    public function show(DisputeRequest $disputeRequest)
    {
        return $this->success($this->disputeRequestService->showDisputeRequest($disputeRequest));
    }
}
