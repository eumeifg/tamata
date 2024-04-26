<?php
return [
    'system' => [
        'default' => [
            'catalog' => [
                'search' => [
                    'engine' => 'elasticsearch6',
                    'elasticsearch6_server_hostname' => getenv('ELASTICSEARCH_HOST'),
                    'elasticsearch6_server_port' => '9200',
                    'elasticsearch6_index_prefix' => 'magento2',
                ],
            ],
            'dev' => [
                'js' => [
                    'enable_js_bundling' => '1',
                    'merge_files' => '1',
                    'minify_files' => '1',
                ],
                'css' => [
                    'merge_css_files' => '1',
                    'minify_files' => '1',
                ],
            ],
        ],
    ],
    'backend' => [
        'frontName' => 'admin',
    ],
    'seller' => [
        'frontName' => 'seller',
    ],
    'crypt' => [
        'key' => 'af6ed091a3b842794cf09d0ac4aa9f50',
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => getenv('DB_HOST'),
                'dbname' => getenv('DB_NAME'),
                'username' => getenv('DB_USER'),
                'password' => getenv('DB_PASS'),
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [

                ],
            ],
        ],
        'slave_connection' => [
            'default' => [
                'host' => getenv('DB_HOST_RO'),
                'dbname' => getenv('DB_NAME'),
                'username' => getenv('DB_USER'),
                'password' => getenv('DB_PASS'),
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1'
            ],
        ],
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default',
        ],
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => getenv('MAGE_MODE'),
    'session' => array(
        'save' => 'redis',
        'redis' => array(
            'password' => '',
            'timeout' => '15',
            'persistent_identifier' => '',
            'database' => '2',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '4',
            'max_concurrency' => '8',
            'break_after_frontend' => '30',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '1',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000',
            'sentinel_master' => getenv('SENTINEL_MASTER'),
            'sentinel_servers' => getenv('SENTINEL_SERVERS'),
            'sentinel_verify_master' => getenv('SENTINEL_VERIFY_MASTER'),
            'sentinel_connect_retries' => getenv('SENTINEL_CONNECT_RETRIES'),
        ),
    ),

    'cache' => [
        'frontend' => [
            'default' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => getenv('SENTINEL'),
                    'database' => '0',
                    'timeout' => '15',
                    'sentinel_master' => getenv('SENTINEL_MASTER'),
                    'sentinel_servers' => getenv('SENTINEL_SERVERS'),
                    'sentinel_verify_master' => getenv('SENTINEL_VERIFY_MASTER'),
                    'sentinel_connect_retries' => getenv('SENTINEL_CONNECT_RETRIES'),
                ],
                'id_prefix' => '8c0_',
            ],
            'page_cache' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => getenv('SENTINEL'),
                    'database' => '1',
                    'timeout' => '15',
                    'compress_data' => '0',
                    'password' => '',
                    'compression_lib' => '',
                    'sentinel_master' => getenv('SENTINEL_MASTER'),
                    'sentinel_servers' => getenv('SENTINEL_SERVERS'),
                    'sentinel_verify_master' => getenv('SENTINEL_VERIFY_MASTER'),
                    'sentinel_connect_retries' => getenv('SENTINEL_CONNECT_RETRIES'),
                ],
                'id_prefix' => '8c0_',
            ],
        ],
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => null,
        ],
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'google_product' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1,
        'target_rule' => 1,
        'amasty_shopby' => 1,
    ],
    'downloadable_domains' => [
        '127.0.0.1',
    ],
    'install' => [
        'date' => 'Mon, 15 Feb 2021 19:33:15 +0000',
    ],
    'queue' => [
        'consumers_wait_for_messages' => 0,
    ],

    'cron_consumers_runner' => [
        'cron_run' => true,
        'max_messages' => 1000,
        'consumers' => [],
    ],
];
