<?php namespace App\Util;

use Log;

class NuLog{
  public static function info($x, $file=__FILE__, $line=__LINE__){
    Log::info(' ');
    Log::info("[DUMP] - ".$file.":".$line.":".json_encode($x));
    Log::info('-------------');
    Log::info(' ');
  }
  public static function warn($x, $file=__FILE__, $line=__LINE__){
    Log::info(' ');
    Log::warn("[DUMP] - ".$file.":".$line.":".json_encode($x));
    Log::info('-------------');
    Log::info(' ');
  }
  public static function error($x, $file=__FILE__, $line=__LINE__){
    Log::info(' ');
    Log::error("[DUMP] - ".$file.":".$line.":".json_encode($x));
    Log::info('-------------');
    Log::info(' ');
  }
}
