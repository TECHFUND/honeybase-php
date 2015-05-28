<?php namespace App\Util;

use Log;

class NuLog{
  public static function info($x){
    Log::info('-------------');
    Log::info(json_encode($x));
    Log::info('-------------');
  }
  public static function warn($x){
    Log::info('-------------');
    Log::warn(json_encode($x));
    Log::info('-------------');
  }
  public static function error($x){
    Log::info('-------------');
    Log::error(json_encode($x));
    Log::info('-------------');
  }
}
