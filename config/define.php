<?php

$data = App\Util\Util::getJSON(__DIR__.'/config.json');

/* path */
define("__CONFIG__", __DIR__.'/');

/* general */
define("SERVICE_NAME", $data->service->name);
define("ORIGIN", $data->origin->development);

/* database */
define("DB_HOST", $data->database->host);
define("DB_USERNAME", $data->database->username);
define("DB_PASSWORD", $data->database->password);
define("DB_NAME", $data->database->name);
define("LOG_PATH", __DIR__.$data->database->log_path);

/* oauth */
define("FACEBOOK_CONSUMER_KEY", $data->oauth->facebook->consumer_key);
define("FACEBOOK_CONSUMER_SECRET", $data->oauth->facebook->consumer_secret);
