# honeybase-php

## setup
- install composer & lumen in [here](http://lumen.laravel.com/docs/installation#install-composer)
- `composer update`

## serve
- `php artisan serve`

## confirm
- see `sample/index.html`

### push
`curl -XPOST 'http://localhost:8000/api/v1/db/push' -d 'table=users_tbl&value={"name":"Shogo", "age": 24, "job":"engineer", "address": "Setagaya"}'`
### update
`curl -XPOST 'http://localhost:8000/api/v1/db/update' -d 'table=users_tbl' -d 'id=9' -d 'value={"name":"Peaske","age":"27","job":"Designer","address":"Shibuya"}'`
### delete
`curl -XPOST 'http://localhost:8000/api/v1/db/delete' -d 'table=users_tbl' -d 'id=9''`
### select
`curl -XGET 'http://localhost:8000/api/v1/db/select' -d 'table=users_tbl' -d 'value={"age":"24"}''`


### log check
- `tail -f storage/logs/lumen.log`

## development roadmap status
- http interface and controller readied.
- push and select with DB by request params.
