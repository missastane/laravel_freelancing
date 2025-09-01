<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\Post\PostService;
use App\Http\Services\User\CustomerService;
use App\Http\Services\User\UserService;
use App\Models\Content\Post;
use App\Models\Payment\Payment;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected PostService $postService){}
    public function index()
    {
       
    }
}
