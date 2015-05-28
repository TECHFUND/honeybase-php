<?php

$data = App\Util\Util::getJSON(__DIR__.'/config.json');

/* general */
define("SERVICE_NAME", $data['service']['name']);
define("ORIGIN", $data['origin']['development']);

/* database */
define("DB_HOST", $data['database']['host']);
Log::info('------------------');
Log::info($data);
Log::info('------------------');
define("DB_USERNAME", $data['database']['username']);
define("DB_PASSWORD", $data['database']['password']);
define("DB_NAME", $data['database']['name']);
define("LOG_PATH", __DIR__.$data['database']['log_path']);

/* oauth */
define("FACEBOOK_CONSUMER_KEY", $data['oauth']['facebook']['consumer_key']);
define("FACEBOOK_CONSUMER_SECRET", $data['oauth']['facebook']['consumer_secret']);
