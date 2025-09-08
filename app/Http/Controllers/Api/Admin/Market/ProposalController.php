<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProposalStatusRequest;
use App\Http\Services\Proposal\ProposalService;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected ProposalService $proposalService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/proposal",
     *     summary="Retrieve list of a user's Proposals",
     *     description="Retrieve list of all of `a user's Proposals`",
     *     tags={"Proposal"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *      @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by proposal status. Possible values: pending, approved , rejected, withdrown",
     *         required=false,
     *         @OA\Schema(type="string", example="pending")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Proposals",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/Proposal"
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
    public function index(ProposalStatusRequest $request)
    {
        return $this->proposalService->getProposals($request->query('status'));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/market/proposal/show/{proposal}",
     *     summary="Get details of a specific Proposal",
     *     description="Returns the `Proposal` details",
     *     operationId="getProposalDetails",
     *     tags={"Proposal"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="proposal",
     *         in="path",
     *         description="ID of the Proposal to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Proposal details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *              @OA\Property(property="project", type="object",
     *                   @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="title", type="string", example="برنامه نویسی لاراول"),
     *                   @OA\Property(property="slug", type="string", example="برنامه-نویسی-لاراول"),
     *                   @OA\Property(property="description", type="string", example= "در این پروژه ما می خواهیم که یک پلتفرم را با فریم ورک لاراول پیاده سازی و اجرا کنیم."),
     *                   @OA\Property(property="duration_time", type="integer", example=15),
     *                   @OA\Property(property="amount", type="decimal", example=7000000.000),
     *                   @OA\Property(property="status", type="string", description="1 => pending, 2 => in progress , 3 => completed, 4 => canceled", example="تکمیل شده"),
     *                   @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
     *                   @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
     *                   @OA\Property(property="employer", type="object",
     *                      @OA\Property(property="username", type="string", example="ایمان"),
     *                    )
     *                 ),
     *                 @OA\Property(property="proposal", type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="freelancer", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="username", type="string", example="missastane"),
     *                       ),
     *                      @OA\Property(property="description", type="string", example="این پیشنهاد منه"),
     *                      @OA\Property(property="total_amount", type="string", nullable=true, example=null),
     *                      @OA\Property(property="due_date", type="string", format="date-time", description="تاریخ تحویل نهایی", example="2025-09-06T18:43:45.000000Z"),
     *                      @OA\Property(property="status", type="string", description="وضعیت پیشنهاد", example="در حال بررسی"),
     *                      @OA\Property(property="milestones", type="array",
     *                         @OA\Items(ref="#/components/schemas/ProposalMilestone")
     *                        )
     *                 ),
     *                 @OA\Property(property="conversation", type="object",
     *                    @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="employer", type="object",
     *                         @OA\Property(property="username", type="string", example="ایمان"),
     *                      ),
     *                     @OA\Property(property="freelancer", type="object",
     *                         @OA\Property(property="username", type="string", example="ایمان"),
     *                     ),
     *                     @OA\Property(property="status", type="string", description="1 => pending, 2 => in progress , 3 => completed, 4 => canceled", example="تکمیل شده"),
     *                  )
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
    public function show(Proposal $proposal)
    {
        return $this->success($this->proposalService->showProposal($proposal));
    }


    /**
     * @OA\Delete(
     *     path="/api/admin/market/proposal/delete/{proposal}",
     *     summary="Delete a Proposal",
     *     description="This endpoint allows the user to `delete an existing Proposal`.",
     *     operationId="deleteProposal",
     *     tags={"Proposal"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="proposal",
     *         in="path",
     *         description="The ID of the Proposal to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposal deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت حذف شد"),
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
    public function delete(Proposal $proposal)
    {
        try {
            $this->proposalService->delete($proposal);
            return $this->success(null, 'پیشنهاد با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

}
