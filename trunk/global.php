<?php

define( 'CHAT_ESSENTIAL_VERSION', '0.26' );

define( 'CHAT_ESSENTIAL_POST_TYPE', 'ce_hosted' );
define( 'CHAT_ESSENTIAL_OPTION', 'chat-essential' );

define( 'CHAT_ESSENTIAL_ENV', 'prod' );

if (CHAT_ESSENTIAL_ENV == 'dev') {
    define( 'CHAT_ESSENTIAL_API_URL', 'https://devapi.eyelevel.ai' );
    define( 'CHAT_ESSENTIAL_DASHBOARD_URL', 'https://devssp.eyelevel.ai');
} else {
    define( 'CHAT_ESSENTIAL_API_URL', 'https://api.eyelevel.ai' );
    define( 'CHAT_ESSENTIAL_DASHBOARD_URL', 'https://chatessential.eyelevel.ai');
}

define( 'CHAT_ESSENTIAL_ALERT_URL', 'https://api.eyelevel.ai' );
define( 'CHAT_ESSENTIAL_UPLOAD_BASE_URL', 'https://upload.eyelevel.ai' );

define( 'CHAT_ESSENTIAL_MIN_TRAINING_CONTENT', 1000 );
define( 'CHAT_ESSENTIAL_MIN_TRAINING_PAGE_CONTENT', 100 );

global $chat_essential_db_version;
$chat_essential_db_version = '0.2';

$engines = array();
$engines[] = array(
	'name' => 'GPT-3',
	'engine' => 'gpt3',
	'kitId' => 1,
);
define( 'CHAT_ESSENTIAL_CORE_ENGINES', $engines );