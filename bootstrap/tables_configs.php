<?php
const MODULE_CONFIGS_FIELDS = [

    'catalog' => [
        'show_in_menu',
        'item_big_img_max_width',
        'item_big_img_max_height',
        'item_small_img_max_width',
        'item_small_img_max_height',

        'item_big_gallery_img_max_width',
        'item_big_gallery_img_max_height',
        'item_small_gallery_img_max_width',
        'item_small_gallery_img_max_height',

        'category_big_list_img_max_width',
        'category_big_list_img_max_height',
        'category_small_list_img_max_width',
        'category_small_list_img_max_height',

        'category_big_list_addit_img_max_width',
        'category_big_list_addit_img_max_height',
        'category_small_list_addit_img_max_width',
        'category_small_list_addit_img_max_height',
    ],

    'clients' => [
        'available_clients_types',
    ],

    'content' => [
        'records_in_page',
    ],

    'troubleshooting_mails' => [
        'mail_smtp',
        'mail_smtp_host',
        'mail_smtp_port',
        'mail_smtp_user',
        'mail_smtp_password',
        'mail_smtp_secure',
        'mail_imap_host',
        'mail_imap_port',
    ],

    'sitemap' => [
        'site_domain',
        'sitemap_folder',
    ],

];

// Действия требующие доступа
const USERS_PERMISSION_ACTIONS = [
    'site_modules' => [
        'delete_module' => [
            'title_code' => 'Can delete module',
            'description' => '',
        ],
        'add_module' => [
            'title_code' => 'Can add module',
            'description' => '',
        ],
    ],
    'userz' => [
        'change_permission_actions' => [
            'title_code' => 'Can change permission actions',
            'description' => '',
        ],
    ],
    'database' => [
        'module_access' => [
            'title_code' => 'Has access to the module',
            'description' => '',
        ],
        'change_database_prefix' => [
            'title_code' => 'Can change database prefix',
            'description' => '',
        ]
    ]
];

// Получаем все конфигурации таблиц
$tables_configs = [];
$configs = \App\Models\TableConfig::all();
foreach ($configs as $row) {
    $tables_configs[$row->table] = json_decode($row->configs, true);
}

// Настройки каталога
$tmp_configs = (!empty($tables_configs['catalog'])) ? $tables_configs['catalog'] : [];
define('CATALOG_CONFIGS', $tmp_configs);

// Настройки клиентов
$tmp_configs = (!empty($tables_configs['clients'])) ? $tables_configs['clients'] : [];
define('CLIENTS_CONFIGS', $tmp_configs);
$available_clients_types = [];
if (!empty(CLIENTS_CONFIGS['available_clients_types'])) {
    $available_clients_types = array_keys(CLIENTS_CONFIGS['available_clients_types']);
}
define('AVAILABLE_CLIENTS_TYPES', $available_clients_types);

// Настройки текстовых записей
$tmp_configs = (!empty($tables_configs['content'])) ? $tables_configs['content'] : [];
define('CONTENT_CONFIGS', $tmp_configs);

// Настройки SMTP
$tmp_configs = (!empty($tables_configs['troubleshooting_mails'])) ? $tables_configs['troubleshooting_mails'] : [];
define('SMTP_CONFIGS', $tmp_configs);

// Настройки Sitemap
$tmp_configs = (!empty($tables_configs['sitemap'])) ? $tables_configs['sitemap'] : [];
define('SITEMAP_CONFIGS', $tmp_configs);
