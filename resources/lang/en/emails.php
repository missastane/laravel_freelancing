
<?php

return [

    'security' => [

        'two_factor_login_attempts_exceeded' => [
            'subject' => 'Security Alert: Multiple Failed Login Attempts',
            'preheader' => 'Multiple failed attempts to verify your two-factor authentication have been detected.',
            'title' => 'Security Alert',
            'greeting' => 'Hello :name,',
            'body' => 'Multiple failed attempts to complete two-factor authentication for your account have been detected. If these attempts were not made by you, someone else may be trying to access your account.',
            'details_title' => 'Failed Attempt Details',
            'attempted_at' => 'Attempt time',
            'ip_address' => 'IP address',
            'device' => 'Browser and device',
            'client_type' => 'Access type',
            'client_web' => 'Web',
            'client_mobile' => 'Mobile',
            'warning_title' => 'Was this activity not yours?',
            'warning_body' => 'If you did not make these attempts, we recommend changing your account password as soon as possible and making sure the email account associated with your profile is secure.',
            'closing' => 'If these attempts were made by you, no further action is required.',
            'footer' => 'This email was sent to notify you of a security event. Please do not reply to this email.',
        ],

    ],

];
