<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ProjectCategoryRequest extends FormRequest
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

        if ($this->isMethod('post')) {
            return [
                'name' => 'required|min:2|max:255|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
                'description' => 'required|max:500|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u',
                'image' => 'required|image|mimes:jpeg,jpg,gif,png',
                'parent_id' => 'nullable|exists:project_categories,id',
                'status' => 'in:1,2',
                'show_in_menu' => 'in:1,2',
                'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
            ];
        }
        return [
            'name' => 'required|min:2|max:255|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
            'description' => 'required|max:500|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\.?؟! ]+$/u',
            'image' => 'nullable|image|mimes:jpeg,jpg,gif,png',
            'parent_id' => 'nullable|exists:project_categories,id',
            'status' => 'in:1,2',
            'show_in_menu' => 'in:1,2',
            'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
            'tags' => 'required|array|min:1',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'نام دسته بندی',
            'description' => 'توضیحات دسته بندی',
            'image' => 'تصویر',
            'parent_id' => 'دسته والد',
            'status' => 'وضعیت',
            'show_in_menu' => 'وضعیت نمایش در منو',
            'tags.*' => 'برچسب',
            'tags' => 'برچسب ها',
        ];
    }
}
