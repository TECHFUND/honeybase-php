<?php

use App\Util\NuLog;

class InterfaceTest extends TestCase {

  public function testCurrentUser() {
    $response = $this->call('GET', '/api/v1/get_current_user');
    $this->assertResponseOk();
  }
  public function testOAuth() {
    $response = $this->call('POST', '/api/v1/oauth', ["user_access_token"=>"", "provider"=>"facebook"]);
    $this->assertResponseStatus(403);
  }
  public function testLogout() {
    $response = $this->call('POST', '/api/v1/logout');
    $this->assertResponseStatus(503);
  }


  /* フロントからusersテーブルにアクセスしたときの振る舞い */
  public function testUsersInsert() {
    $response = $this->call('POST', '/api/v1/db/insert', ["table"=>"users", "value"=>"{}"]);
    $this->assertResponseStatus(403);
  }
  public function testUsersUpdate() {
    $response = $this->call('POST', '/api/v1/db/update', ["table"=>"users", "id"=>100, "value"=>"{}"]);
    $this->assertResponseStatus(403);
  }
  public function testUsersDelete() {
    $response = $this->call('POST', '/api/v1/db/delete', ["table"=>"users", "id"=>100]);
    $this->assertResponseStatus(403);
  }
  public function testUsersSelect() {
    $response = $this->call('GET', '/api/v1/db/select', ["table"=>"users", "value"=>"{}"]);
    $this->assertResponseStatus(403);
  }

  /* フロントからsessionsテーブルにアクセスしたときの振る舞い */
  public function testSessionsInsert() {
    $response = $this->call('POST', '/api/v1/db/insert', ["table"=>"sessions", "value"=>"{}"]);
    $this->assertResponseStatus(403);
  }
  public function testSessionsUpdate() {
    $response = $this->call('POST', '/api/v1/db/update', ["table"=>"sessions", "id"=>100, "value"=>"{}"]);
    $this->assertResponseStatus(403);
  }
  public function testSessionsDelete() {
    $response = $this->call('POST', '/api/v1/db/delete', ["table"=>"sessions", "id"=>100]);
    $this->assertResponseStatus(403);
  }
  public function testSessionsSelect() {
    $response = $this->call('GET', '/api/v1/db/select', ["table"=>"sessions", "value"=>"{}"]);
    $this->assertResponseStatus(403);
  }

  /* フロントからユーザー定義テーブルにアクセスしたときの振る舞い */
  public function testUserDefinitionInsert() {
    $response = $this->call('POST', '/api/v1/db/insert', ["table"=>"userdefinition", "value"=>"{}"]);
    $this->assertResponseStatus(503);
  }
  public function testUserDefinitionUpdate() {
    $response = $this->call('POST', '/api/v1/db/update', ["table"=>"userdefinition", "id"=>100, "value"=>"{}"]);
    $this->assertResponseStatus(503);
  }
  public function testUserDefinitionDelete() {
    $response = $this->call('POST', '/api/v1/db/delete', ["table"=>"userdefinition", "id"=>100]);
    $this->assertResponseStatus(503);
  }
  public function testUserDefinitionSelect() {
    $response = $this->call('GET', '/api/v1/db/select', ["table"=>"userdefinition", "value"=>"{}"]);
    $this->assertResponseStatus(503);
  }


}
