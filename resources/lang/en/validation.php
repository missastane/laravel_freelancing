<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted" => "The :attribute field must be accepted.",
    "active_url" => "The :attribute is not a valid URL.",
    "after" => "The :attribute must be a date after :date.",
    "alpha" => "The :attribute must only contain letters.",
    "alpha_dash" => "The :attribute must only contain letters, numbers, dashes and underscores.",
    "alpha_num" => "The :attribute must only contain letters and numbers.",
    "array" => "The :attribute must be an array.",
    "before" => "The :attribute must be a date before :date.",
    "between" => [
        "numeric" => "The :attribute must be between :min and :max.",
        "file" => "The :attribute must be between :min and :max kilobytes.",
        "string" => "The :attribute must be between :min and :max characters.",
        "array" => "The :attribute must have between :min and :max items.",
    ],
    "boolean" => "The :attribute field must be true or false.",
    "confirmed" => "The :attribute confirmation does not match.",
    "date" => "The :attribute is not a valid date.",
    "date_format" => "The :attribute does not match the format :format.",
    "different" => "The :attribute and :other must be different.",
    "digits" => "The :attribute must be :digits digits.",
    "digits_between" => "The :attribute must be between :min and :max digits.",
    'dimensions' => 'The :attribute has invalid image dimensions.',
    "email" => "The :attribute must be a valid email address.",
    "exists" => "The selected :attribute is invalid.",
    "filled" => "The :attribute field is required.",
    "image" => "The :attribute must be an image.",
    "in" => "The selected :attribute is invalid.",
    "integer" => "The :attribute must be an integer.",
    "ip" => "The :attribute must be a valid IP address.",
    "max" => [
        "numeric" => "The :attribute must not be greater than :max.",
        "file" => "The :attribute must not be greater than :max kilobytes.",
        "string" => "The :attribute must not be greater than :max characters.",
        "array" => "The :attribute must not have more than :max items.",
    ],
    "mimes" => "The :attribute must be a file of type: :values.",
    "min" => [
        "numeric" => "The :attribute must be at least :min.",
        "file" => "The :attribute must be at least :min kilobytes.",
        "string" => "The :attribute must be at least :min characters.",
        "array" => "The :attribute must have at least :min items.",
    ],
    "not_in" => "The selected :attribute is invalid.",
    "numeric" => "The :attribute must be a number.",
    "regex" => "The :attribute format is invalid.",
    "required" => "The :attribute field is required.",
    "required_if" => "The :attribute field is required when :other is :value.",
    "required_with" => "The :attribute field is required when :values is present.",
    "required_with_all" => "The :attribute field is required when :values are present.",
    "required_without" => "The :attribute field is required when :values is not present.",
    "required_without_all" => "The :attribute field is required when none of :values are present.",
    "same" => "The :attribute and :other must match.",
    "size" => [
        "numeric" => "The :attribute must be :size.",
        "file" => "The :attribute must be :size kilobytes.",
        "string" => "The :attribute must be :size characters.",
        "array" => "The :attribute must contain :size items.",
    ],
    "string" => "The :attribute must be a string.",
    "timezone" => "The :attribute must be a valid zone.",
    "unique" => "The :attribute has already been taken.",
    'uploaded' => 'The :attribute failed to upload.',
    "url" => "The :attribute format is invalid.",
    'recaptcha' => 'The :attribute verification failed!',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => array(
        'adult_id' => array(
            'required' => 'Please choose some parents!',
        ),
        'group_id' => array(
            'required' => 'Please choose a group or choose temp!',
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => [
        "name" => "name",
        'firstName' => 'first name',
        "username" => "username",
        "email" => "email address",
        "first_name" => "first name",
        "last_name" => "last name",
        "lastName" => "last name",
        "family" => "last name",
        "password" => "password",
        "password_confirmation" => "password confirmation",
        "discription" => "description",
        "city" => "city",
        "country" => "country",
        "address" => "address",
        "phone" => "phone",
        "national_code" => "national code",
        "mobile" => "mobile",
        "cellphone" => "cellphone",
        "age" => "age",
        "sex" => "gender",
        "gender" => "gender",
        "day" => "day",
        "month" => "month",
        "year" => "year",
        "hour" => "hour",
        "minute" => "minute",
        "second" => "second",
        "title" => "title",
        "text" => "text",
        "content" => "content",
        "description" => "description",
        "excerpt" => "excerpt",
        "date" => "date",
        "time" => "time",
        "available" => "available",
        "size" => "size",
        "file" => "file",
        "fullname" => "full name",
        "postal_code" => "postal code",
        "comment" => "comment",
        "body" => "body",
        "image" => "image",
        "photos" => "photos",
        "photo" => "photo",
        "cat_id" => "category",
        "author_id" => "author",
        "published_at" => "published date",
        "reference_id" => "reference",
        "priority_id" => "priority",
        "post_category_id" => "category",
        "tags" => "tags",
        "status" => "status",
        "summary" => "summary",
        "commentable" => "commentable",
        "logo" => "logo",
        "icon" => "icon",
        "answer" => "answer",
        "question" => "question",
        "parent_id" => "parent field",
        "url" => "URL",
        "keywords" => "keywords",
        "permission_id" => "permission",
        "amount" => "amount",
        "delivery_time_unit" => "delivery time unit",
        "delivery_time" => "delivery time",
        "persian_name" => "persian name",
        "original_name" => "original name",
        "brand_id" => "brand",
        "category_id" => "category",
        "weight" => "weight",
        "height" => "height",
        "width" => "width",
        "length" => "length",
        "price" => "price",
        "meta_key" => "meta key",
        "meta_value" => "meta value",
        "introduction" => "introduction",
        "marketable" => "marketable",
        "unit" => "unit",
        "value" => "value",
        "product_id" => "product",
        "price_increase" => "price increase",
        "type" => "type",
        "receiver" => "receiver",
        "deliverer" => "deliverer",
        "marketable_number" => "marketable quantity",
        "frozen_number" => "frozen quantity",
        "sold_number" => "sold quantity",
        "color" => "color",
        "copan_id" => "discount code",
        "max_price" => "maximum price",
        "min_price" => "minimum price",
        "g-recaptcha-response" => 'reCAPTCHA verification',
        "postal_tracking_code" => 'postal tracking code',
        "guarantee" => 'guarantee',
    ],
];
