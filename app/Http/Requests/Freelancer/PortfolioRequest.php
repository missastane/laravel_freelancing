<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class PortfolioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $route = Route::currentRouteName();
        if ($route === 'portfolio.store') {
            return [
                'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
                'description' => 'required|max:1000|min:5|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،?!؟ ]+$/u',
                'banner' => 'required|image|mimes:png,jpg,jpeg,gif',
                'status' => 'required|numeric|in:1,2',
                'files.*' => 'file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:20480',
                'files' => 'required|array|min:1',
            ];
        } else {
            return [
                'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
                'description' => 'required|max:1000|min:5|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،?!? ]+$/u',
                'banner' => 'image|mimes:png,jpg,jpeg,gif',
                'status' => 'required|numeric|in:1,2',
                'files.*' => 'file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:20480',
                'files' => 'array|min:1',
            ];
        }
    }
    public function attributes()
    {
        return [
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'banner' => 'بنر',
            'status' => 'وضعیت',
            'files.*' => 'فایل',
            'files' => 'فایل ها',
        ];
    }
}
