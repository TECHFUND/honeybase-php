<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class DataBaseController extends Controller {

  public function push(Request $request)
  {
    $headers = ['Access-Control-Allow-Origin' => 'http://localhost:8001'];
    $data = $request->all();
    var_dump($data["path"]);
    var_dump($data);
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
}
