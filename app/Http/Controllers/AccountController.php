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
use App\Util\Util;

class AccountController extends Controller {

  /* jsからajaxするときvar_dumpしてると落ちてallow_originエラーになるので注意 */
  public function getCurrentUser(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function oauth(Request $request)
  {
    $data = $request->all();
    $token = $data['user_access_token'];
    $provider = $data['provider'];
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];

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
      setcookie(SERVICE_NAME."id", $session_id);
    }

    return response(["flag"=>true, "user"=>$user], 200, $headers);
  }

  private function searchOrCreateUser($social_id){
    /* アカウントがまだ存在しなかったら作る。存在したらスルー。 */
    $db = new MysqlAdaptor();
    $existing_user = $db->select("users", ["social_id"=>$social_id]);
    $user = $existing_user["data"][0];
    if( count($existing_user['data']) > 0 ){
      $inserted_result = $db->insert("users", ["unique_name"=>"", "nick_name"=>"", "social_id"=>$social_id]);
      if($inserted_result['flag']){
        $user = $inserted_result['data'];
      }
    }
    return $user;
  }

  private function createOrUpdateSession($user){
    /* 既存・新規作成ユーザーIDをランダム文字列と紐づける */
    $db = new MysqlAdaptor();
    $existing_session = $db->select("sessions", ["social_id"=>$social_id]);
    $new_session_id = Util::createRandomString(100);
    if( count($existing_session['data']) > 0 ){
      $target_id = $existing_session['data'][0]['id'];
      $db->update("sessions", $target_id, ["session_id"=>$new_session_id, "user_id"=>$user['id'], "social_id"=>$user['social_id']])
    } else {
      $db->insert("sessions", ["session_id"=>$new_session_id, "user_id"=>$user['id'], "social_id"=>$user['social_id']])
    }
    return $new_session_id;
  }

  public function logout(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  /*
  public function signup(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $data = $request->all();
    return response($data, 200, $headers);
  }
  public function login(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $data = $request->all();
    return response($data, 200, $headers);
  }
  public function anonymous(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $data = $request->all();
    return response($data, 200, $headers);
  }
  */
}
