<?php namespace App\Models;

use App\Util\NuLog;

class User {
  public static function current_user($session_id){
    $current_user = null;
    $db = new MysqlAdaptor();
    $session_array = $db->select("sessions", ["session_id"=>$session_id])['data'];
    if( count($session_array) > 0 ){
      $session = $session_array[0];
      $current_user = $db->select("users", ["id"=>$session['user_id']])['data'][0];
    }
    return $current_user;
  }

}
