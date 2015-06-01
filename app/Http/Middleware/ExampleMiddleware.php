<?php namespace App\Http\Middleware;

use Closure;

class ExampleMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }



  // MiddleWareでinsert, select含めたrequestのuser_idを取得してcurrent_user.idと比較するのはありかも
  // insert, update, delete, selectにaccess: all, none, loginを定義できる
  //
  /*
    public function check($request, Closure $next){
      $data = $request->all();
      $table_name = $data['table'];
      $session_id = $request->cookie(SERVICE_NAME.'id');

      $resource_user_id = 'user_id';
      if($table == "users"){
        $resource_user_id = 'id';
      }

      // セキュリティルール in MiddleWare
      $rights = json_decode(__DIR__.'/../../config/rights.json');
      $current_user = User.current_user($session_id);
      $action = $request->action();
      $permission_type = $rights[$table_name][$action];

      if($rights[$table_name] == null){ return $response(403, "no such table"); }
      if($permission_type == null){ $permission_type = "none" }

      if ($permission_type == "all") {
        return $next($request);
      } elseif ($permission_type == "login") {
        if($data[$resource_user_id] == $current_user.id){
          return $next($request);
        }
      } elseif($permission_type == $current_user.type){
        // admin, writerのようなユーザー定義権限
        return $next($request);
      } else {
        // 指定無し、あるいはnone
        return $response(403);
      }

      // admin, writer, login権限について
      admin権限は、current_userがusersテーブルのtype: adminなユーザーのみにアクセスを許す
      writer権限は、current_userがusersテーブルのtype: writerなユーザーのみにアクセスを許す
      login権限は、current_user.idがresource.user_idなユーザーのみにアクセスを許す
      // admin, writer権限はusersテーブルのtypeの値を自由に設定できる。matchしないときは"invalid user type"エラーを出す
      // table : [] のarray_lengthが4を超えたら"invalid length"エラーを出す
      // usersテーブルのtype: all, none, loginは予約語なので"reserved user type"エラーを出す
    }


  */

}
