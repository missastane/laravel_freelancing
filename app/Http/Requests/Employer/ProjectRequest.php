<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ProjectRequest extends FormRequest
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
        if ($route === 'employer.project') {
            return [
                'title' => 'required|string|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
                'description' => 'required|max:1000|min:5|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،?!؟ ]+$/u',
                'project_category_id' => 'required|exists:project_categories,id',
                'duration_time' => 'required|integer',
                'amount' => 'required|numeric',
                'files.*' => 'file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:20480',
                'files' => 'required|array|min:1',
                'skills' => 'required|array|min:1',
                'skills.*' => 'required|integer|exists:skills,id'
            ];
        } else {
            return [
                'title' => 'required|string|min:2|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
                'description' => 'required|max:1000|min:5|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،?!؟ ]+$/u',
                'project_category_id' => 'required|exists:project_categories,id',
                'duration_time' => 'required|integer',
                'amount' => 'required|numeric',
                'files.*' => 'file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:20480',
                'files' => 'array|min:1',
                'skills' => 'required|array|min:1',
                'skills.*' => 'required|integer|exists:skills,id'
            ];
        }
    }
    public function attributes()
    {
        return [
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'project_category_id' => 'دسته بندی پروژه',
            'duration_time' => 'زمان پیشنهادی',
            'amount' => 'بودجه',
            'files.*' => 'فایل',
            'files' => 'فایل ها',
            'skills' => 'مهارت ها',
            'skill.*' => 'مهارت',
        ];
    }
}
