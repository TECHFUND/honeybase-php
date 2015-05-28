<?php namespace App\Util;

class Util {

  public static function getJSON ($path) {
    $filename = $path;
    $handle = fopen($filename, 'r');
    $data = json_decode(fread($handle, filesize($filename)));
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

}
