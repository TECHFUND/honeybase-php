<?php namespace App\Util;

use Log;

class Util {

  public static function getJSON ($path) {
    $filename = $path;
    $handle = fopen($filename, 'r');
    $json_str = fread($handle, filesize($filename));
    $data = json_decode($json_str);
    fclose($handle);
    return $data;
  }

  public static function createRandomString($length) {
    $keys = array_flip(array_merge(
      range('0', '9'),
      range('a', 'z'),
      range('A', 'Z')
    ));
    $s = '';
    for ($i = 0; $i < $length; $i++) {
      $s .= array_rand($keys);
    }
    return $s;
  }

  public static function dd($x) {
    array_map(function($x) { var_dump($x); }, func_get_args()); die;
  }

}
