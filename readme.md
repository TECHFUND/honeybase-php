# honeybase-php

## setup
- install composer & lumen in [here](http://lumen.laravel.com/docs/installation#install-composer)
- `composer update`

## serve
- `php artisan serve`

## confirm
- see `sample/index.html`
- request to `curl -XPOST 'http://localhost:8000/api/v1/db/push' -d 'table=users_tbl&value={"name":"Shogo", "age": 24, "job":"engineer", "address": "Setagaya"}'`
- `tail -f storage/logs/lumen.log`

## development roadmap status
- http interface and controller readied.
- push and select with DB by request params.
