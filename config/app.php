<?php

return [
    'name'       => 'Vexogen',
    'tagline'    => 'Agency ERP',
    'url'        => env('APP_URL', ''),
    'timezone'   => 'Asia/Kolkata',
    'currency'   => 'INR',
    'currency_symbol' => '₹',
    'gst_rate'   => 18,
    'session_name' => 'vexogen_session',
    'upload_max_mb' => 50,
    'allowed_extensions' => ['ai','psd','cdr','pdf','png','jpg','jpeg','zip','docx','mp4','webp','svg'],
    'roles' => ['admin','manager','designer','developer','marketing','accounts','client'],
    'project_statuses' => [
        'lead','discussion','quotation_sent','advance_received','planning',
        'design','development','review','revision','final_approval',
        'completed','delivered','closed'
    ],
    'expense_categories' => [
        'office_rent','salary','internet','fuel','software','printing',
        'equipment','photography','miscellaneous'
    ],
    'service_categories' => [
        'branding','graphic_design','packaging','website','mobile_app',
        'digital_marketing','seo','social_media','photography','video',
        'printing','ui_ux'
    ],
];
