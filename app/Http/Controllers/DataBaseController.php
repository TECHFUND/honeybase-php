<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MysqlAdaptor;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Log;


class DataBaseController extends Controller {

  /* jsからajaxするときvar_dumpしてると落ちてallow_originエラーになるので注意 */
  public function insert(Request $request)
  {
    $data = $request->all();
    $tbl = $data["table"];
    $value = json_decode($data["value"]);
    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $db = new MysqlAdaptor();
    $result = false;

    if($tbl == "" || $value == null){
      Log::error("push input invalid");
    } else {
      $result = $db->insert($tbl, $value)["flag"];
    }
    $res = ["flag"=>$result, "data"=>($result) ? $data : null];
    return response($res, 200, $headers);
  }

  public function update(Request $request)
  {
    $data = $request->all();
    $tbl = $data["table"];
    $id = $data['id'];
    $value = json_decode($data['value']);

    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $db = new MysqlAdaptor();
    $result = false;

    if($tbl == "" || $id < 0){
      Log::error("set input invalid");
    } else {
      $result = $db->update($tbl, $id, $value)["flag"];
    }
    $res = ["flag"=>$result, "data"=> ["id"=>$id, "value"=>$value]];
    return response($res, 200, $headers);
  }

  public function delete(Request $request)
  {
    $data = $request->all();
    $tbl = $data["table"];
    $id = $data['id'];

    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $db = new MysqlAdaptor();
    $result = false;

    if($tbl == "" || $id < 0){
      Log::error("remove input invalid");
    } else {
      $result = $db->delete($tbl, $id)["flag"];
    }
    $res = ["flag"=>$result, "id"=>$id];
    return response($res, 200, $headers);
  }

  public function select(Request $request)
  {
    $data = $request->all();
    $tbl = $data["table"];
    $value = json_decode($data["value"]);

    $headers = ['Access-Control-Allow-Origin' => ORIGIN];
    $db = new MysqlAdaptor();
    $result = false;

    if($tbl == "" || $value == null){
      Log::error("select input invalid");
    } else {
      $result = $db->select($tbl, $value);
    }
    $res = $result;
    return response($res, 200, $headers);
  }
}
