<?php namespace App\Models;

class User {
  public static function current_user($session_id){
    $db = new MysqlAdaptor();
    $session = $db->select("session", ["session_id"=>$session_id]);
    $current_user = $db->select("users", ["id"=>$session['user_id']]);
    return $current_user;
  }

}
