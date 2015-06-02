<?php

use App\Util\NuLog;
use App\Util\Util;
use App\Models\MysqlAdaptor;

class SQLInjectionTest extends TestCase {

  public $tbl = "articles";

  /*
    ・[SELECT] 「WHERE句の恒等」で全件取得
    ・[ALL] 「'」を入力して内部のSQLを推測する
    ・[ALL]「;」で強制的に文を終了させ、継続してDELETE文を実行する
    ・[ALL] 文字コードの変換を利用して、「脳\'＝\x96\x7C\'＝ｱ睨ｬ'」という風にシングルクォートのサニタイズを解除する
    ・[ALL] <script>を保存させて、間接XSSを行う
    ・[ALL] 数値型は数値以外で終了させられる性質を用いて擬似的に「'」を再現する
    ・[NONE] PostgresだとprepareにNULL文字を入れると文が切断される。MySQLだと'NULL'が保存される
  */

  public function testInjection1() {
    // where句に恒等を入れて全件取得する攻撃
    $db = new MysqlAdaptor();
    $malphrase = "t' OR 't' = 't";
    $result = $db->select("users", ['unique_name'=>$malphrase, "nickname"=>$malphrase, "social_id"=>$malphrase, "type"=>$malphrase]);
    NuLog::info($result['data'],__FILE__,__LINE__);
    $this->assertEquals(false, $result['flag']);
    $this->assertEquals(0, count($result['data']) );
  }

  public function testInjection2() {
    // where句に恒等を入れて全件取得する攻撃
    $db = new MysqlAdaptor();
    $malphrase = "t' OR 't' = 't";
    $result = $db->select("articles", ['title'=>$malphrase, "description"=>$malphrase]);
    NuLog::info($result['data'],__FILE__,__LINE__);
    $this->assertEquals(false, $result['flag']);
    $this->assertEquals(0, count($result['data']) );
  }
}
