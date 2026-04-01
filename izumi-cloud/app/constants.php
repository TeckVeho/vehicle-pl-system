<?php

define('DEFAULT_ZERO_PAD', 5);
define('DEFAULT_STR_ZERO', '0');

define('CODE_SUCCESS', 200);
define('CODE_CREATE_FAILED', 201);
define('CODE_DELETE_FAILED', 202);
define('CODE_MULTI_STATUS', 207);
define('CODE_NO_ACCESS', 403);
define('CODE_NOT_FOUND', 404);
define('CODE_ERROR_SERVER', 500);
define('CODE_UNAUTHORIZED', 401);

define('IMAGE', 'upload/image');

define('TEMP_PASS', '123456789');
define('TEMP_DISK', 'temp_file');

define('BASE_URL_IZUMI', 'https://izumi.vw-dev.com');
define('BASE_URL_IZUMI_V2', 'https://izumi-v2.vw-dev.com');
define('BASE_URL_IZUMI_STAGE', 'https://stage.izumilogi.com');
define('BASE_URL_IZUMI_STAGE_V2', 'https://izumi-v2-stage.izumilogi.com');
define('BASE_URL_IZUMI_PRODUCTION', 'https://izumilogi.com');
define('BASE_URL_IZUMI_V2_PRODUCTION', 'https://izumi-v2.izumilogi.com');

define('BASE_URL_MAINTENANCE', 'https://izumi-maintenance.vw-dev.com');
define('BASE_URL_MAINTENANCE_STAGE', 'https://maint-stage.izumilogi.com');
define('BASE_URL_MAINTENANCE_PRODUCTION', 'https://maint.izumilogi.com');

define('BASE_URL_PAYSLIP', 'https://izumi-dx.vw-dev.com');
define('BASE_URL_PAYSLIP_STAGE', 'https://payslip-stage.izumilogi.com');
define('BASE_URL_PAYSLIP_PRODUCTION', 'https://payslip.izumilogi.com');

define('BASE_URL_WEB_APP', 'https://izumi-web-app.vw-dev.com');
define('BASE_URL_WEB_APP_STAGE', 'https://izumi-web-app-stage.izumilogi.com');
define('BASE_URL_WEB_APP_PRODUCTION', 'https://izumi-web-app.izumilogi.com');

define('BASE_URL_WEB_E_LEARNING', 'https://e-learning.vw-dev.com');
define('BASE_URL_WEB_E_LEARNING_STAGE', 'https://e-learning-stage.izumilogi.com');
define('BASE_URL_WEB_E_LEARNING_PRODUCTION', 'https://ie.izumilogi.com');

define('BASE_URL_PL', 'https://izumi-pl.vw-dev.com');
define('BASE_URL_PL_STAGE', 'https://pl-stage.izumilogi.com');
define('BASE_URL_PL_PRODUCTION', 'https://pl.izumilogi.com');

define('BASE_URL_WORKS', 'https://izumi-works.vw-dev.com');
define('BASE_URL_WORKS_STAGE', 'https://iw-stage.izumilogi.com');
define('BASE_URL_WORKS_PRODUCTION', 'https://iw.izumilogi.com');

define('BASE_URL_SMART_APPROVAL', 'https://izumi-smart-approval.vw-dev.com');
define('BASE_URL_SMART_APPROVAL_STAGE', 'https://sa-stage.izumilogi.com');
define('BASE_URL_SMART_APPROVAL_PRODUCTION', 'https://sa.izumilogi.com');

define('BASE_URL_WORK_SHIFT', 'https://izumi-ai-shift.vw-dev.com');
define('BASE_URL_WORK_SHIFT_STAGE', 'https://ws-stage.izumilogi.com');
define('BASE_URL_WORK_SHIFT_PRODUCTION', 'https://ws.izumilogi.com');

define('BASE_URL_ITP', 'https://itpv2.transtron.fujitsu.com');

define('LIST_BASE_URL_USER_SYNC', [
    BASE_URL_WEB_APP,
    BASE_URL_IZUMI,
    BASE_URL_IZUMI_V2,
    BASE_URL_PAYSLIP,
    BASE_URL_MAINTENANCE,
    BASE_URL_WEB_E_LEARNING,
    BASE_URL_WORKS,
    BASE_URL_SMART_APPROVAL,
    BASE_URL_PL,
    BASE_URL_WORK_SHIFT,
]);
define('LIST_BASE_URL_USER_SYNC_STAGE', [
    BASE_URL_WEB_APP_STAGE,
    BASE_URL_IZUMI_STAGE,
    BASE_URL_IZUMI_STAGE_V2,
    BASE_URL_PAYSLIP_STAGE,
    BASE_URL_MAINTENANCE_STAGE,
    BASE_URL_WEB_E_LEARNING_STAGE,
    BASE_URL_PL_STAGE,
    BASE_URL_WORKS_STAGE,
    BASE_URL_SMART_APPROVAL_STAGE,
    BASE_URL_WORK_SHIFT_STAGE,
]);
define('LIST_BASE_URL_USER_SYNC_PRODUCTION', [
    BASE_URL_WEB_APP_PRODUCTION,
    BASE_URL_IZUMI_PRODUCTION,
    BASE_URL_IZUMI_V2_PRODUCTION,
    BASE_URL_PAYSLIP_PRODUCTION,
    BASE_URL_MAINTENANCE_PRODUCTION,
    BASE_URL_WEB_E_LEARNING_PRODUCTION,
    BASE_URL_PL_PRODUCTION,
    BASE_URL_WORKS_PRODUCTION,
    BASE_URL_SMART_APPROVAL_PRODUCTION,
    BASE_URL_WORK_SHIFT_PRODUCTION,
]);

define('LIST_BASE_URL_EMPLOYEE_SYNC', [
    BASE_URL_SMART_APPROVAL,
    BASE_URL_WORK_SHIFT,
]);
define('LIST_BASE_URL_EMPLOYEE_SYNC_STAGE', [
    BASE_URL_SMART_APPROVAL_STAGE,
    BASE_URL_WORK_SHIFT_STAGE,
]);
define('LIST_BASE_URL_EMPLOYEE_SYNC_PRODUCTION', [
    BASE_URL_SMART_APPROVAL_PRODUCTION,
    BASE_URL_WORK_SHIFT_PRODUCTION,
]);

define('PATH_UPLOAD_DATA_ITEM', 'data_item');
define('URL_API_SEND_TO_IZUMI', 'https://izumi.vw-dev.com/api/cloud/sync');
define('URL_API_SEND_TO_IZUMI_V2', 'https://izumi-v2.vw-dev.com/api/cloud/sync');
define('URL_API_SEND_TO_IZUMI_STAGING', 'https://stage.izumilogi.com/api/cloud/sync');
define('URL_API_SEND_TO_IZUMI_STAGING_V2', 'https://izumi-v2-stage.izumilogi.com/api/cloud/sync');
define('URL_API_SEND_TO_IZUMI_PRODUCTION', 'https://izumilogi.com/api/cloud/sync');
define('URL_API_SEND_TO_IZUMI_V2_PRODUCTION', 'https://izumi-v2.izumilogi.com/api/cloud/sync');

define('PATH_ZIP_FILE', 'data_item_zip');
define('API_SEND_TO_MAINTENANCE', 'https://izumi-maintenance.vw-dev.com/api/receive-vehicles-data');
define('API_SEND_TO_MAINTENANCE_STAGING', 'https://maint-stage.izumilogi.com/api/receive-vehicles-data');
define('API_SEND_TO_MAINTENANCE_PRODUCTION', 'https://maint.izumilogi.com/api/receive-vehicles-data');

define('API_GET_MAINTENANCE', 'https://izumi-maintenance.vw-dev.com/api/maintenance-cost/export/');
define('API_GET_MAINTENANCE_STAGING', 'https://maint-stage.izumilogi.com/api/maintenance-cost/export/');
define('API_GET_MAINTENANCE_PRODUCTION', 'https://maint.izumilogi.com/api/maintenance-cost/export/');

define('API_SEND_TO_PAYSLIP', 'https://izumi-dx.vw-dev.com/api/receive-shain-data');
define('API_SEND_TO_PAYSLIP_STAGING', 'https://payslip-stage.izumilogi.com/api/receive-shain-data');
define('API_SEND_TO_PAYSLIP_PRODUCTION', 'https://payslip.izumilogi.com/api/receive-shain-data');

define('API_SEND_COURSE_DATA_TO_TIMESHEET_DEV', 'https://izumi.vw-dev.com/api/cloud/sync/route');
define('API_SEND_COURSE_DATA_TO_TIMESHEET_STAGING', 'https://stage.izumilogi.com/api/cloud/sync/route');
define('API_SEND_COURSE_DATA_TO_TIMESHEET_PRODUCTION', 'https://izumilogi.com/api/cloud/sync/route');

define('API_PAYSLIP_PAYMENT', 'https://izumi-dx.vw-dev.com/api/receive-payment-data');
define('API_PAYSLIP_PAYMENT_STAGING', 'https://payslip-stage.izumilogi.com/api/receive-payment-data');
define('API_PAYSLIP_PAYMENT_PRODUCTION', 'https://payslip.izumilogi.com/api/receive-payment-data');

define('API_EXECUTE_CALCULATE_MAINT', '/api/re-caculate-schedule');

define('API_AI_OCR_IMAGE', 'https://daiseilog.dx-suite.com/ConsoleWeb/api/v1/reading/pages/images');
define('API_AI_OCR_PARTS', 'https://daiseilog.dx-suite.com/ConsoleWeb/api/v1/reading/parts');
define('API_CERT_KEY_AI_OCR', '1d4a2a6cdfedb01fc5289f3a17a51307fc96f56f42083a066d179b125e42e61593127e12b758be19d403058fab2afb7bc6408aa27723dcdb7e1ec7f1f229f04f');
define('CLOUD_DATA_TIMESHEET_DEV', 'https://izumi.vw-dev.com/api/cloud/data/timesheet');
define('CLOUD_DATA_TIMESHEET_STAGING', 'https://stage.izumilogi.com/api/cloud/data/timesheet');
define('CLOUD_DATA_TIMESHEET_PRODUCTION', 'https://izumilogi.com/api/cloud/data/timesheet');

define('TIMESHEET_API_SYNC_KING_TIME', '/api/cloud/sync-king-time');
define(
    'JAPAN_YEAR',
    [
        [
            'range_start' => '1926-12-25',
            'range_end' => '1989-01-07',
            'start_year' => 1926,
            'label_jp' => '昭和',
            'label_eng' => 'showa',
        ],
        [
            'range_start' => '1989-01-08',
            'range_end' => '2019-04-30',
            'start_year' => 1989,
            'label_jp' => '平成',
            'label_eng' => 'heisei',
        ],
        [
            'range_start' => '2019-05-01',
            'range_end' => null,
            'start_year' => 2019,
            'label_jp' => '令和',
            'label_eng' => 'reiwa',
        ],
    ]
);

define('ROLE_CREW', 'crew'); // CREW
define('ROLE_CLERKS', 'clerks'); // 事務員
define('ROLE_TL', 'tl'); // TL
define('ROLE_GENERAL_AFFAIR', 'general_affair'); // 総務広報
define('ROLE_ACCOUNTING', 'accounting'); // 経理財務
define('ROLE_PERSONNEL_LABOR', 'personnel_labor'); // 人事労務
define('ROLE_AM_SM', 'am_sm');
define('ROLE_DIRECTOR', 'director'); // 取締役
define('ROLE_DX_USER', 'dx_user'); // DX
define('ROLE_DX_MANAGER', 'dx_manager'); // 管理者

define('ROLE_QUALITY_CONTROL', 'quality_control'); // 品質管理
define('ROLE_SALES', 'sales'); // 品質管理
define('ROLE_SITE_MANAGER', 'site_manager'); // 現場MG
define('ROLE_HQ_MANAGER', 'hq_manager'); // 本社MG
define('ROLE_DEPARTMENT_MANAGER', 'department_manager'); // 部長
define('ROLE_EXECUTIVE_OFFICER', 'executive_officer'); // 執行役員
define('ROLE_DEPARTMENT_OFFICE_STAFF', 'department_office_staff'); // 事業部事務員

define('COURSE_TYPE', [
    1 => 'cvs',
    2 => 'mass_sales',
    3 => 'co_delivery',
    4 => 'sideways',
    5 => 'kurabin',
    6 => 'general',
    7 => 'trunk_line',
    8 => 'store_delivery',
    9 => 'other',
]);

define('COURSE_TYPE_VALUE', [
    'cvs' => 1,
    'mass_sales' => 2,
    'co_delivery' => 3,
    'sideways' => 4,
    'kurabin' => 5,
    'general' => 6,
    'trunk_line' => 7,
    'store_delivery' => 8,
    'other' => 9,
]);

define('DELIVERY_TYPE', [
    0 => 'dry',
    1 => 'chilled',
    2 => 'frozen',
]);

define('DELIVERY_TYPE_VALUE', [
    'dry' => 0,
    'chilled' => 1,
    'frozen' => 2,
]);

define('GATE', [
    0 => 'existing',
    1 => 'none',
]);

define('GATE_VALUE', [
    'existing' => 0,
    'none' => 1,
]);

define('WING', [
    'existing' => 0,
    'none' => 1,
]);

define('WING_VALUE', [
    'existing' => 0,
    'none' => 1,
]);

define('TONNAGE', [
    2, 3, 4, 6, 10,
]);

define('IS_WEEK_NON_DELIVERY', [
    'yes' => 1,
    'no' => 0,
]);

define('DAY_IN_WEEKEND', [
    1 => 'Mon',
    2 => 'Tue',
    3 => 'Wed',
    4 => 'Thu',
    5 => 'Fri',
    6 => 'Sat',
    7 => 'Sun',
]);

define('DAY_IN_WEEKEND_VALUE', [
    'Mon' => 1,
    'Tue' => 2,
    'Wed' => 3,
    'Thu' => 4,
    'Fri' => 5,
    'Sat' => 6,
    'Sun' => 7,
]);

define('QUERY_COURSE_MUST_SELECT', [
    'courses.id',
    'courses.course_code',
    'courses.start_date',
    'courses.end_date',
    'courses.course_type',
    'courses.delivery_type',
    'courses.start_time',
    'courses.gate',
    'courses.wing',
    'courses.tonnage',
    'courses.quantity',
    'courses.allowance',
    'courses.department_id',
    'courses.bin_type',
    'courses.deleted_at',
    'courses.course_flag',
]);

define('QUERY_ROUTE_MUST_SELECT', [
    'routes.id',
    'routes.name',
    'routes.department_id',
    'routes.customer_id',
    'routes.route_fare_type',
    'routes.fare',
    'routes.highway_fee',
    'routes.highway_fee_holiday',
    'routes.is_government_holiday',
]);

define('QUERY_DRIVER_RECORD_INDEX', [
    'driver_recorders.id',
    'departments.id as department_id',
    'departments.name as department_name',
    'title',
    'type',
    'excel_file_id',
    'record_date',
    'remark',
    'type_one',
    'type_two',
    'shipper',
    'accident_classification',
    'place_of_occurrence',
    'driver_recorders.created_at',
    'driver_recorders.updated_at',
]);

define('IS_GORVERNMENT_HOLIDAY', [
    'yes' => 1,
    'no' => 0,
]);

define('ROUTE_FARE_TYPE', [
    'daily' => 1,
    'monthly' => 2,
]);

define('BIN_TYPE_VALUE', [
    'one_day' => 1,
    'first_half' => 2,
    'second' => 3,
]);
define('ROUTE_FARE_TYPE_NAME', [
    '日額' => 1,
    '月額' => 2,
]);

define('LIST_ROLE', [
    'crew',
    'clerks',
    'tl',
    'accounting',
    'general_affair',
    'personnel_labor',
    'am_sm',
    'director',
    'dx_user',
    'dx_manager',
]);

define('REGULAR_FLAG', [
    'regular' => 0,
    'irregular' => 1,
]);

define('VEHICLE_LIST_SELECT', [
    'department_id',
    'vehicle_identification_number',
    'inspection_expiration_date',
    'scrap_date',
]);

define('PARKING_PLACE_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('DAISHA', [
    'not_specified' => 1,
    'normal' => 2,
    'gomu' => 3,
    'specified' => 4,
    'handheld' => 5,
]);

define('KEY_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('INSIDE_RULE_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('ELEVATOR_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('WAITING_PLACE_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('DELIVERY_SLIP_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('VEHICLE_HEIGHT_WIDTH', [
    'yes' => 1,
    'no' => 0,
]);

define('SECURITY_VALUE', [
    'yes' => 1,
    'no' => 0,
]);

define('STORE_FILE_ATTATCH_DISK', 'stores_files');

define('STORE_IMAGE_TYPE', [
    'delivery_route_map' => 'delivery_route_map',
    'parking_position_1' => 'parking_position_1',
    'parking_position_2' => 'parking_position_2',
]);

define('DRIVER_RECORDER_TYPE', [
    'accident' => 0,
    'other' => 1,
]);

define('DRIVER_PATH_UPLOAD_FILE', 'driver_upload');
define('DRIVER_PATH_UPLOAD_FILE_S3', 'driver_upload_s3');
define('DRIVER_MOVE_TYPE', ['front', 'inside', 'behind']);

define('MOVIE_PATH_UPLOAD_FILE', 'movie_upload');
define('MOVIE_PATH_UPLOAD_FILE_S3', 'movie_upload_s3');
define('EMPLOYEE_PATH_UPLOAD_FILE_S3', 'employee_upload_s3');

define('LW_AUTH_URL', 'https://auth.worksmobile.com/oauth2/v2.0/authorize');
define('LW_AUTH_TOKEN_URL', 'https://auth.worksmobile.com/oauth2/v2.0/token');
define('LW_API_SEND_MSG', 'https://www.worksapis.com/v1.0/bots/?/channels/?/messages');
define('LW_API_GET_MESSAGES', 'https://www.worksapis.com/v1.0/bots/?/channels/?/messages');
define('LW_API_GET_ALL_MEMBER_IN_CHANNEL', 'https://www.worksapis.com/v1.0/bots/?/channels/?/members?count=100');
define('LW_API_GET_USER_INFO', 'https://www.worksapis.com/v1.0/users/');
define('LW_API_GET_LIST_BOT', 'https://www.worksapis.com/v1.0/bots');

define('TYPE_ONE', [
    '事故' => 1,
    'GP' => 2,
    'その他' => 3,
]);
define('TYPE_TOW', [
    '有責' => 1,
    '無責' => 2,
    'その他' => 3,
]);
define('SHIPPER', [
    'CVS' => 1,
    'ヤマ物' => 2,
    'サンロジ' => 3,
    '富士エコー' => 4,
    'パスコ' => 5,
    'ロジネット' => 6,
    'FR' => 7,
    'その他' => 8,
]);
define('ACCIDENT_CLASSIFICATION', [
    '接触(物)' => 1,
    '接触(車)' => 2,
    '接触(人)' => 3,
    '追突' => 4,
    'バック' => 5,
    '自損横転' => 6,
    'オーバーハング' => 7,
    '巻込み' => 8,
    '衝突' => 9,
    '不明・その他' => 10,
]);
define('PLACE_OF_OCCURRENCE', [
    '店舗敷地' => 1,
    '構内' => 2,
    '一般道路' => 3,
    '交差点' => 4,
    '高速道路' => 5,
    '納品口' => 6,
    '駐車場' => 7,
    '不明・その他' => 8,
]);

define('KINGTIME_API_DAILY_WORKINGS_TIMERECORD', 'https://api.kingtime.jp/v1.0/daily-workings/timerecord');

define('EMPLOYEE_ROLE', [
    '部長' => 1,
    '本部長' => 2,
    '常務' => 3,
    '社長' => 4,
]);

define('ITV2_KEEPER_URL_API', 'https://ittkv2.ittenko-keeper.com/se');

define('IZUMI_DEFACE_DEV', 'https://izumi-deface.vw-dev.com/');
// define('IZUMI_DEFACE_STAGE', 'https://izumi-deface.vw-dev.com/');
// define('IZUMI_DEFACE_PRODUCT', 'https://izumi-deface.vw-dev.com/');
define('VEHICLE', [
    'vehicle_identification_number' => '車体番号',
    'door_number' => 'ドアナンバー',
    'no_number_plate' => '車両No',
    'department_name' => '拠点',
    'driving_classification' => '運行区分',
    'tonnage' => 't数',
    'truck_classification' => 't車',
    'voluntary_premium' => '任意保険料',
    'license_classification' => '免許区分',
    'manufactor' => 'メーカー',
    'first_registration' => '初年度登録',
    'inspection_expiration_date' => '車検満了日',
    'vehicle_delivery_date' => '車両納入日',
    'box_distinction' => '箱区分',
    'owner' => '所有者',
    'etc_certification_number' => 'ETCセットアップﾟ証明番号',
    'etc_number' => 'ETC番号',
    'fuel_card_number_1' => '燃料カード番号➀',
    'fuel_card_number_2' => '燃料カード番号➁',
    'box_shape' => '箱形状',
    'mount' => '架装',
    'refrigerator' => '冷凍機',
    'eva_type' => 'エバ種類',
    'gate' => 'ゲート',
    'humidifier' => '加湿器',
    'type' => '型式',
    'motor' => '原動機',
    'displacement' => '排気量',
    'length' => '長さ',
    'width' => '幅',
    'height' => '高さ',
    'maximum_loading_capacity' => '最大積載量',
    'vehicle_total_weight' => '車両総重量',
    'in_box_length' => '箱内寸長',
    'in_box_width' => '箱内寸幅',
    'in_box_height' => '箱内寸高',
    'scrap_date' => '廃車日',
    'early_registration' => '期中登録',
    'd1d_not_installed' => 'D1D非搭載',
    'optional_detail' => '任意明細',
    'liability_insurance_period' => '自賠責期限',
    'insurance_company' => '自賠会社',
    'agent' => '自賠扱者',
    'mileage' => '走行距離',
    'monthly_mileage' => '月間走行距離',
    'tire_size' => 'タイヤサイズ',
    'battery_size' => 'バッテリーサイズ',
    'start_of_leasing' => '契約開始日',
    'end_of_leasing' => '契約満了日',
    'leasing_period' => '契約期間',
    'leasing_company' => 'メンテナンスリース会社',
    'maintenance_lease_fee' => 'メンテナンスリース料',
    'garage' => '整備工場',
    'tel' => '電話番号',
    'vehicle_pdf_history' => '車検証',
    'remark_old_car_1' => '備考',
    'detail' => '詳細',
    'delete' => '削除',
]);

define('VEHICLE_DEFAULT', [
    't数',
    't車',
    '任意保険料',
    'メーカー',
    '初年度登録',
    '車検満了日',
    '箱区分',
    'ETCセットアップﾟ証明番号',
    'ETC番号',
    '燃料カード番号➀',
    '燃料カード番号➁',
    '架装',
    '冷凍機',
    'ゲート',
    '長さ',
    '幅',
    '高さ',
    '最大積載量',
    '車両総重量',
    '自賠責期限',
    '契約満了日',
    'メンテナンスリース会社',
    'メンテナンスリース料',
]);

define('HASACCESS_DELETE', [
    'clerks',
    'tl',
    'department_office_staff',
    'accounting',
    'general_affair',
//    'personnel_labor',
    'am_sm',
    'director',
    'dx_user',
    'dx_manager',
    'quality_control',
    'site_manager',
    'hq_manager',
    'department_manager',
    'executive_officer',
]);

define('EMPLOYEE_UPLOAD_FILE', 'employee_upload');
define('NEWS_LETTER_PATH_UPLOAD_FILE', 'news_letter_upload');
define('NEWS_LETTER_PATH_UPLOAD_FILE_S3', 'news_letter_upload_s3');
