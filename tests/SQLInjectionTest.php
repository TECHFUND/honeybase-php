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

  public function testSelectInjection1() {
    // where句に恒等を入れて全件取得する攻撃
    // "SELECT * FROM users WHERE unique_name = 't\\' OR \\'t\\' = \\'t' AND nickname = 't\\' OR \\'t\\' = \\'t' AND social_id = 't\\' OR \\'t\\' = \\'t' AND type = 't\\' OR \\'t\\' = \\'t'"
    $db = new MysqlAdaptor();
    $malphrase = "t' OR 't' = 't";
    $result = $db->select("users", ['unique_name'=>$malphrase, "nickname"=>$malphrase, "social_id"=>$malphrase, "type"=>$malphrase]);
    $this->assertEquals(false, $result['flag']);
    $this->assertEquals(0, count($result['data']) );
  }

  public function testSelectInjection2() {
    // where句に恒等を入れて全件取得する攻撃
    // "SELECT * FROM articles WHERE title = 't\\' OR \\'t\\' = \\'t' AND description = 't\\' OR \\'t\\' = \\'t'"
    $db = new MysqlAdaptor();
    $malphrase = "t' OR 't' = 't";
    $result = $db->select("articles", ['title'=>$malphrase, "description"=>$malphrase]);
    $this->assertEquals(false, $result['flag']);
    $this->assertEquals(0, count($result['data']) );
  }

  public function testYenEscape(){
    // Shift_JISのデータベースで以下が文字化けする
    // 十 Ⅸ ソ 表 能 貼 暴 予 禄 圭 構 蚕 噂 欺
    // 漢字の後に\を入れれば大丈夫
    $db = new MysqlAdaptor();
    $malphrases = ["十", "Ⅸ", "ソ", "表", "能", "貼", "暴", "予", "禄", "圭", "構", "蚕", "噂", "欺"];
    foreach ($malphrases as $key => $value) {
      $result = $db->select("articles", ['title'=>$value, "description"=>$value]);
      $this->assertEquals(false, $result['flag']);
      $this->assertEquals(0, count($result['data']) );
    }
  }
}
