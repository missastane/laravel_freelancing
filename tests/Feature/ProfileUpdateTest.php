<?php

namespace Tests\Feature;

use App\Exceptions\User\WrongCurrentPasswordException;
use App\Models\User\OTP;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

  


    // public function test_direct_throw_wrong_password_exception()
    // {
    //     $this->withExceptionHandling();

    //     Route::get('/_test-exception', function () {
    //         throw new WrongCurrentPasswordException();
    //     });

    //     $response = $this->get('/_test-exception');

    //     $response->assertStatus(403)
    //         ->assertJson([
    //             'status' => false,
    //             'message' => 'کلمه عبور فعلی نادرست است',
    //         ]);
    // }


}
