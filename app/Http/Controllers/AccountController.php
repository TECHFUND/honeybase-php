<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class AccountController extends Controller {

  /* jsからajaxするときvar_dumpしてると落ちてallow_originエラーになるので注意 */
  public function getCurrentUser(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }


  public function signup(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function login(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function logout(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function anonymous(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }
}
