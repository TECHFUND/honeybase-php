<?php

use App\Util\NuLog;
use App\Util\Util;
use App\Models\MysqlAdaptor;

class MysqlAdaptorTest extends TestCase {

  public $tbl = "articles";

  public function testMySQLInsert() {
    $db = new MysqlAdaptor();
    $result = $db->insert("articles", ['title'=>Util::createRandomString(10), 'description'=>Util::createRandomString(20), "user_id"=>1]);
    NuLog::info($result['id'],__FILE__,__LINE__);
    $this->assertEquals(true, $result['flag']);
  }
  public function testMySQLDelete() {
    $test_database = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $select_result = mysqli_query($test_database, 'select * from '.$this->tbl.' order by id desc limit 1');
    $last_id = $select_result->fetch_all()[0][0];

    $db = new MysqlAdaptor();
    $result = $db->delete($this->tbl, $last_id);
    $this->assertEquals(true, $result['flag']);
  }
  public function testMySQLUpdate() {
    $test_database = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $select_result = mysqli_query($test_database, 'select * from '.$this->tbl.' order by id desc limit 1');
    $last_id = $select_result->fetch_all()[0][0];

    $db = new MysqlAdaptor();
    //NuLog::info($len,__FILE__,__LINE__);
    $result = $db->update("articles", $last_id, ["title"=>Util::createRandomString(10), "description"=>Util::createRandomString(20), "user_id"=>1]);
    $this->assertEquals(true, $result['flag']);
  }
  public function testMySQLSelect() {
    $test_database = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $select_result = mysqli_query($test_database, 'select * from '.$this->tbl.' ;');
    $len = $select_result->num_rows;

    $db = new MysqlAdaptor();
    $result = $db->select("articles", []);
    $this->assertEquals(true, $result['flag']);
    $this->assertEquals($len, count($result['data']));
  }
}
