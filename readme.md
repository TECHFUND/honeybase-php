# honeybase-php

## how to use
- install composer & lumen in [here](http://lumen.laravel.com/docs/installation#install-composer)
- `composer update`
- serve by `php artisan serve`
- app launch in localhost:8000

## logging
- `tail -f storage/logs/lumen.log`
- `curl -XPOST 'http://localhost:8000/api/v1/db/push' -d 'path=users&data={name:"shogo",sex:"male",action:"push"}'`-like request also returns value.

## dev status
- http interface and controller readied.
- push and select with DB by request params.
