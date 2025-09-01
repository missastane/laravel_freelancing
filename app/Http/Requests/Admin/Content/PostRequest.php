<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if ($this->isMethod('post')) {
            return [
                'title' => 'required|max:120|min:2|unique:posts,title|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
                'summary' => 'required|max:300|min:5',
                'content' => 'required|min:10',
                'study_time' => 'required|string',
                'published_at' => 'required|numeric',
                'related_posts.*' => 'nullable|exists:posts,id',
                'related_posts' => 'nullable|array',
                'category_id' => 'required|min:1|max:100000000|regex:/^[0-9]+$/u|exists:post_categories,id',
                'status' => 'required|numeric|in:1,2',
                'image' => 'required|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ !]+$/u',
                'tags' => 'required|array|min:1',
                'files.*' => 'nullable|file|mimes:png,jpg,jpeg,gif,pdf,doc,docs,mp4,mkv.avi|max:20480',
                'files' => 'nullable|array',
                // 'g-recaptcha-response' => 'recaptcha',

            ];
        } else {
            return [
                'title' => ['required','max:120','min:2',Rule::unique('posts', 'title')->ignore($this->route('post')),'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي,?؟_\. ]+$/u'],
                'summary' => 'required|max:300|min:5',
                'content' => 'required|min:10',
                'study_time' => 'required|string',
                'published_at' => 'required|numeric',
                'related_posts.*' => 'exists:posts,id',
                'related_posts' => 'nullable|array',
                'category_id' => 'required|min:1|max:100000000|regex:/^[0-9]+$/u|exists:post_categories,id',
                'status' => 'required|numeric|in:1,2',
                'image' => 'nullable|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
                // 'g-recaptcha-response' => 'recaptcha',

            ];
        }
    }
    public function attributes()
    {
        return[
            'title' => 'عنوان پست',
            'summary' => 'خلاصه پست',
            'content' => 'محتویات',
            'study_time' => 'زمان تقریبی مطالعه',
            'related_posts.*' => 'پست مرتبط',
            'related_posts' => 'پست های مرتبط',
            'published_at' => 'زمان انتشار پست',
            'category_id' => 'دسته بندی پست',
            'status' => 'وضعیت پست',
            'image' => 'تصویر پست',
            'tags.*' => 'برچسب پست',
            'tags' => 'برچسب های پست',
        ];
    }
}
