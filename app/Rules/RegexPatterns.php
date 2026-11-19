<?php

namespace App\Rules;

class RegexPatterns
{
    // فقط حروف، فاصله، نیم‌فاصله، دش و آپاستروف
    public const NAME = '/^[\p{L}\s\x{200c}\-\']+$/u';

    // متن عنوان بدون کاراکترهای شروع و پایان تگ HTML
    public const SAFE_TITLE = '/^[^<>]+$/u';

    // متن معمولی شامل حروف، اعداد، فاصله و علائم نگارشی
    public const PLAIN_TEXT = '/^[\p{L}\p{N}\s\p{P}\p{M}\x{200c}]+$/u';

    public const SYSTEM_NAME = '/^[a-zA-Z][a-zA-Z0-9_]*$/';

    // عنوان فارسی: حروف فارسی و عربی، اعداد فارسی و فاصله
    public const PERSIAN_TITLE = '/^[ا-یء-ي۰-۹ ]+$/u';

    // عنوان انگلیسی: حروف انگلیسی، اعداد و فاصله
    public const ENGLISH_TITLE = '/^[a-zA-Z0-9 ]+$/';

    // موبایل
    public const MOBILE = '/^(\+|00)?[0-9\-\s]{9,15}$/';

    // تلفن ثابت
    public const PHONE = '/^(\+|00)?[0-9\-\s()]{7,20}$/';

    // کد پستی
    public const POSTAL_CODE = '/^\d{4}\s?[A-Z]{2}$/i';

    // شماره ساختمان: حداقل یک رقم
    public const HOME_NUMBER = '/^(?=.*\d)[a-zA-Z0-9\s\-\/]+$/';


}