<?php namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Util\NuLog;
use App\Util\Util;

class RightsMiddleware {

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  // MiddleWareでinsert, select含めたrequestのuser_idを取得してcurrent_user.idと比較するのはありかも
  // insert, update, delete, selectにaccess: all, none, loginを定義できる
  public function handle($request, Closure $next){
    $data = $request->all();
    $table_name = $data['table'];
    $session_id = $request->cookie(SERVICE_NAME.'id');

    $resource_user_id = 'user_id';
    if($table_name == "users"){
      $resource_user_id = 'id';
    }

    // セキュリティルール in MiddleWare
    $rights = Util::getJSON(__CONFIG__.'rights.json');
    $current_user = User::current_user($session_id);
    $path_array = explode("/", $request->path());
    $action = array_pop($path_array);
    $permission_type = $rights->$table_name->$action;
    $header = ['Access-Control-Allow-Origin' => ORIGIN, "Access-Control-Allow-Credentials"=>"true"];

    if($rights->$table_name == null){
      return response(["flag"=>false, "error_message"=>"no such table_name in rights definision"], 503, $header);
    }
    if($permission_type == null){
      $permission_type = "none";
    }

    if ($permission_type == "all") {
      return $next($request);
    } elseif ($permission_type == "owner") {
      if($data[$resource_user_id] == $current_user.id){
        return $next($request);
      }
    } elseif ($permission_type == "login") {
      if($current_user != null){
        return $next($request);
      } else {
        return response(['flag'=>false, "error_message"=>"required login but not logged in"], 403, $header);
      }
    } elseif($permission_type == $current_user.type){
      // admin, writerのようなユーザー定義権限
      return $next($request);
    } else {
      // 指定無し、あるいはnone
      return response(['flag'=>false, "error_message"=>"no such rights definision, or defined as none"], 403, $header);
    }
  }

}
