<?php

date_default_timezone_set('America/Vancouver');


class Mysql {
    protected $_db = null;
    protected $_table = '';

    public function open($credentials) {
        $this->_db = mysql_connect($credentials['host'], $credentials['user'], $credentials['password']);
        if (!$this->_db) {
            $msg = mysql_error();
            throw new Exception($msg);
        }                
    }    
    
    public function connect($db, $table) {
        $result = mysql_select_db($db, $this->_db);
        $exception = mysql_error($this->_db);
        if ($exception) {
            throw new Exception("$exception");
        }
        $this->_table = $table;
    }
    
    public function save($data) {
	$time = $data['eventTime']['d'] . ' ' . $data['eventTime']['t'];

        $query = "INSERT INTO {$this->_table} (eventTime, uid, event, eventData) VALUES ";
        $query .= "(" . 
                "'" . $time . "'" . ',' .
                $data['userId'] . ',' .
                "'" . $data['event'] . "'" . ',' .
                "'" . mysql_real_escape_string($data['jsonString']) . "'" .
                ")";
        
        mysql_query($query, $this->_db);
        if (mysql_errno($this->_db) === 1146) {
            $this->createCollection($this->_table);
            mysql_query($query, $this->_db);
        }
        $exception = mysql_error($this->_db);
        if ($exception) {
            throw new Exception("$exception - query failed: $query");
        }
        
        return true;
    }

    private function createCollection($name) {
        $query = "CREATE TABLE IF NOT EXISTS $name (
				  _id int(10) unsigned NOT NULL AUTO_INCREMENT,
				  eventTime datetime NOT NULL,
				  uid bigint(20) unsigned NOT NULL,
				  event varchar(128) NOT NULL,
				  eventData varchar(8000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  PRIMARY KEY (_id), KEY (fbuid)
                 ) ENGINE=myisam
                ";
        mysql_query($query, $this->_db);
        $exception = mysql_error($this->_db);
        if ($exception) {
            throw new Exception("$exception - query failed: $query");
        }
    }
}

