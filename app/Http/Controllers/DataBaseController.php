<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class DataBaseController extends Controller {

  public function push()
  {
    //引数にRequest $requestを置いても同じエラー

    // $data = Request::all();
    //これつかうとAccess-Control-Allow-Originエラーになる。
    // 異常値をresposeに渡すとheader設定できずに送信しちゃう？
    $json = ["hoge"=>"fuga"];
    return response($json, 200, array('Access-Control-Allow-Origin' => '*'));
  }

}
