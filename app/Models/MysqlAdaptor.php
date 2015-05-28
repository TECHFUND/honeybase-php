<?php namespace App\Models;

use mysqli;
use Log;

/**
 * MysqlAdapter
 */
class MysqlAdaptor {

	var $database;

	// constructer
	function __construct() {

    /* 引数が無いと 'Whoops, looks like something went wrong.' になる */
		// dbaccess
    if(DB_HOST=="") {
      Log::error("no database host");
    } elseif (DB_USERNAME=="") {
      Log::error("no database username");
    } elseif (DB_PASSWORD=="") {
      Log::info("no database password");
    } elseif (DB_NAME=="") {
      Log::error("no database name");
    }
    $database = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

		// connect error
		if (mysqli_connect_errno()) {
			error_log("[" . date("Y-m-d h:i:s") . "]ConnectFailed status=" . mysqli_connect_error() . "\n", 3, LOG_PATH);
			exit;
		}

		// database = null;
		if ($database == null) {
			error_log("[" . date("Y-m-d h:i:s") . "]DB接続失敗 status=" . mysqli_connect_error() . "\n", 3, LOG_PATH);
			exit;
		}

		$this->database =& $database;
		$this->database->set_charset('utf8');
	}

	/**
	 * select method
	 * @param		str		$tbl			table name
	 * @param		arr		$data			where
	 * @return		arr					true：array("ret":true, array(0:array, 1:array ...))　false：array("ret":false)
	 */
	function select($tbl, $data = array()) {
		// init
		$where = "";
		$rows = array();
    $bool = false;

		// search tbl
		$sql = "SHOW TABLES FROM " . DB_NAME . " LIKE '" . $tbl . "';";
		$result = mysqli_query($this->database, $sql);

		// tbl none
		if (0 == $result->num_rows) {
			return ["flag"=>false, "data"=>array()];
		}


		// create where query
		foreach ($data as $key => $value) {
			if ("" != $where) {
				$where .= " AND ";
			} else {
				$where .= "WHERE ";
			}
			if (NULL === $value) {
				$where .= $key . " IS NULL";
			} else {
				$where .= $key . " = '" . $value . "'";
			}
		}

		// create sql
		$sql = "SELECT * FROM $tbl $where";

		// query
		$result = mysqli_query($this->database, $sql);

		if (0 != $result->num_rows) {
			// loop
			while ($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
      $bool = true;
		}

		return ["flag"=>$bool, "data"=>$rows];
	}



	/**
	 * insert method
	 * @param		str		$tbl			table name
	 * @param		arr		$data			insert data
	 * @return		arr					true：true　false：false
	 */
	function insert($tbl, $data = array()) {

		// init
		$insert_str = $values_str = "";
		$sql_param = array();
		$return_flg = false;

		// search tbl
		$sql = "SHOW TABLES FROM " . DB_NAME . " LIKE '" . $tbl . "';";
		$result = mysqli_query($this->database, $sql);

		// tbl none
		if (0 == $result->num_rows) {
			// create tbl
			$this->create($tbl, $data);
		}

		// count insert rows
		if (0 != count($data)) {
			// create insert query
			foreach ($data as $key => $value) {
				if ("" != $insert_str) {
					$insert_str .= ", ";
					$values_str .= ", ";
				}
				$insert_str .= $key;
				if (NULL != $value) {
					$values_str .= "'" . $this->database->real_escape_string($value) . "'";
				} else {
					$values_str .= "NULL";
				}
			}

			// auto commit to OFF
			$this->database->autocommit(FALSE);

			// create sql
			$sql = 'INSERT INTO ' . $tbl . ' (' . $insert_str . ') values (' . $values_str . ');';

			// query
			$result = mysqli_query($this->database, $sql);

			if (1 != $this->database->affected_rows) {
				// Roll back if there were rows affected is not one line
				$this->database->rollback();
        $this->errorReport($sql);
			} else {
				// Had row affected commit if one line
				$this->database->commit();
				$return_flg = true;
			}
		}

		return ["flag"=>$return_flg];
	}


	/**
	 * create tbl method
	 * @param		str		$tbl			table name
	 * @param		arr		$data			set
	 * @return		arr					true：true　false：false
	 */
	function create($tbl, $data = array()) {

		// init
		$create_str = $values_str = "";
		$sql_param = array();
		$return_flg = false;

		// count insert rows
		if (0 != count($data)) {
			// create insert query

			foreach ($data as $key => $value) {
				switch (gettype($value)) {
					case "integer":
						$type = "int(11)";
						break;
					case "string":
						$type = "text";
						break;
					case "double":
						$type = "bigint(20)";
						break;
					case "boolean":
						$type = "boolean";
						break;
				}
				$create_str .= "`" . $key . "` " . $type . ",";
			}

			// create sql
			$sql = "CREATE TABLE IF NOT EXISTS `" . $tbl . "` (`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT," . $create_str . "PRIMARY KEY (`id`), UNIQUE KEY `id` (`id`)) ENGINE=innoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			// auto commit to OFF
			$this->database->autocommit(FALSE);

			// query
			$result = mysqli_query($this->database, $sql);

			if (1 != $this->database->affected_rows) {
				// Roll back if there were rows affected is not one line
				$this->database->rollback();
        $this->errorReport($sql);
			} else {
				// Had row affected commit if one line
				$this->database->commit();
				$return_flg = true;
			}
		}
		return ["flag"=>$return_flg];
	}




	/**
	 * update method
	 * @param		str		$tbl			table name
	 * @param		int		$id			update id
	 * @param		array		$value			update value
	 * @return		arr					true：true　false：false
	 */
	function update($tbl, $id, $value = array()) {
		// init
		$sql_param = array();
		$return_flg = false;

		// search tbl
		$sql = "SHOW TABLES FROM " . DB_NAME . " LIKE '" . $tbl . "';";
		$result = mysqli_query($this->database, $sql);

		// tbl none
		if (0 == $result->num_rows) {
      Log::error("There is no table, cannot update target record");
		}


		// count insert rows
		if (0 < $id) {
			// auto commit to OFF
			$this->database->autocommit(FALSE);

			// create sql
      $set_str = 'SET ' . $this->genSetStr($value);
			$sql = 'UPDATE ' . $tbl . ' ' . $set_str . ' WHERE id=' . $id . " ;";

      // 更新
      /* 変化無し・id無し・Schemeと合わない　のときはfalseが返る */
      $existance = $this->select($tbl, ["id"=>$id])["flag"];
      $result = ($existance) ? mysqli_query($this->database, $sql) : null;

			if (1 != $this->database->affected_rows) {
				// Roll back if there were rows affected is not one line
				$this->database->rollback();
        $this->errorReport($sql);
			} else {
				// Had row affected commit if one line
				$this->database->commit();
				$return_flg = true;
			}
		}
		return ["flag"=>$return_flg];
	}

  function genSetStr($data){
    $str = "";
    foreach ($data as $key => $value) {
			if (gettype($value) == "string") {
        $value = "'".$value."'";
			}
      $str .= $key . '=' . $value . ", ";
    }
    return trim($str, ", ");
  }






	/**
	 * delete method
	 * @param		str		$tbl			table name
	 * @param		int		$id			delete id
	 * @return		arr					true：true　false：false
	 */
	function delete($tbl, $id) {
		// init
		$sql_param = array();
		$return_flg = false;

		// search tbl
		$sql = "SHOW TABLES FROM " . DB_NAME . " LIKE '" . $tbl . "';";
		$result = mysqli_query($this->database, $sql);

		// tbl none
		if (0 == $result->num_rows) {
      Log::error("There is no table, cannot remove target record");
		}


		// count insert rows
		if (0 < $id) {
			// auto commit to OFF
			$this->database->autocommit(FALSE);

			// create sql
			$sql = 'DELETE FROM ' . $tbl . ' WHERE id = ' . $id;

			// query
			$result = mysqli_query($this->database, $sql);

			if (1 != $this->database->affected_rows) {
				// Roll back if there were rows affected is not one line
				$this->database->rollback();
        $this->errorReport($sql);
			} else {
				// Had row affected commit if one line
				$this->database->commit();
				$return_flg = true;
			}
		}
		return ["flag"=>$return_flg];
	}

  function errorReport($sql){
    $msg = "[" . date("Y-m-d h:i:s") . "]Query failed by:" . $sql . "\n";
		error_log($msg, 3, LOG_PATH);
    Log::error($msg);
  }

	function mysql_exploit($result){
		$data = ($result != null) ? $result->fetch_field()->social_id : null;
		return $data;
	}

}
