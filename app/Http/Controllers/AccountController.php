<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MysqlAdaptor;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

use Log;
use App\Util\NuLog;
use App\Util\Util;

class AccountController extends Controller {

  /* jsからajaxするときvar_dumpしてると落ちてallow_originエラーになるので注意 */
  public function getCurrentUser(Request $request)
  {
    $session_id = $request->cookie(SERVICE_NAME.'id');
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $data = $request->all();
    $db = new MysqlAdaptor();
    $result = $db->select("sessions", ["session_id"=>$session_id]); // "id"=>にしたい
    $flag = false;
    $user = null;
    if( count($result['data']) == 1 ){
      $session = $result['data'][0];
      $user_id = $session['user_id'];
      $headers = ['Access-Control-Allow-Origin' => ORIGIN, "Set-Cookie"=>SERVICE_NAME."id"."=".$session_id, "Access-Control-Allow-Credentials"=>"true"];
      $user = $db->select("users", ["id"=>$user_id])['data'][0];
      $flag = true;
    } else {
      // sessionのcookieと違うのでcookieを消してやる
      $headers = ['Access-Control-Allow-Origin' => ORIGIN, "Set-Cookie"=>"", "Access-Control-Allow-Credentials"=>"true"];
    }
    return response(["flag"=>$flag, "user"=>$user], 200, $headers);
  }

  public function oauth(Request $request)
  {
    $session_id = $request->cookie(SERVICE_NAME.'id');
    NuLog::info($session_id); // should be null
    $data = $request->all();
    $token = $data['user_access_token'];
    $provider = $data['provider'];

    if($provider == "facebook"){
      FacebookSession::setDefaultApplication(FACEBOOK_CONSUMER_KEY, FACEBOOK_CONSUMER_SECRET);
      $session = new FacebookSession($token);

      if($session) {
        try {
          $me_request = new FacebookRequest($session, 'GET', '/me');
          $user_profile = $me_request->execute()->getGraphObject(GraphUser::className());
          $social_id = $user_profile->getId();
        } catch(FacebookRequestException $e) {
          Log::error( "Exception occured, code: " . $e->getCode() );
          Log::error( " with message: " . $e->getMessage() );
        }
      }
      $user = $this->searchOrCreateUser($social_id);
      $session_id = $this->createOrUpdateSession($user);
    }

    $headers = ['Access-Control-Allow-Origin' => ORIGIN, "Set-Cookie"=>SERVICE_NAME."id"."=".$session_id, "Access-Control-Allow-Credentials"=>"true"];
    return response(["flag"=>true, "user"=>$user], 200, $headers);
  }

  public function logout(Request $request)
  {
    $db = new MysqlAdaptor();
    $session_id = $request->cookie(SERVICE_NAME.'id');
    $result = $db->select("sessions", ["session_id"=>$session_id]);
    $flag = false;
    $headers = ['Access-Control-Allow-Origin' => ORIGIN, "Access-Control-Allow-Credentials"=>"true"];
    $status = 503;

    if( count($result["data"]) == 1 ){
      $flag = true;
      $status = 200;
      $db->delete("sessions", $result["data"][0]['id']);
      $headers += ["Set-Cookie"=>""];
    } else {
      NuLog::error('logout something wrong');
    }
    return response(["flag"=>$flag], $status, $headers);
  }






  /****************************
   * OAUTH FUNCTION
   ****************************/
  private function searchOrCreateUser($social_id){
    /* アカウントがまだ存在しなかったら作る。存在したらスルー。 */
    $db = new MysqlAdaptor();
    $existing_user = $db->select("users", ["social_id"=>$social_id]);
    $user = null;
    if( count($existing_user['data']) == 0 ){
      /* ユーザーが存在しないので、ユーザーを作る */
      $user_data = ["unique_name"=>"", "nick_name"=>"", "social_id"=>$social_id, "type"=>"login"];
      $inserted_result = $db->insert("users", $user_data);
      $user_data += ["id"=>$inserted_result['id']];
      $user = ($inserted_result['flag']) ? $user_data : null;
      /* ユーザーが存在しないときはidを返せてないっぽい */
    } else {
      /* ユーザーが存在するので、検索ヒットしたユーザーを返す */
      $user = $existing_user["data"][0];
    }
    return $user;
  }

  private function createOrUpdateSession($user){
    /* 既存・新規作成ユーザーIDをランダム文字列と紐づける */
    if(is_array($user)){
      $db = new MysqlAdaptor();
      $existing_session = $db->select("sessions", ["user_id"=>$user["id"]]);
      $new_session_id = Util::createRandomString(100);
      if( count($existing_session['data']) > 0 ) {
        $target_id = $existing_session['data'][0]['id'];
        $db->update("sessions", $target_id, ["session_id"=>$new_session_id, "user_id"=>$user['id']]);
      } else {
        $db->insert("sessions", ["session_id"=>$new_session_id, "user_id"=>$user['id']]);
      }
    } else {
      Log::error("null arg in createOrUpdateSession");
    }
    return $new_session_id;
  }

}
