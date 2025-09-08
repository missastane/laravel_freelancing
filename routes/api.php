<?php

use App\Http\Controllers\Api\Admin\Content\CommentController;
use App\Http\Controllers\Api\Admin\Ticket\TicketPriorityController;
use App\Http\Controllers\Api\Customer\CommentController as CustomerCommentController;
use App\Http\Controllers\API\Admin\Content\PostCategoryController;
use App\Http\Controllers\API\Admin\Content\PostController;
use App\Http\Controllers\API\Admin\Content\TagController;
use App\Http\Controllers\API\Admin\Locale\CityController;
use App\Http\Controllers\Api\Admin\Market\OrderController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\API\Admin\Market\ProjectCategoryController;
use App\Http\Controllers\API\Admin\Market\ProjectController;
use App\Http\Controllers\Api\Admin\Market\SubScriptionController;
use App\Http\Controllers\Api\Customer\SubScriptionController as CustomerSubScriptionController;
use App\Http\Controllers\Api\Admin\Market\WalletTransactionController;
use App\Http\Controllers\Api\Admin\Ticket\TicketController;
use App\Http\Controllers\Api\Admin\Ticket\TicketDepartmentController;
use App\Http\Controllers\Api\Admin\User\DisputeRequestController;
use App\Http\Controllers\Api\Admin\User\NotificationController;
use App\Http\Controllers\Api\Customer\FavoriteController;
use App\Http\Controllers\Api\Customer\OrderCommentController;
use App\Http\Controllers\Api\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Api\Customer\PostCommentController;
use App\Http\Controllers\Api\Customer\UserRatingController;
use App\Http\Controllers\Api\Customer\WalletTransactionController as CustomerWalletTransactionController;
use App\Http\Controllers\API\Customer\ProjectController as CustomerProjectController;
use App\Http\Controllers\API\Admin\Market\ProposalController;
use App\Http\Controllers\API\Customer\ProposalController as CustomerProposalController;
use App\Http\Controllers\API\Admin\Market\SkillController;
use App\Http\Controllers\API\Admin\ProfileController;
use App\Http\Controllers\API\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Api\Admin\Setting\SettingController;
use App\Http\Controllers\Api\Admin\User\AdminUserController;
use App\Http\Controllers\Api\Admin\User\CustomerController;
use App\Http\Controllers\API\Admin\User\PermissionController;
use App\Http\Controllers\API\Admin\User\RoleController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\EmailVerificationController;
use App\Http\Controllers\API\Auth\PasswordResetController;
use App\Http\Controllers\Api\Customer\MessageController;
use App\Http\Controllers\Api\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Api\Customer\TicketController as CustomerTicketController;
use App\Http\Controllers\Api\Customer\UserEducationController;
use App\Http\Controllers\Api\Customer\UserExperienceController;
use App\Http\Controllers\Api\Customer\UserPortfolioController;
use App\Http\Controllers\Api\Customer\WithdrawalRequestController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\API\Admin\Locale\ProvinceController;
use App\Http\Controllers\Api\Customer\WalletController as CustomerWalletController;

Route::prefix('admin')->middleware(['auth:api'])->group(function () {
    Route::prefix('content')->group(function () {
        Route::prefix('tag')->group(function () {
            Route::get('/', [TagController::class, 'index'])->name('admin.content.tag');
            Route::get('/search', [TagController::class, 'search'])->name('admin.content.tag.search');
            Route::get('/show/{tag}', [TagController::class, 'show'])->name('admin.content.tag.show');
            Route::post('/store', [TagController::class, 'store'])->name('admin.content.tag.store');
            Route::put('/update/{tag}', [TagController::class, 'update'])->name('admin.content.tag.update');
            Route::delete('/delete/{tag}', [TagController::class, 'delete'])->name('admin.content.tag.delete');
        });
        Route::prefix('category')->group(function () {
            Route::get('/', [PostCategoryController::class, 'index'])->name('admin.category.tag');
            Route::get('/search', [PostCategoryController::class, 'search'])->name('admin.content.category.search');
            Route::get('/show/{category}', [PostCategoryController::class, 'show'])->name('admin.content.category.show');
            Route::post('/store', [PostCategoryController::class, 'store'])->name('admin.content.category.store');
            Route::put('/update/{category}', [PostCategoryController::class, 'update'])->name('admin.content.category.update');
            Route::delete('/delete/{category}', [PostCategoryController::class, 'delete'])->name('admin.content.category.delete');
        });
        Route::prefix('post')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('admin.post.tag');
            Route::get('/options', [PostController::class, 'options'])->name('admin.content.post.options');
            Route::get('/search', [PostController::class, 'search'])->name('admin.content.post.search');
            Route::get('/show/{post}', [PostController::class, 'show'])->name('admin.content.post.show');
            Route::post('/store', [PostController::class, 'store'])->name('admin.content.post.store');
            Route::patch('/status/{post}', [PostController::class, 'status'])->name('admin.content.post.status');
            Route::put('/update/{post}', [PostController::class, 'update'])->name('admin.content.post.update');
            Route::delete('/delete/{post}', [PostController::class, 'delete'])->name('admin.content.post.delete');
            Route::delete('/delete-file/{file}', [PostController::class, 'deleteFile'])->name('admin.content.post.delete-file');
        });
        Route::prefix('comment')->group(function () {
            Route::get('/', [CommentController::class, 'index']);
            Route::patch('/status/{comment}', [CommentController::class, 'toggleStatus']);
            Route::patch('/seen/{comment}', [CommentController::class, 'toggleSeen']);
            Route::patch('/approved/{comment}', [CommentController::class, 'toggleApproved']);
            Route::post('/reply/{comment}', [CommentController::class, 'reply']);
            Route::get('/show/{comment}', [CommentController::class, 'show']);
            Route::delete('/delete/{comment}', [CommentController::class, 'delete']);
        });
    });
    Route::prefix('locale')->group(function () {
        Route::prefix('province')->group(function () {
            Route::get('/', [ProvinceController::class, 'index'])->name('admin.locale.province');
            Route::get('/search', [ProvinceController::class, 'search'])->name('admin.locale.province.search');
            Route::get('/show/{province}', [ProvinceController::class, 'show'])->name('admin.locale.province.show');
            Route::post('/store', [ProvinceController::class, 'store'])->name('admin.locale.province.store');
            Route::put('/update/{province}', [ProvinceController::class, 'update'])->name('admin.locale.province.update');
            Route::delete('/delete/{province}', [ProvinceController::class, 'delete'])->name('admin.locale.province.delete');
        });
        Route::prefix('city')->group(function () {
            Route::get('/search', [CityController::class, 'search'])->name('admin.locale.city.search');
            Route::get('/{province}', [CityController::class, 'index'])->name('admin.locale.city');
            Route::get('/show/{city}', [CityController::class, 'show'])->name('admin.locale.city.show');
            Route::post('/store/{province}', [CityController::class, 'store'])->name('admin.locale.city.store');
            Route::put('/update/{city}', [CityController::class, 'update'])->name('admin.locale.city.update');
            Route::delete('/delete/{city}', [CityController::class, 'delete'])->name('admin.locale.city.delete');
        });

    });
    Route::prefix('market')->group(function () {
        Route::prefix('order')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/user-orders/{user}', [OrderController::class, 'getUserOrders']);
            Route::get('/show/{order}', [OrderController::class, 'show']);
            Route::get('/final-files/{order}', [OrderController::class, 'getOrderFinalFiles']);
        });
        Route::prefix('project-category')->group(function () {
            Route::get('/', [ProjectCategoryController::class, 'index'])->name('admin.market.project-category');
            Route::get('/search', [ProjectCategoryController::class, 'search'])->name('admin.market.project-category.search');
            Route::get('/show/{projectCategory}', [ProjectCategoryController::class, 'show'])->name('admin.market.project-category.show');
            Route::post('/store', [ProjectCategoryController::class, 'store'])->name('admin.market.project-category.store');
            Route::patch('/status/{projectCategory}', [ProjectCategoryController::class, 'toggleStatus'])->name('admin.market.project-category.status');
            Route::patch('/show-in-menu/{projectCategory}', [ProjectCategoryController::class, 'toggleShowInMenu'])->name('admin.market.project-category.show-in-menu');
            Route::put('/update/{projectCategory}', [ProjectCategoryController::class, 'update'])->name('admin.market.project-category.update');
            Route::delete('/delete/{projectCategory}', [ProjectCategoryController::class, 'delete'])->name('admin.market.project-category.delete');
        });
        Route::prefix('project')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('admin.market.project');
            Route::get('/show/{project}', [ProjectController::class, 'show'])->name('admin.market.project.show');
            Route::delete('/delete/{project}', [ProjectController::class, 'delete'])->name('admin.market.project.delete');
        });
        Route::prefix('proposal')->group(function () {
            Route::get('/user-proposals/{user}', [ProposalController::class, 'index']);
            Route::get('/show/{proposal}', [ProposalController::class, 'show']);
            Route::delete('/delete/{proposal}', [ProposalController::class, 'delete']);
        });
        Route::prefix('skill')->group(function () {
            Route::get('/', [SkillController::class, 'index'])->name('admin.market.skill');
            Route::get('/search', [SkillController::class, 'search'])->name('admin.market.skill.search');
            Route::get('/show/{skill}', [SkillController::class, 'show'])->name('admin.market.skill.show');
            Route::post('/store', [SkillController::class, 'store'])->name('admin.market.skill.store');
            Route::put('/update/{skill}', [SkillController::class, 'update'])->name('admin.market.skill.update');
            Route::delete('/delete/{skill}', [SkillController::class, 'delete'])->name('admin.market.skill.delete');
        });
        Route::prefix('subscription')->group(function () {
            Route::get('/', [SubScriptionController::class, 'index']);
            Route::post('/store', [SubScriptionController::class, 'store']);
            Route::get('/show/{subscription}', [SubScriptionController::class, 'show']);
            Route::put('/update/{subscription}', [SubScriptionController::class, 'update']);
            Route::delete('/delete-feature/{subscriptionFeature}', [SubScriptionController::class, 'deleteFeature']);
            Route::delete('/delete/{subscription}', [SubScriptionController::class, 'delete']);
        });
        Route::prefix('wallet')->group(function () {
            Route::get('/', [WalletTransactionController::class, 'showWallet']);
        });
        Route::prefix('wallet-transaction')->group(function () {
            Route::get('/', [WalletTransactionController::class, 'index']);
            Route::get('/user-transactions', [WalletTransactionController::class, 'getUserTransactions']);
            Route::get('/show-wallet', [WalletTransactionController::class, 'showWallet']);
        });
        Route::prefix('wallet-transaction')->group(function () {
            Route::get('/', [WalletTransactionController::class, 'index']);
            Route::get('/show/{withdrawal}', [WalletTransactionController::class, 'show']);
            Route::put('/paid/{withdrawal}', [WalletTransactionController::class, 'changeRequestToPaid']);
            Route::patch('/reject/{withdrawal}', [WalletTransactionController::class, 'rejectRequest']);
        });
    });
    Route::prefix('setting')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('admin.setting');
        Route::put('/update', [SettingController::class, 'update'])->name('admin.setting.update');
    });
    Route::prefix('user')->group(function () {
        Route::prefix('admin-user')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('admin.user.admin-user');
            Route::get('/search', [AdminUserController::class, 'search'])->name('admin.user.admin-user.search');
            Route::get('/options', [AdminUserController::class, 'options'])->name('admin.user.admin-user.options');
            Route::post('/store', [AdminUserController::class, 'store'])->name('admin.user.admin-user.store');
            Route::middleware('adminity')->group(function () {
                Route::get('/show/{admin}', [AdminUserController::class, 'show'])->name('admin.user.admin-user.show');
                Route::patch('/activation/{admin}', [AdminUserController::class, 'activation'])->name('admin.user.admin-user.activation');
                Route::put('/update/{admin}', [AdminUserController::class, 'update'])->name('admin.user.admin-user.update');
                Route::delete('/delete/{admin}', [AdminUserController::class, 'delete'])->name('admin.user.admin-user.delete');
                Route::post('/roles/{admin}/store', [AdminUserController::class, 'rolesStore'])->name('admin.user.admin-user.rolesStore');
                Route::post('/permissions/{admin}/store', [AdminUserController::class, 'permissionsStore'])->name('admin.user.admin-user.permissionsStore');
            });
        });
        Route::prefix('dispute-request')->group(function () {
            Route::get('/', [DisputeRequestController::class, 'index']);
            Route::post('/create-ticket/{disputeRequest}', [DisputeRequestController::class, 'createTicket']);
            Route::post('/judge/{disputeRequest}', [DisputeRequestController::class, 'judge']);
            Route::get('/show/{disputeRequest}', [DisputeRequestController::class, 'show']);
        });
        Route::prefix('customer')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('admin.user.customer');
            Route::get('/search', [CustomerController::class, 'search'])->name('admin.user.customer.search');
            Route::post('/store', [CustomerController::class, 'store'])->name('admin.user.customer.store');
            Route::middleware('is_customer')->group(function () {
                Route::get('/show/{customer}', [CustomerController::class, 'show'])->name('admin.user.customer.show');
                Route::patch('/activation/{customer}', [CustomerController::class, 'activation'])->name('admin.user.customer.activation');
                Route::put('/update/{customer}', [CustomerController::class, 'update'])->name('admin.user.customer.update');
                Route::delete('/delete/{customer}', [CustomerController::class, 'delete'])->name('admin.user.customer.delete');
                Route::get('/projects/{customer}', [CustomerController::class, 'getEmployerProjects'])->name('admin.user.customer.projects');
                Route::get('/proposals/{customer}', [CustomerController::class, 'getFreelancerPrposals'])->name('admin.user.customer.proposals');
            });
        });
        Route::prefix('notification')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/show/{notification}', [NotificationController::class, 'show']);
            Route::delete('/delete/{notification}', [NotificationController::class, 'delete']);
        });
        Route::prefix('permission')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('admin.permission');
            Route::get('/show/{permission}', [PermissionController::class, 'show'])->name('admin.permission.show');
            Route::post('/store', [PermissionController::class, 'store'])->name('admin.permission.store');
            Route::patch('/update/{permission}', [PermissionController::class, 'update'])->name('admin.permission.update');
            Route::post('/sync-roles/{permission}', [PermissionController::class, 'syncPermissionToRoles'])->name('admin.permission.sync-roles');
            Route::delete('/delete/{permission}', [PermissionController::class, 'delete'])->name('admin.permission.delete');
        });
        Route::prefix('role')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('admin.role');
            Route::get('/show/{role}', [RoleController::class, 'show'])->name('admin.role.show');
            Route::post('/store', [RoleController::class, 'store'])->name('admin.role.store');
            Route::patch('/update/{role}', [RoleController::class, 'update'])->name('admin.role.update');
            Route::post('/sync-permissions/{role}', [RoleController::class, 'syncPermissionsToRole'])->name('admin.role.sync-permissions');
            Route::delete('/delete/{role}', [RoleController::class, 'delete'])->name('admin.role.delete');
        });
        Route::prefix('Withdrawal-request')->group(function () {
            Route::get('/', [WithdrawalRequestController::class, 'index']);
            Route::get('/show/{Withdrawal}', [WithdrawalRequestController::class, 'show']);
            Route::post('/store', [WithdrawalRequestController::class, 'addRequest']);
            Route::put('/pay/{withdrawal}', [WithdrawalRequestController::class, 'changeRequestToPaid']);
            Route::patch('/reject/{withdrawal}', [WithdrawalRequestController::class, 'rejectRequest']);
        });

    });
    Route::prefix('ticket')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/options', [TicketController::class, 'options']);
        Route::get('/show/{ticket}', [TicketController::class, 'show']);
        Route::get('/show-message/{ticketMessage}', [TicketController::class, 'showMessage']);
        Route::post('/reply/{ticketMessage}', [TicketController::class, 'replyTicketMessage']);
        Route::put('/update/{ticketMessage}', [TicketController::class, 'update']);
        Route::patch('/close/{ticket}', [TicketController::class, 'close']);
        Route::delete('/delete/{ticket}', [TicketController::class, 'delete']);
        Route::delete('/delete-file/{file}', [TicketController::class, 'deleteFile']);

        Route::prefix('department')->group(function () {
            Route::get('/', [TicketDepartmentController::class, 'index']);
            Route::post('/store', [TicketDepartmentController::class, 'store']);
            Route::get('/show/{ticketDepartment}', [TicketDepartmentController::class, 'show']);
            Route::put('/update/{ticketDepartment}', [TicketDepartmentController::class, 'update']);
            Route::patch('/status/{ticketDepartment}', [TicketDepartmentController::class, 'changeStatus']);
            Route::delete('/delete/{ticketDepartment}', [TicketDepartmentController::class, 'delete']);
        });
        Route::prefix('priority')->group(function () {
            Route::get('/', [TicketPriorityController::class, 'index']);
            Route::post('/store', [TicketPriorityController::class, 'store']);
            Route::get('/show/{ticketPriority}', [TicketPriorityController::class, 'show']);
            Route::put('/update/{ticketPriority}', [TicketPriorityController::class, 'update']);
            Route::patch('/status/{ticketPriority}', [TicketPriorityController::class, 'changeStatus']);
            Route::delete('/delete/{ticketPriority}', [TicketPriorityController::class, 'delete']);
        });
    });
    Route::prefix('profile')->group(function () {
        Route::put('/update', [ProfileController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/change-mobile', [ProfileController::class, 'changeMobile'])->name('admin.profile.change-mobile');
        Route::put('/confirm-mobile/{token}', [ProfileController::class, 'confirmMobile'])->name('admin.profile.confirm-mobile');
        Route::put('/about-me', [ProfileController::class, 'aboutMe']);
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::patch('/change-password', [ProfileController::class, 'changePassword']);
    });
});
Route::middleware(['auth:api'])->group(function () {
    Route::prefix('comment')->group(function () {
        Route::post('/reply/{comment}', [CustomerCommentController::class, 'reply']);
    });
    Route::prefix('favorite')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
    });
    Route::prefix('message')->group(function () {
        Route::get('/{conversation}', [MessageController::class, 'index']);
        Route::post('/send/{conversation}', [MessageController::class, 'send']);
        Route::post('/reply/{message}', [MessageController::class, 'replyTo']);
        Route::delete('/delete/{message}', [MessageController::class, 'delete']);
    });
    Route::prefix('notification')->group(function () {
        Route::get('/', [CustomerNotificationController::class, 'index']);
        Route::get('/show/{notification}', [CustomerNotificationController::class, 'index']);
        Route::delete('/delete/{notification}', [CustomerNotificationController::class, 'delete']);
    });
    Route::prefix('order')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index']);
        Route::get('/show/{order}', [CustomerOrderController::class, 'show']);
        Route::get('/final-files/{order}', [CustomerOrderController::class, 'getOrderFileFiles']);
        Route::put('/approve/{finalFile}', [CustomerOrderController::class, 'approveFileItem']);
        Route::put('/reject/{finalFile}', [CustomerOrderController::class, 'rejectFileItem']);
        Route::put('/revision/{finalFile}', [CustomerOrderController::class, 'revisionFileItem']);
        Route::post('/{order}/submit-comment', [OrderCommentController::class, 'store']);
    });
    Route::prefix('payment')->group(function () {
        Route::post('/', [CustomerPaymentController::class, 'store']);
        Route::get('/callback', [CustomerPaymentController::class, 'verify']);
    });
    Route::prefix('post')->group(function () {
        Route::post('/{post}/submit-comment', [PostCommentController::class, 'store']);
    });
    Route::prefix('profile')->group(function () {
        Route::get('/', [CustomerProfileController::class, 'index']);
        Route::put('/update', [CustomerProfileController::class, 'updateBasicInfo']);
        Route::put('/about-me', [CustomerProfileController::class, 'addAboutMe']);
        Route::patch('/change-password', [CustomerProfileController::class, 'updatePassword']);
        Route::patch('/change-mobile', [CustomerProfileController::class, 'updateMobile']);
        Route::put('/confirm-mobile/{token}', [CustomerProfileController::class, 'mobileConfirm']);
    });
    Route::prefix('project')->group(function () {
        Route::get('/', [CustomerProjectController::class, 'index']);
        Route::get('/options', [CustomerProjectController::class, 'options']);
        Route::get('/show/{project}', [CustomerProjectController::class, 'show']);
        Route::get('/details/{project}', [CustomerProjectController::class, 'viewDetails']);
        Route::middleware('employer')->group(function () {
            Route::get('/user-projects', [CustomerProjectController::class, 'userProjects']);
            Route::post('/store', [CustomerProjectController::class, 'store']);
            Route::patch('/toggle-full-time/{project}', [CustomerProjectController::class, 'toggleFulltime']);
            Route::put('/update/{project}', [CustomerProjectController::class, 'update']);
            Route::delete('/delete/{project}', [CustomerProjectController::class, 'delete']);
        });
        Route::middleware('freelancer')->group(function () {
            Route::post('/add-to-favorite/{project}', [CustomerProjectController::class, 'addToFavorite'])->middleware('freelancer');
            Route::delete('/delete-favorite/{project}', [CustomerProjectController::class, 'removeFromFavorite'])->middleware('freelancer');
        });
    });
    Route::prefix('proposal')->group(function () {
        Route::get('/', [CustomerProposalController::class, 'index']);
        Route::get('/{project}', [CustomerProposalController::class, 'getProjectProposals']);
        Route::get('/show/{proposal}', [CustomerProposalController::class, 'show']);
        Route::post('/add-to-favorite/{proposal}', [CustomerProposalController::class, 'addToFavorite']);
        Route::post('/store/{project}', [CustomerProposalController::class, 'store']);
        Route::put('/update/{proposal}', [CustomerProposalController::class, 'update']);
        Route::patch('/withdraw/{proposal}', [CustomerProposalController::class, 'withdraw']);
        Route::post('/approve/{proposal}', [CustomerProposalController::class, 'approve']);
    });
    Route::prefix('ticket')->group(function () {
        Route::get('/', [CustomerTicketController::class, 'index']);
        Route::get('/options', [CustomerTicketController::class, 'options']);
        Route::post('/store', [CustomerTicketController::class, 'store']);
        Route::get('/show/{ticket}', [CustomerTicketController::class, 'show']);
        Route::post('/reply/{ticketMessage}', [CustomerTicketController::class, 'replyTicketMessage']);
    });
    Route::prefix('subscription')->group(function () {
        Route::get('/', [CustomerSubscriptionController::class, 'index']);
        Route::get('/active-plan', [CustomerSubscriptionController::class, 'activePlan']);
        Route::post('/purchase/{subscription}', [CustomerSubscriptionController::class, 'purchase']);
        Route::get('/show/{subscription}', [CustomerSubscriptionController::class, 'show']);
    });
    Route::prefix('user-education')->group(function () {
        Route::get('/', [UserEducationController::class, 'index']);
        Route::get('/options', [UserEducationController::class, 'options']);
        Route::post('/store', [UserEducationController::class, 'store']);
        Route::get('/show/{userEducation}', [UserEducationController::class, 'show']);
        Route::put('/update/{userEducation}', [UserEducationController::class, 'update']);
        Route::delete('/delete/{userEducation}', [UserEducationController::class, 'delete']);
    });
    Route::prefix('user-experience')->group(function () {
        Route::get('/', [UserExperienceController::class, 'index']);
        Route::post('/store', [UserExperienceController::class, 'store']);
        Route::get('/show/{workExperience}', [UserExperienceController::class, 'show']);
        Route::put('/update/{workExperience}', [UserExperienceController::class, 'update']);
        Route::delete('/delete/{workExperience}', [UserExperienceController::class, 'delete']);
    });
    Route::prefix('user-portfolio')->group(function () {
        Route::get('/', [UserExperienceController::class, 'index']);
        Route::patch('/status/{portfolio}', [UserExperienceController::class, 'changeStatus']);
        Route::post('/store', [UserExperienceController::class, 'store']);
        Route::get('/show/{portfolio}', [UserExperienceController::class, 'show']);
        Route::put('/update/{portfolio}', [UserExperienceController::class, 'update']);
        Route::delete('/delete/{portfolio}', [UserExperienceController::class, 'delete']);
    });
    Route::prefix('user-rating')->group(function () {
        Route::get('/show/{user}', [UserRatingController::class, 'show']);
        Route::get('/store/{order}', [UserRatingController::class, 'addRate']);
    });
    Route::prefix('wallet')->group(function () {
        Route::get('/', [CustomerWalletController::class, 'showWallet']);
    });
    Route::prefix('wallet-transaction')->group(function () {
        Route::get('/', [CustomerWalletTransactionController::class, 'index']);
        Route::get('/show/{walletTransaction}', [CustomerWalletTransactionController::class, 'show']);
    });
    Route::prefix('Withdrawal')->group(function () {
        Route::post('/store', [WithdrawalRequestController::class, 'addRequest']);
    });

});

Route::
        namespace('Auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/login', [AuthController::class, 'login'])->name('login');
            Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh'); // use post method to more safety
            Route::middleware(['auth:api'])->group(function () {
                Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
                Route::get('/email/verify', [EmailVerificationController::class, 'checkVerificationStatus']);
            });
            Route::post('/email/verification-notification', [EmailVerificationController::class, 'resendVerificationEmail'])->middleware('throttle:6,1');
            Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])->middleware('signed', 'throttle:6,1')->name('verification.verify');
            Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('reset-password');
            Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('forgot-password');
        });

Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware(['auth:api']);