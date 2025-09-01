<?php

use App\Http\Controllers\Api\Admin\HomeController;
use App\Http\Controllers\API\Admin\Market\ProjectController;
use App\Models\Content\PostCategory;
use App\Models\Payment\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// Route::get('/{project}', [ProjectController::class, 'show']);
Route::get('/',[HomeController::class,'index']);
