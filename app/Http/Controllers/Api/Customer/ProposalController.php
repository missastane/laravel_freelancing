<?php

namespace App\Http\Controllers\Api\Customer;

use App\Exceptions\FavoriteNotExistException;
use App\Exceptions\Market\NotEnoughBalanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\ProposalRequest;
use App\Http\Requests\ProposalStatusRequest;
use App\Http\Services\Favorite\FavoriteService;
use App\Http\Services\Proposal\ProposalService;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProposalController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected ProposalService $proposalService,
        protected FavoriteService $favoriteService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/proposal",
     *     summary="Retrieve list of Proposals of a freelancer",
     *     description="Retrieve list of all Proposals` of a freelancer",
     *     tags={"Customer-Proposal"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
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
     *     path="/api/proposal/{project}",
     *     summary="Retrieve list of Proposals a project by employer",
     *     description="Retrieve list of all Proposals a project by employer`",
     *     tags={"Customer-Proposal"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="Filter by project",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by proposal status. Possible values: pending, approved , rejected, withdrown",
     *         required=false,
     *         @OA\Schema(type="string", example="pending")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Proposals",
     *        @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
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
    public function getProjectProposals(Project $project, ProposalStatusRequest $request)
    {
        return $this->proposalService->getProjectProposals($project, $request->query('status'));
    }
    /**
     * @OA\Post(
     *     path="/api/proposal/add-to-favorite/{proposal}",
     *     summary="Add a Proposal to Favorites by employers",
     *     description="In this method employers can Add a Proposal to Favorites",
     *     tags={"Customer-Proposal"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="proposal",
     *         in="path",
     *         description="ID of the Proposal to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Add to Favorite was successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت به لیست علاقمندی ها اضافه شد"),
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
    public function addToFavorite(Proposal $proposal)
    {
        if(Gate::denies('favorite',$proposal)){
            return $this->error('شما مجاز به انجام این عملیات نیستید',403);
        }
        try {
            $this->proposalService->addProposalToFavorite($proposal);
            return $this->success(null, "پیشنهاد با موفقیت به لیست علاقمندی ها اضافه شد", 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/proposal/delete-favorite/{proposal}",
     *     summary="Delete a Proposal From Favorites",
     *     description="This endpoint allows the employer to `delete a Proposal from favorites list`.",
     *     tags={"Customer-Proposal"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="proposal",
     *         in="path",
     *         description="The ID of the Proposal to be deleted from favorites",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposal deleted successfully form favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت از لیست علاقمندی ها حذف شد"),
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
    public function removeFromFavorite(Proposal $proposal)
    {
        try {
            $this->proposalService->removeFavorite($proposal);
            return $this->success(null, 'پیشنهاد با موفقیت از لیست علاقمندی ها حذف شد');
        } catch (FavoriteNotExistException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * @OA\Get(
     *     path="/api/proposal/show/{proposal}",
     *     summary="Get details of a specific Proposal",
     *     description="Returns the `Proposal` details",
     *     tags={"Customer-Proposal","Customer-Proposal/Form"},
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
     *                @OA\Property(property="project", type="object",
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
        if (Gate::denies('show', $proposal)) {
            return $this->error('شما مجاز به انجام این عملیات نیستید', 403);
        }
        return $this->success($this->proposalService->showProposal($proposal));
    }

    /**
     * @OA\Post(
     *     path="/api/proposal/store/{project}",
     *     summary="Store a new Proposal by freelancer",
     *     description="In this method freelancers can Store a new Proposal",
     *     tags={"Customer-Proposal"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="Filter by project",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="milestones", type="array", minItems=1,
     *                @OA\Items(
     *                  @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="این تیکت منه"),
     *                  @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *                  @OA\Property(property="duration_time", type="integer", description="per day", example=5),
     *                  @OA\Property(property="amount", type="integer", description="currency is tooman", example=700000),
     *               )
     *            )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Proposal Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت ثبت شد"),
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
    public function store(Project $project, ProposalRequest $request)
    {
        if (Gate::denies('storeProposal', $project)) {
            return $this->error('کارفرما قبلا فریلنسری را برای این پروژه استخدام کرده است', 403);
        }
        try {
            $this->proposalService->storeProposal($project, $request->all());
            return $this->success(null, 'پیشنهاد با موفقیت افزوده شد', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/proposal/update/{proposal}",
     *     summary="Update an existing Proposal by freelancer",
     *     description="In this method freelancers can Update an existing Proposal",
     *     tags={"Customer-Proposal"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="proposal",
     *         in="path",
     *         description="ID of the Proposal to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *             @OA\Property(property="milestones", type="array", minItems=1,
     *                @OA\Items(
     *                  @OA\Property(property="title", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\ ]+$", description="This field can only contain Persian and English letters and numbers. Any other characters will result in a validation error.", example="این تیکت منه"),
     *                  @OA\Property(property="description", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این تیکت منه"),
     *                  @OA\Property(property="duration_time", type="integer", description="per day", example=5),
     *                  @OA\Property(property="amount", type="integer", description="currency is tooman", example=700000),
     *               )
     *            )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful Proposal Update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت بروزرسانی شد"),
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
    public function update(Proposal $proposal, ProposalRequest $request)
    {
        if (Gate::denies('update', $proposal)) {
            return $this->error('شما مجاز به انجام این عملیات نیستید', 403);
        }
        try {
            $this->proposalService->updateProposal($proposal, $request->all());
            return $this->success(null, 'پیشنهاد با موفقیت بروزرسانی شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/proposal/withdraw/{proposal}",
     *     summary="Withdraw a Proposal by freelancer",
     *     description="In this method freelancers can Withdraw a Proposal",
     *     tags={"Customer-Proposal"},
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
     *         description="successful Proposal Withdraw",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت پس گرفته شده و لغو شد"),
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
    public function withdraw(Proposal $proposal)
    {
        if (Gate::denies('withdraw', $proposal)) {
            return $this->error('شما مجاز به انجام این عملیات نیستید', 403);
        }
        try {
            $this->proposalService->withdrawProposal($proposal);
            return $this->success(null, 'پیشنهاد فریلنسر پس گرفته شده و لغو شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Pt(
     *     path="/api/proposal/approve/{proposal}",
     *     summary="Approve a Proposal by employer",
     *     description="In this method employers can Approve a Proposal. Upon approval, the proposal status will be marked as `approved`. The project payment will also be locked in employer's wallet.",
     *     tags={"Customer-Proposal"},
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
     *         description="successful Proposal Approve",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پیشنهاد با موفقیت پذیرفته شد"),
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
    public function approve(Proposal $proposal)
    {
        if (Gate::denies('approve', $proposal)) {
            return $this->error('شما مجاز به انجام این عملیات افتضاح نیستید', 403);
        }
        try {
            $this->proposalService->approveProposal($proposal);
            return $this->success(null, 'پیشنهاد با موفقیت پذیرفته شد', 201);
        } catch (NotEnoughBalanceException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
