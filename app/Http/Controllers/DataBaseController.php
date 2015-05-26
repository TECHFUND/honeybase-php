<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MysqlAdaptor;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Log;


class DataBaseController extends Controller {

  /* jsからajaxするときvar_dumpしてると落ちてallow_originエラーになるので注意 */
  public function push(Request $request)
  {
    $data = $request->all();
    $tbl = $data["table"];
    $json = json_decode($data["data"]);
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $db = new MysqlAdaptor();
    $result = false;

    if($tbl == "" || $json == null){
      Log::error("input invalid");
    } else {
      $result = $db->insert($tbl, $json);
    }
    return response($data, 200, $headers);
  }

  public function set(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function remove(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function select(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }

  public function get(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    return response($data, 200, $headers);
  }
}
