<?php

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ERROR | E_PARSE );
require_once "timer.php";

header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: x-requested-with');




/*
PHP REST SQL: A HTTP REST interface to MySQL written in PHP
Copyright (C) 2004 Paul James <paul@peej.co.uk>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Database Requirements:

Session Table for storing session ids, etc.
User Table for login lookup

*/


/* $id$ */

/**
 * PHP REST SQL class
 * The base class for the Rest SQL system that opens up a REST interface to a MySQL database.
 */
class PHPRestSQL {
    
    /**
     * Parsed configuration file
     * @var str[]
     */
    var $config;
    
    /**
     * Database connection
     * @var resource
     */
    var $db;
    
    /**
     * The HTTP request method used.
     * @var str
     */
    var $method = 'GET';
	
    /**
     * The HTTP request data sent (if any).
     * @var str
     */
    var $requestData = NULL;
	
	/**
	 * The URL extension stripped off of the request URL
	 * @var str
	 */
	var $extension = NULL;
	
    /**
     * The database table to query.
     * @var str
     */
    var $table = NULL;

    /**
     * The primary key of the database row to query.
     * @var str[]
     */
    var $uid = NULL;
    
    /**
     * Array of strings to convert into the HTTP response.
     * @var str[]
     */
    var $output = array();
    
    /**
     * Type of display, database, table or row.
     */
    var $display = NULL;

    var $restricted = true;
    var $restrictedFields = array();
    
    var $userType = -1;
    var $userId = -1;
    var $userSchema = "";
    var $userTimeZone = 0;
    var $userDaylightSavings = 0;

    var $logfile = '../log.txt';

    var $benchmarkTimer;

    /**
     * Constructor. Parses the configuration file "phprestsql.ini", grabs any request data sent, records the HTTP
     * request method used and parses the request URL to find out the requested table name and primary key values.
     * @param str iniFile Configuration file to use
     */
    function PHPRestSQL($iniFile = 'phprestsql.ini') {
        $this->logToFile("Request Received.  Vars:");
        $this->logToFile(var_export($_REQUEST, true));


        $this->config = parse_ini_file($iniFile, TRUE);
        
        if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD'])) {

            if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
                $this->requestData = '';
                $httpContent = fopen('php://input', 'r');
                while ($data = fread($httpContent, 1024)) {
                    $this->requestData .= $data;
                }
                fclose($httpContent);
            }

            //add params for put and delete commands
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method == "PUT" || $method == "DELETE") {
                $params = array();
                parse_str($this->requestData, $params);
                $GLOBALS["_{$method}"] = $params;
                $_REQUEST = $params + $_REQUEST;
            }

            
            $urlString = substr($_SERVER['REQUEST_URI'], strlen($this->config['settings']['apiURL']));
			$urlParts = explode('/', $urlString);
			
			$lastPart = array_pop($urlParts);
			$dotPosition = strpos($lastPart, '.');
			if ($dotPosition !== FALSE) {
				$this->extension = substr($lastPart, $dotPosition + 1);
				$lastPart = substr($lastPart, 0, $dotPosition);
			}
            $dotPosition = strpos($lastPart, '?');
            if ($dotPosition !== FALSE) {
                $lastPart = substr($lastPart, 0, $dotPosition);
            }


            array_push($urlParts, $lastPart);
			
			if (isset($urlParts[0]) && $urlParts[0] == '') {
				array_shift($urlParts);
			}
			
            if (isset($urlParts[0])) $this->table = $urlParts[0];
            if (count($urlParts) > 1 && $urlParts[1] != '') {
                array_shift($urlParts);
                foreach ($urlParts as $uid) {
                    if ($uid != '') {
                        $this->uid[] = $uid;
                    }
                }
            }
            
            $this->method = $_SERVER['REQUEST_METHOD'];


        }
    }
    
    /**
     * Connect to the database.
     */
    function connect() {
        $database = $this->config['database']['type'];
        require_once($database.'.php');
        $this->db = new $database(); 
        if (isset($this->config['database']['username']) && isset($this->config['database']['password'])) {
            if (!$this->db->connect($this->config['database'])) {
                trigger_error('Could not connect to server', E_USER_ERROR);
            }
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$this->config['database']['username'] = $_SERVER['PHP_AUTH_USER'];
			$this->config['database']['password'] = $_SERVER['PHP_AUTH_PW'];
            if (!$this->db->connect($this->config['database'])) {
                $this->unauthorized();
                exit;
            }
        } else {
            $this->unauthorized();
            exit;
        }
    }
    
    /**
     * Execute the request.
     */
    function exec() {
        $this->connect();
        $this->db->executeStmt("DELETE FROM user_sessions WHERE last_accessed <  DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        if (isset($_COOKIE["mysession"])) {

            $sql = "SELECT users.*, user_types.schema FROM users, user_sessions, user_types WHERE users.user_type_id=user_types.id AND users.id=user_sessions.user_id AND user_sessions.session_key='$_COOKIE[mysession]'";

            $this->logToFile("Cookies working");
            $resource = $this->db->row($this->db->executeStmt($sql));
            if (isset($resource)) {
                $this->db->executeStmt("UPDATE user_sessions SET last_accessed = NOW() WHERE session_key='$_COOKIE[mysession]'");
                $this->userId = $resource["id"];
                $this->userType = $resource["userType"];
                $this->userSchema = $resource["schema"];
                $this->userTimeZone = $resource["timezone"];
                $this->userDaylightSavings = $resource["daylight_savings"];
            }
        } else if (isset($_REQUEST["session_id"])) {
            $sql = "SELECT users.*, user_types.schema FROM users, user_sessions, user_types WHERE user_types.id=users.user_type_id AND users.id=user_sessions.user_id AND user_sessions.session_key='$_REQUEST[session_id]'";
            $this->logToFile("No cookies");
            $resource = $this->db->row($this->db->executeStmt($sql));
            if (isset($resource)) {
                $this->db->executeStmt("UPDATE user_sessions SET last_accessed = NOW() WHERE session_key='$_REQUEST[session_id]'");
                $this->userId = $resource["id"];
                $this->userType = $resource["userType"];
                $this->userSchema = $resource["schema"];
                $this->userTimeZone = $resource["timezone"];
                $this->userDaylightSavings = $resource["daylight_savings"];
            }
            unset($_REQUEST["session_id"]);

        } else {
        }


        require_once('login-functions.php');
        require_once('custom-functions.php');

        $this->benchmarkTimer = new Benchmark();
        switch ($this->method) {
            case 'GET':
                if (!custom_get($this)) $this->get();
                /*
                  if (!custom_get($this)) {
                    require_once('mql.php');
                    $mql = new MQL();
                    $dsn = "mysql:dbname=cti_corporate_engagement;host=mysql.project-files.net;port=3306";

                    echo $mql->run($this->userId, $query, $schema, $dsn, $this->config['database']['username'], $this->config['database']['password']);
                    die;
                    //$this->get();
                }
                 */
                break;
            case 'POST': //mapped to update for now....  better used for operations that would result in a different server state if executed a different number of times

                if (!login_function($this)) {
                    if (!custom_post($this)) {
                        $this->post();
                    }
                }
                break;
            case 'PUT': //mapped to create for now...  better used for operations that would result in the same server state even if executed a different number of times
                $this->put();
                break;
            case 'DELETE':
                $this->delete();
                break;
        }
        $this->db->close();
    }

    function respondWithSuccessObject($success='false', $code=0, $message='',$session_id='',$timezone='',$daylightsavings='') {
        $return = array();
        $field = array (
            field => 'success',
            value => $success
        );
        $return[] = $field;
        if (strpos($code,"id:")===FALSE) {
            $field = array (
                field => 'code',
                value => $code
            );
        } else {
            $field = array (
                field => 'code',
                value => 1
            );
            $return[] = $field;
            $field = array (
                field => 'id',
                value => str_replace("id:","", $code)
            );
        }
        $return[] = $field;
        $field = array (
            field => 'message',
            value => substr(json_encode($message),1,strlen(json_encode($message))-2)
        );
        $return[] = $field;
        if ($session_id!="") {
            $field = array (
                field => 'session_id',
                value => $session_id
            );
            $return[] = $field;
        }
        if ($timezone!="") {
            $field = array (
                field => 'timezone',
                value => $timezone
            );
            $return[] = $field;
        }
        if ($daylightsavings!="") {
            $field = array (
                field => 'daylight-savings',
                value => $daylightsavings
            );
            $return[] = $field;
        }
        $this->output['row'] = $return;
        $this->display = 'row';
        $this->generateResponseData();
        die();
    }

    function respondWithCSV($sql, $optHeaders="") {
        $result = $this->db->executeStmt($sql);
        if ($optHeaders!="") {
            $headers = split(",", $optHeaders);
        } else {
            $num_fields = $this->db->getColumnCount($result);
            $headers = array();
            for ($i = 0; $i < $num_fields; $i++)
            {
                $headers[] = $this->db->getColumnName($result , $i);
            }
        }
        $fp = fopen('php://output', 'w');
        if ($fp && $result)
        {
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="report.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            fputcsv($fp, $headers);
            while ($row = $this->db->row($result))
            {
                fputcsv($fp, array_values($row));
            }
            die;
        }
    }

    /**
     * Get the primary keys for the request table.
     * @return str[] The primary key field names
     */
    function getPrimaryKeys() {
    	return $this->db->getPrimaryKeys($this->table);

        #$resource = $this->db->getColumns($this->table);
        #$primary = NULL;
        #if ($resource) {
        #    while ($row = $this->db->row($resource)) {
        #        if ($row['Key'] == 'PRI') {
        #            $primary[] = $row['Field'];
        #        }
        #    }
        #}
        #return $primary;
    }
    
    /**
     * Execute a GET request. A GET request fetches a list of tables when no table name is given, a list of rows
     * when a table name is given, or a table row when a table and primary key(s) are given. It does not change the
     * database contents.
     * NOTE: Table return not ideal - only returns ids... not row values.
     *
     */
    function get() {
        if ($this->restricted) {
            if(isset($this->userType)) {

            //if ($this->table) {
                $this->uid[0] = "";
                $this->uid["user_id"] = $this->userId;
                $where = '';
                switch($this->userType) {
                    case 1:
                        //acct mgr
                        if ($this->table=="data") $this->table = "organizations";

                        if ($this->table!="organizations") {
                            $this->unauthorized();
                            return;
                        }

                       /* $this->display = 'table';
                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'notes'
                        );
                        $resource = $this->db->getTable("", $this->table); */

                        $this->getDataJson($this->table);

                        break;
                    case 2:
                        //org sponsor
                        if ($this->table=="data") $this->table = "organizations";
                        if ($this->table!="organizations") {
                            $this->unauthorized();
                            return;
                        }
                        $where = "id=$this->userId";
                        $this->display = 'row';
                        $resource = $this->db->getRow($this->table, $where);

                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'notes'
                        );
                        $this->restrictedFields[] = array (
                            'table'=>'coachs',
                            'field'=>'pay_rate'
                        );
                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'pay_rate'
                        );

                        $this->getDataJson($this->table, $this->userId);

                        break;
                    case 3:
                        //coach
                        if ($this->table=="data") $this->table = "coachs";
                        if ($this->table!="coachs")  {
                            $this->unauthorized();
                            return;
                        }


                        $this->restrictedFields[] = array (
                            'table'=>'clients',
                            'field'=>'bill_rate'
                        );
                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'bill_rate'
                        );

                        /*

                        $where = "id=$this->userId";
                        $resource = $this->db->getRow($this->table, $where);
                        $this->display = 'row';
                        */

                        $this->getDataJson($this->table, $this->userId);

                        break;
                    case 4:
                        if ($this->table=="data") $this->table = "clients";
                        if ($this->table!="clients")  {

                            $this->unauthorized();
                            return;
                        }

                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'bill_rate'
                        );

                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'pay_rate'
                        );

                        $this->restrictedFields[] = array (
                            'table'=>'coachs',
                            'field'=>'pay_rate'
                        );
                        $this->restrictedFields[] = array (
                            'table'=>'clients',
                            'field'=>'bill_rate'
                        );
                        /*$where = "id=$this->userId";

                        $resource = $this->db->getRow($this->table, $where);
                        $this->display = 'row';
                        */
                        $this->getDataJson($this->table, $this->userId);

                        break;
                    case 5:
                        if ($this->table=="data") $this->table = "organizations";
                        if ($this->table!="organizations")  {
                            $this->unauthorized();
                            return;
                        }

                        /* $this->display = 'table';
                        $resource = $this->db->getTable("", $this->table);
                        */
                        $this->restrictedFields[] = array (
                            'table'=>'sessions',
                            'field'=>'notes'
                        );
                        $this->getDataJson($this->table);
                        break;
                    default:
                        $this->unauthorized();
                        return;
                        break;
                }
/*
                if ($resource) {

                    if ($this->db->numRows($resource) == 1 ) {
                        $this->output['row'] = $this->getRowData($this->table, $this->db->getRelatedTables($this->table), $this->db->row($resource), "");
                        $this->generateResponseData();
                    } else if ($this->db->numRows($resource) > 1) {
                        $this->display = 'table';
                        $this->output['table'] = $this->getTableData($this->table, $resource, "");
                        //$profiling = $timer->getProfiling();

                        $this->generateResponseData();
                        return;
                    } else {
                        $this->notFound();
                    }
                } else {
                    $this->unauthorized();
                } */
            } else {
                $this->unauthorized();
                return;
            }

        } else {
            $this->unauthorized();
            return;
        }



         /* else {
            if ($this->table) {
                $primary = $this->getPrimaryKeys();
                if ($primary) {
                    if ($this->uid[0] == 'search'){  //find by field value
                        $this->display = 'row';
                        array_shift($this->uid);
                        $where = '';
                        for ($i=0; $i<count($this->uid)/2;$i++) {
                            $where .= '`' . $this->uid[$i].'` = \''.$this->uid[$i+1].'\' AND ';
                        }
                        $where = substr($where, 0, -5);
                        $resource = $this->db->getRow($this->table, $where);

                        if ($resource) {
                            if ($this->db->numRows($resource) == 1 ) {
                                $this->output['row'] = $this->getRowData($this->table, $this->db->getRelatedTables($this->table), $this->db->row($resource), "");
                                $this->generateResponseData();
                            } else if ($this->db->numRows($resource) > 1) {
                                $this->display = 'table';
                                $this->output['table'] = $this->getTableData($this->table, $resource, "");
                                $this->generateResponseData();
                                return;
                            } else {
                                $this->notFound();
                            }
                        } else {
                            $this->unauthorized();
                        }
                    } else if ($this->uid && count($primary) == count($this->uid)) { // get a row by id

                        $this->display = 'row';
                        $where = '';

                        foreach($primary as $key => $pri) {
                            $where .= $pri.' = \''.$this->uid[$key].'\' AND ';
                        }

                        $where = substr($where, 0, -5);

                        $resource = $this->db->getRow($this->table, $where);
                        if ($resource) {
                            if ($this->db->numRows($resource) == 1) {
                                $this->output['row'] = $this->getRowData($this->table, $this->db->getRelatedTables($this->table), $this->db->row($resource), "");
                                $this->generateResponseData();
                                return;
                            } else {
                                $this->notFound();
                            }
                        } else {
                            $this->unauthorized();
                        }
                    } else { // get table
                        $this->display = 'table';
                        $resource = $this->db->getTable(join(', ', $primary), $this->table);
                        if ($resource) {
                            if ($this->db->numRows($resource) > 0) {
                                $this->output['table'] = $this->getTableData($this->table, $resource, "");
                            }
                            $this->generateResponseData();
                        } else {
                            $this->unauthorized();
                        }
                    }
                }
            } else { // get database
                $this->display = 'database';
                $resource = $this->db->getDatabase();
                if ($resource) {
                    if ($this->db->numRows($resource) > 0) {
                        while ($row = $this->db->row($resource)) {
                            $this->output['database'][] = array(
                                'xlink' => $this->config['settings']['apiURL'].'/'.reset($row),
                                'value' => reset($row)
                            );
                        }
                        $this->generateResponseData();
                    } else {
                        $this->notFound();
                    }
                } else {
                    $this->unauthorized();
                }
            }
        } */
    }
/*
    function getTableData($tablename, $data, $parentNodes) {
        require_once 'timer.php';
        $this->logToFile("getTableData: $tablename");

        $timer = new Benchmark();


        $values = array();
        $relationships = $this->db->getRelatedTables($tablename);
        $timer->mark("1");
        $this->logToFile("GetRelatedTable Query: " . $timer->elapsed('start', '1'));

        while ($row = $this->db->row($data)) {
           $timer->mark("A");
            $values[] = $this->getRowData($tablename, $relationships, $row, $parentNodes);
            $timer->mark("B");
            $this->logToFile("A-B: " . $timer->elapsed('A', 'B'));
        }
        $field = array(
            'istable' => true,
            'field' => $tablename,
            'value' => $values
        );
        $timer->mark("C");

        return $field;
    }

    function getSimpleTableData($tablename, $data) {
        $values = array();
        $j = $this->db->numRows($data);

        for ($i=0;$i<$j;$i++) { //} (
            $row = $this->db->row($data);
            $this->logToFile(print_r($row, true));
            $rowvalues = array();
            foreach ($row as $column => $val) {
                    $field = array(
                        'field' => $column,
                        'value' => $val
                    );
                    $rowvalues[] = $field;
            }
            $values[] = $rowvalues;
        }
        $field = array(
            'istable' => true,
            'field' => $tablename,
            'value' => $values
        );
        return $field;
    }

    function getRowData($tablename, $relationships, $row, $parentNodes) {
        $values = array();
        $parentNodes .= "|$tablename|";
        //if ($row) {
            foreach ($row as $column => $data) {
                foreach($this->restrictedFields as $rf) {
                    if ($tablename==$rf["table"] && $rf["field"]==$column) {
                        // do not add...

                    } else {
                        $field = array(
                            'field' => $column,
                            'value' => $data
                        );
                        //if (substr($column, -strlen($this->config['database']['foreignKeyPostfix'])) == $this->config['database']['foreignKeyPostfix']) {
                        //    $field['xlink'] = $this->config['settings']['apiURL'].'/'.substr($column, 0, -strlen($this->config['database']['foreignKeyPostfix'])).'/'.$data;
                        //}
                        $values[] = $field;
                    }
                }

            }

            //get related data objects...
            if (is_array($relationships['parents'])) {
                foreach ($relationships['parents'] as $p) {
                    if (strpos("|$parentNodes|", $p) === false ) {
                        //echo "Parent: $p ----- ";
                        $where = "id=" . $row[$this->make_singular($p)."_id"];
                        $resource = $this->db->getRow($p, $where);
                        if ($resource) {
                            //if ($this->db->numRows($resource) > 1) {
                                //this should never be used because an object should have only one parent
                            //    echo "ERROR: Returning Child Table $p for $tablename<br>";
                             //   $field = array(
                             //       'field' => $p,
                             //       'value' => $this->getTableData($p, $resource, $parentNodes)
                             //   );
                           // } else if ($this->db->numRows($resource)==1) {
                                //echo "Returning Parent Row $p for $tablename<br>";
                                //$prelationships = $this->db->getRelatedTables($p);
                                //do not load children for security purposes
                                //$prelationships["children"] = array();
                                $rel = $this->db->getRelatedTables($p);
                                $rel['children'] = array();

                                $field = array(
                                    'field' => $p,
                                    'value' => $this->getRowData($p,$rel, $this->db->row($resource), $parentNodes)
                                );
                                $values[] = $field;
                           // }

                        }
                        //echo "Adding Field: " . print_r($field);

                    }
                }
            }

            if (is_array($relationships['children'])) {
                foreach ($relationships['children'] as $p) {
                   // echo "Child: $p " . strpos("|$parentNodes|", $p) . " ----- ";
                    if (strpos("|$parentNodes|", $p) === false ) {
                        //echo "Child: $p ";
                        $where = $this->make_singular($tablename)."_id=" . $row["id"];
                        //echo $where ."--------";
                        $resource = $this->db->getRow($p, $where);
                        if ($resource) {
                            if ($this->db->numRows($resource) > 1) {
                                //echo "Returning Child Table $p for $tablename<br>";
                                $field = array(
                                    'field' => $p,
                                    'value' => $this->getTableData($p, $resource, $parentNodes)
                                );
                                $values[] = $field;
                            } else if ($this->db->numRows($resource)==1) {
                                //echo "Returning Child Row $p for $tablename<br>";
                                $field = array(
                                    'field' => $p,
                                    'value' => $this->getRowData($p, $this->db->getRelatedTables($p), $this->db->row($resource), $parentNodes)
                                );
                                $values[] = $field;
                            }
                        }
                        //echo "Adding Field: " . print_r($field);

                    }
                }
            }


            if (is_array($relationships['linked'])){
                foreach ($relationships['linked'] as $p) {

                    if (strpos("|$parentNodes|", $p) === false ) {


                        $parts = explode("_", $p);
                        //echo $tablename . " id=" . $row["id"];
                        if ($parts[1]==$tablename) {
                            $jtbl = $parts[2];
                            $resource = $this->db->getJoinTable($parts[1], $p, $parts[2], $row["id"]);
                        } else {
                            $jtbl = $parts[1];
                            $resource = $this->db->getJoinTable($parts[2], $p, $parts[1], $row["id"]);
                        }

                        if ($resource) {
                                 $field = array(
                                    'field' => $jtbl,
                                    'value' => $this->getRowData($jtbl, array(), $this->db->row($resource), $parentNodes)
                                );
                                $values[] = $field;

                        }

                    }
                }
            }
        return $values;
    }
*/
    /**
     * Execute a POST request.
     */
    function post() {  //mapped to update function


        if (!$this->checkPutPostPermissions($this->userType, $this->table)) {
            $this->logToFile("Unauthorized Post: " . $this->table . "(" . $this->userType . ")");
            $this->unauthorized();
            die;
        }
        $userId = 0;

        if ($this->table && $this->uid) {
            if ($this->requestData) {
                $primary = $this->getPrimaryKeys();
                if ($primary && count($primary) == count($this->uid)) { // update a row
                    $pairs = $this->parseRequestData();


                    $sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('user_id') AND COLUMN_NAME!='user_sessions' AND TABLE_NAME='" . $this->table . "' AND TABLE_SCHEMA='" . $this->db->dbname . "'";
                    //echo $sql;
                    $userRowsAffected = false;
                    $resource = $this->db->executeStmt($sql);
                    if ($this->db->numRows($resource) > 0) { //this is a table with a user associated with it so create the user as well as the record
                        //if ($this->table=="clients" || $this->table=="coachs" || $this->table=="organizations") {
                        $userdata = array();
                        $sendEmail = false;
                        $userfields = array("first_name", "last_name", "email", "username", "phone", "userType","sendemail");
                        foreach($userfields as $fld) {
                            if (isset($pairs[$fld])) {
                                if ($fld=="email") $userdata["username"] = $pairs[$fld];
                                else if ($fld=="userType" || $fld=="user_type_id") {
                                    $userdata["user_type_id"] = $pairs[$fld];
                                    $userdata["userType"] = $pairs[$fld];
                                } else if ($fld=="sendemail") $sendEmail = $pairs["sendemail"];
                                else $userdata[$fld] = $pairs[$fld];
                                unset($pairs[$fld]);
                            }
                        }

                        $sql = 'SELECT t.user_id, u.userType FROM ' . $this->table . ' t INNER JOIN users u ON t.user_id=u.id WHERE t.id=' . $this->uid[0];
                        $userResource = $this->db->executeStmt($sql);
                        $userId = $this->db->row($userResource);
                        $userId = $userId["user_id"];
                        $theUserType = $userId["userType"];

                        if (count($userdata)>0) {
                            $values = '';
                            foreach ($userdata as $column => $data) {
                                $values .= '`'.$column."` = '".$this->db->escape($data)."', ";
                            }
                            $values = substr($values, 0, -2);
                            $where = ' id=' . $userId;

                            $resource = $this->db->updateRow("users", $values, $where);
                            if ($resource) {
                                if ($this->db->numAffected() > 0) $userRowsAffected = true;
                            } else {
                                $this->internalServerError();
                            }
                        }

                        if ($sendEmail && $sendEmail!="false") {
                            $errmsg = "";
                            $inviteType = "invite";
                            if ($theUserType==2) $inviteType = "invite_sponsor";
                            else if ($theUserType==3) $inviteType="invite_coach";
                            else if ($theUserType==4) $inviteType="invite_client";
                            if (!send_mail($this, $userId, $inviteType, true, $errmsg)) {
                                $this->respondWithSuccessObject('false',99, "Error sending mail: " . $errmsg);
                            }
                        }
                    }


                    $where = '';
                    foreach($primary as $key => $pri) {
                        $where .= $pri.' = \''.$this->uid[$key].'\' AND ';
                    }
                    $where = substr($where, 0, -5);

                    //update the coach id and see if it is changed, if so send coach/client update email.
                    if ($this->table=="clients" && isset($pairs["coach_id"]) && $pairs["coach_id"]!="null") {
                        //$this->logToFile("Testing client/coach pairing for notification.");
                        $values = "`coach_id` = ".$pairs["coach_id"];

                        $resource = $this->db->updateRow("clients", $values, $where);
                        //$this->logToFile("Update Sql: " + $this->db->getLastSQLStatement());
                        if ($resource) {
                           // $this->logToFile("Resource ok.  Num rows: " + $this->db->numAffected());
                            if ($this->db->numAffected() > 0) {
                                $userRowsAffected = true;
                                $this->logToFile("Sending coach/client pairing notifications");
                                if (!send_coach_client_notification_mail($this, $userId, $errmsg)) {
                                    $this->logToFile("Error sending coach notification email to client.");
                                } else {
                                    $this->logToFile("Notifications sent!");
                                }
                            }
                        }
                    }


                    if (isset($pairs["session_start_datetime"])) {
                        $pairs["session_datetime"] = date("Y-m-d H:i:s", strtotime(str_replace("-","/",$pairs["session_start_datetime"]))); // $parts["year"] . "-" . $parts["month"] . "-" . $parts["day"] . " " . $parts["hour"] . ":" . $parts["minute"] . ":00";
                        unset($pairs["session_start_datetime"]);
                    }
                    if (isset($pairs["session_end_datetime"])) {
                        $pairs["session_end_datetime"] = date("Y-m-d H:i:s", strtotime(str_replace("-","/",$pairs["session_end_datetime"]))); //$parts["year"] . "-" . $parts["month"] . "-" . $parts["day"] . " " . $parts["hour"] . ":" . $parts["minute"] . ":00";
                        $pairs["duration"] = date_diff_minutes($pairs["session_datetime"],$pairs["session_end_datetime"]);
                        unset($pairs["session_end_datetime"]);
                    }
                    if (isset($pairs["session_datetime"])) {
                        //convert to UTC time...
                        $pairs["session_datetime"] = $this->db->dateToUtc($pairs["session_datetime"], $this->userTimeZone, $this->userDaylightSavings);
                    }

                    if (isset($pairs["start_date"])) {
                        $pairs["start_date"] = date("Y-m-d", strtotime(str_replace("-","/",$pairs["start_date"]))); //$parts["year"] . "-" . $parts["month"] . "-" . $parts["day"];
                    }
                    if (isset($pairs["tags"])) $pairs["tags"] = str_replace(" ", "", $pairs["tags"]);
                    
                    if (isset($pairs["bio"]) && $this->userType==3) $pairs["bio_complete"] = 1;
                    $values = '';
                    foreach ($pairs as $column => $data) {
                        $values .= '`'.$column."` = '".$this->db->escape($data)."', ";
                    }
                    $values = substr($values, 0, -2);


                    $resource = $this->db->updateRow($this->table, $values, $where);
                    if ($resource) {
                        if ($userRowsAffected || $this->db->numAffected() > 0) {
                            $this->respondWithSuccessObject('true');
                        } else {
                            $this->respondWithSuccessObject('true',1,"No rows affected by update.");
                            //$this->badRequest();
                        }
                    } else {
                       $this->internalServerError();
                    }
                } else {
                    $this->badRequest();
                }
            } else {
                $this->lengthRequired();
            }
        } else {
            $this->logToFile("POST not allowed. " . $this->table);
            $this->methodNotAllowed('GET, HEAD');
        }
    }

    /**
     * Execute a PUT request. A PUT request adds a new row to a table given a table and name=value pairs in the
     * request body.
     */
    function put() {

        if (!$this->checkPutPostPermissions($this->userType, $this->table)) {
            $this->unauthorized();
            die;
        }

        if ($this->table) {
            if ($this->requestData) {
               // prepare data for INSERT
                $pairs = $this->parseRequestData();
                $user_id = -1;

                //check for a session being inserted for a client without a coach...  and pull pay and bill rates
                if ($this->table=="sessions" && $this->userType==1) {
                  $sql = "SELECT coach_id FROM clients WHERE coach_id IS NULL AND id=" . $pairs["client_id"];
                  $resource = $this->db->executeStmt($sql);
                  if ($this->db->numRows($resource) > 0)
                    $this->respondWithSuccessObject('false',15, "Please assign a coach to this client before adding the session.");
                }

                //check for bill and pay rates for session entry
                if ($this->table=="sessions") {
                    if (!isset($pairs["bill_rate"])||!isset($pairs["pay_rate"])) {
                        $sql = "SELECT cl.bill_rate, co.pay_rate FROM clients cl, coachs co WHERE cl.coach_id=co.id AND cl.id=" . $pairs["client_id"];
                        $resource = $this->db->executeStmt($sql);
                        $row = $this->db->row($resource);
                        if (!isset($pairs["bill_rate"])) $pairs["bill_rate"] = $row["bill_rate"];
                        if (!isset($pairs["pay_rate"])) $pairs["pay_rate"] = $row["pay_rate"];
                    }
                }



                $sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('user_id') AND COLUMN_NAME!='user_sessions' AND TABLE_NAME='" . $this->table . "' AND TABLE_SCHEMA='" . $this->db->dbname . "'";
                //echo $sql;
                $resource = $this->db->executeStmt($sql);
                $theUserType = 0;



                if ($this->db->numRows($resource) > 0) { //this is a table with a user associated with it so create the user as well as the record
                //if ($this->table=="clients" || $this->table=="coachs" || $this->table=="organizations") {
                    $userdata = array();
                    $userfields = array("first_name", "last_name", "email", "username","phone", "userType","sendemail");
                    $sendEmail = true;
                    foreach($userfields as $fld) {
                        if (isset($pairs[$fld])) {
                            if ($fld=="email") $userdata["username"] = $pairs[$fld];
                            else if ($fld=="userType" || $fld=="user_type_id") {
                                $userdata["user_type_id"] = $pairs[$fld];
                                $userdata["userType"] = $pairs[$fld];
                                $theUserType = $userdata["userType"];
                            } else if ($fld=="sendemail") {
                                $sendEmail = $pairs["sendemail"];
                                unset($pairs[$fld]);
                            } else $userdata[$fld] = $pairs[$fld];
                            unset($pairs[$fld]);
                        }
                    }
                    $values = join('", "', $userdata);
                    $names = join('`, `', array_keys($userdata));
                    $resource = $this->db->insertRow("users", $names, $values);

                    if (!$resource) {
                        if ($this->db->getError()==1062)  $this->respondWithSuccessObject('false',$this->db->getError(), "That email / username already exists.");
                        else  $this->respondWithSuccessObject('false',$this->db->getError(), "Error (" . $this->db->getError() . ") adding user.");
                    }


                    $pairs["user_id"] = $this->db->lastInsertId();
                    $errmsg = "";

                    if ($sendEmail && $sendEmail != "false") {

                        $inviteType = "invite";
                        if ($theUserType==2) $inviteType = "invite_sponsor";
                        else if ($theUserType==3) $inviteType="invite_coach";
                        else if ($theUserType==4) $inviteType="invite_client";
                        if (!send_mail($this, $pairs["user_id"], $inviteType, true, $errmsg)) {
                            $this->db->deleteRow('users', "id=$pairs[user_id]");
                            $this->respondWithSuccessObject('false',99, "Error sending mail: " . $errmsg);
                        }
                    }
                }

                if ($this->userType==3) $pairs["coach_id"] = $this->user_to_table_id("coachs", $this->userId);
                else if ($this->userType==4) $pairs["client_id"] = $this->user_to_table_id("clients", $this->userId);
                else if ($this->userType==2) $pairs["organization_id"] = $this->user_to_table_id("organizations", $this->userId);
                else if ($this->userType==1 && $this->table=="sessions") {
                    $pairs["coach_id"] = $this->get_coach_for_client($pairs["client_id"]);
                    if ($pairs["coach_id"]==-1) $this->respondWithSuccessObject('false',"0","Error adding session: Client has not selected a coach.");
                }

                if (isset($pairs["session_start_datetime"])) {
                    $pairs["session_datetime"] = date("Y-m-d H:i:s", strtotime(str_replace("-","/",$pairs["session_start_datetime"]))); //$parts["year"] . "-" . $parts["month"] . "-" . $parts["day"] . " " . $parts["hour"] . ":" . $parts["minute"] . ":" . $parts["second"];
                    unset($pairs["session_start_datetime"]);
                }
                if (isset($pairs["session_end_datetime"])) {
                    $pairs["session_end_datetime"] = date("Y-m-d H:i:s", strtotime(str_replace("-","/",$pairs["session_end_datetime"]))); //$parts["year"] . "-" . $parts["month"] . "-" . $parts["day"] . " " . $parts["hour"] . ":" . $parts["minute"] . ":" . $parts["second"];
                    $pairs["duration"] = date_diff_minutes($pairs["session_datetime"],$pairs["session_end_datetime"]);
                    unset($pairs["session_end_datetime"]);
                }
                if (isset($pairs["session_datetime"])) {
                    //convert to UTC time...
                    $pairs["session_datetime"] = $this->db->dateToUtc($pairs["session_datetime"], $this->userTimeZone, $this->userDaylightSavings);
                }
                if (isset($pairs["start_date"])) {
                    $pairs["start_date"] = date("Y-m-d", strtotime(str_replace("-","/",$pairs["start_date"]))); //$parts["year"] . "-" . $parts["month"] . "-" . $parts["day"];
                }
                if (isset($pairs["tags"])) $pairs["tags"] = str_replace(" ", "", $pairs["tags"]);
                $values = join('", "', $pairs);
                $names = join('`, `', array_keys($pairs));



                $resource = $this->db->insertRow($this->table, $names, $values);

                if (!$resource) $this->respondWithSuccessObject("false",$this->db->getError(), "Error inserting record.  Not saved.");


                if ($this->table=="clients" && isset($pairs["coach_id"]) && $pairs["coach_id"]!="null") {
                    //send an email to coach and client

                    $this->logToFile("Sending client and coach notification.");

                    if (!send_coach_client_notification_mail($this, $pairs["user_id"], $errmsg)) {
                        $this->logToFile("Error sending coach notification email to client.");
                    }
                }


                $this->respondWithSuccessObject('true', "id:" . $this->db->lastInsertId(), "");
            } else {
                $this->lengthRequired();
            }
        } elseif ($this->table) {
            $this->logToFile("PUT not allowed");
            $this->methodNotAllowed('GET, HEAD, PUT');

        } else {
            $this->logToFile("PUT not allowed.");
            $this->methodNotAllowed('GET, HEAD');
        }
    }

    function checkPutPostPermissions($userType, $table) {
        switch($userType) {
            case 1:
                //acct mgr - full permisisons
                if ($table == "users") return false;
                break;
            case 2:
                //org sponsor - sesssions and clients (and I would say maybe not even these...)
                if ($table != "sessions" && $table!="clients") return false;
                break;
            case 3:
                //coach
                if ($table != "sessions" && $table!="documents" && $table!="coachs" && $table!="clients") return false;
                break;
            case 4:
                //client
                if ($table != "documents" && $table!="sessions") return false;
                break;
            default: //accounting not authorised to update anything
                return false;
                break;
        }
        return true;
    }
    /**
     * Execute a DELETE request. A DELETE request removes a row from the database given a table and primary key(s).
     */
    function delete() {
        switch($this->userType) {
            case 1:
                //acct mgr
                break;
            case 3:
                if ($this->table != "sessions" && $this->table!="documents") {
                    $this->unauthorized();
                    return;
                }
                break;
            case 4:
                //client
               if ($this->table != "documents") {
                   $this->unauthorized();
                   return;
               }
                break;
            default:
                $this->unauthorized();
                return;
                break;
        }
        if ($this->table && $this->uid) {
            $primary = $this->getPrimaryKeys();
            //echo "(" . count($primary) . ","  . count($this->uid) . ") ";
            if ($primary && count($primary) == count($this->uid)) { // delete a row
                $where = '';
                foreach($primary as $key => $pri) {
                    $where .= $pri.' = \''.$this->uid[$key].'\' AND ';
                }
                $where = substr($where, 0, -5);



                $resource = $this->db->deleteRow($this->table, $where);
                $this->logToFile("DELETE SQL: " . $this->db->getLastSQLStatement());

                if ($resource) {
                    if ($this->db->numAffected() > 0) {
                        $this->noContent();
                    } else {
                        $this->notFound();
                    }
                } else {
                    $this->respondWithSuccessObject('false', $this->db->getError(), "Error on delete...");
                }
            }
        } elseif ($this->table) {
            $this->logToFile("DELETE not allowed");
            $this->methodNotAllowed('GET, HEAD, PUT');
        } else {
            $this->logToFile("DELETE not allowed");
            $this->methodNotAllowed('GET, HEAD');
        }
    }
    
    /**
     * Parse the HTTP request data.
     * @return str[] Array of name value pairs
     */
    function parseRequestData() {
        $values = array();
        $pairs = explode("&", $this->requestData);
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair);
            if (isset($parts[0]) && isset($parts[1]) && $parts[0]!="session_id") {
                $values[$parts[0]] =  $this->db->escape(urldecode($parts[1]));
            }
        }
        return $values;
    }
    
    /**
     * Generate the HTTP response data.
     */
    function generateResponseData() {

		if ($this->extension) {
			if (isset($this->config['mimetypes'][$this->extension])) {
				$mimetype = $this->config['mimetypes'][$this->extension];
				if (isset($this->config['renderers'][$mimetype])) {
					$renderClass = $this->config['renderers'][$mimetype];
				}
			}
		} elseif (isset($_SERVER['HTTP_ACCEPT'])) {

            $accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
            $orderedAccepts = array();
            foreach ($accepts as $key => $accept) {
                $exploded = explode(';', $accept);
                if (isset($exploded[1]) && substr($exploded[1], 0, 2) == 'q=') {
                    $orderedAccepts[substr($exploded[1], 2)][] = $exploded[0];
                } else {
                    $orderedAccepts['1'][] = $exploded[0];
                }
            }
            krsort($orderedAccepts);
            foreach ($orderedAccepts as $acceptArray) {
                foreach ($acceptArray as $accept) {
                    if (isset($this->config['renderers'][$accept])) {
                        $renderClass = $this->config['renderers'][$accept];
                        break 2;
                    } else {
                        $grep = preg_grep('/'.str_replace(str_replace($accept, '*', '.*'), "/", "\/").'/', array_keys($this->config['renderers']));
                        if ($grep) {
                            $renderClass = $this->config['renderers'][$grep[0]];
                            break 2;
                        }
                    }
                }
            }
        } else {
            $renderClass = array_shift($this->config['renderers']);
        }

        if (!isset($renderClass)) $renderClass = array_shift($this->config['renderers']);

        if (isset($renderClass)) {

			require_once($renderClass);


			$renderer = new PHPRestSQLRenderer();



			$renderer->render($this);

            $this->benchmarkTimer->mark("end");
            $this->logToFile("Function Benchmark: " . $this->benchmarkTimer->elapsed('start', 'end'));


            exit;
		} else {
            $this->logToFile("Not Acceptable 1");
			$this->notAcceptable();
			exit;
		}
    }
        
    /**
     * Send a HTTP 201 response header.
     */
    function created($url = FALSE) {
        header('HTTP/1.0 201 Created');
        if ($url) {
            header('Location: '.$url);   
        }
    }
    
    /**
     * Send a HTTP 204 response header.
     */
    function noContent() {
        header('HTTP/1.0 204 No Content');
    }
    
    /**
     * Send a HTTP 400 response header.
     */
    function badRequest() {
        header('HTTP/1.0 400 Bad Request');
    }
    
    /**
     * Send a HTTP 401 response header.
     */
    function unauthorized($realm = 'Blackbox') {
        header('WWW-Authenticate: Basic realm="'.$realm.'"');
        header('HTTP/1.0 403 Forbidden');
    }
    
    /**
     * Send a HTTP 404 response header.
     */
    function notFound() {
        header('HTTP/1.0 404 Not Found');
    }
    
    /**
     * Send a HTTP 405 response header.
     */
    function methodNotAllowed($allowed = 'GET, HEAD') {
        header('HTTP/1.0 405 Method Not Allowed');
        header('Allow: '.$allowed);
    }
    
    /**
     * Send a HTTP 406 response header.
     */
    function notAcceptable() {
        header('HTTP/1.0 406 Not Acceptable');
        echo join(', ', array_keys($this->config['renderers']));
    }
    
    /**
     * Send a HTTP 411 response header.
     */
    function lengthRequired() {
        header('HTTP/1.0 411 Length Required');
    }
    
    /**
     * Send a HTTP 500 response header.
     */
    function internalServerError() {

        header('HTTP/1.0 500 Internal Server Error');
    }

    function make_singular($string) {
        return substr_replace(str_replace("_users", "", $string),"",-1);
    }

    function getGuid($namespace = '') {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['LOCAL_ADDR'];
        $data .= $_SERVER['LOCAL_PORT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = '' .
            substr($hash,  0,  8) .
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12) .
            '';
        return $guid;
    }

    function initiateSession($userId) {
        $sessionKey = $this->getGuid();

        $this->db->executeStmt("INSERT INTO user_sessions (user_id, session_key) VALUES ($userId, '$sessionKey')");
        setcookie("mysession", "$sessionKey", time()+86400); //one day...

        return $sessionKey;
    }

    function terminateSession() {
        $this->db->executeStmt("DELETE FROM user_sessions WHERE session_key='$_COOKIE[mysession]'");
        setcookie("mysession", "", time()-86400); //remove cookie...
    }

    

    function logToFile($msg) {
        $fd = fopen($this->logfile, "a"); // append date/time to message
        $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
        fwrite($fd, $str . "\n");
        fclose($fd);
    }


    function getDataJson($tablename, $recordId=-1) {
        $sql = $this->getDataSql($tablename, "", "", $recordId);
        //echo $sql[0];
        //return;
        $resource = $this->db->executeStmt($sql[0]);
        if (!$resource) $this->unauthorized();
        if ($this->db->numRows($resource)==0) $this->notFound();

        $a = array();
        $a[$tablename] = new RDO();
        $a[$tablename]->tablename=$tablename;
        $skipTable = false;
        while ($row = $this->db->row($resource, $this->userTimeZone, $this->userDaylightSavings)) {
            $tblarr = array();
            foreach ($row as $key => $value) {
                list($tbl, $col) = explode("|", $key);
                $parts = explode("_", str_replace("_users", "", $tbl));
                $tblname = end($parts);
                if ($col=="id") { //new table we're dealing with...  check if we've already got this record...
                    if (!isset($a[$tbl])) $a[$tbl] = new RDO();

                    $a[$tbl]->tablename = $tblname;

                    if ($a[$tbl]->current_id == $value) {
                        $skipTable = true;
                        continue;
                    } else {
                        //this is working with the former table/row to roll up all children
                        if ( count($a[$tbl]->children) > 0) {
                            //     echo "Adding children to table: $tbl " . count($a[$tbl]->children) . "\n";

                            foreach ( $a[$tbl]->children as $childTable) {
                                //   echo "Child: $childTable Name: ". $a[$childTable]->tablename . "\n";
                                $a[$tbl]->currentRow[$a[$childTable]->tablename] = $a[$childTable]->rows;
                                $a[$childTable] = null;
                            }
                        }
                        //echo "Adding row to `$tbl`\n";
                        if (isset($a[$tbl]->currentRow) && count($a[$tbl]->currentRow)>0) $a[$tbl]->rows[]=$a[$tbl]->currentRow;
                        //if (count($a[$tbl]->currentRow)==0) echo "ZERO COUNT FOR TABLE $tbl\n";
                        //echo "Adding row to $tbl " . count($a[$tbl]->rows) . "\n";
                        $a[$tbl]->currentRow = array();
                        $a[$tbl]->currentRow[$col] = $value;
                        $a[$tbl]->current_id = $value;

                        if (count($tblarr)>0 && !in_array($tbl, $a[end($tblarr)]->children)) {
                            //  echo "Adding child ($tbl) to parent " . end($tblarr) . "\n";
                            $a[end($tblarr)]->children[] = $tbl;
                        }
                        //else if (count($tblarr)==0 && !in_array($tbl, $a[$tablename]->children)) $a[$tablename]->children[] = $tbl;

                        array_push($tblarr, $tbl);
                        //echo "Adding $tbl\n";
                        $skipTable = false;
                    }
                }
                else if ($skipTable) continue;
                else if ($tbl == "endmarker") {
                    $currentTable = array_pop($tblarr);
                    //echo "Popped $currentTable\n";
                    //echo "endmarker $currentTable " . $a[$currentTable]->currentRow["id"] . "\n";
                    if ( count($a[$currentTable]->children) > 0) {
                        // echo "Adding children to table: $currentTable " . count($a[$currentTable]->children) . "\n";

                        foreach ( $a[$currentTable]->children as $childTable) {
                            // echo "Child: $childTable Name: ". $a[$childTable]->tablename . "\n";
                            $a[$currentTable]->currentRow[$a[$childTable]->tablename] = $a[$childTable]->rows;
                            $a[$childTable] = null;
                        }
                    }
                    if (isset($a[$currentTable]->currentRow)) $a[$currentTable]->rows[]=$a[$currentTable]->currentRow;


                    $a[$currentTable]->currentRow = array();
                    continue;
                }
                else {
                    $a[$tbl]->currentRow[$col] = $value;
                    //echo "v:$tbl - $col - $value \n";
                }

            }

        }

        $tbl = $tablename;
        foreach ( $a[$tbl]->children as $childTable) $a[$tbl]->currentRow[$a[$childTable]->tablename] = $a[$childTable]->rows;
        $a[$tbl]->rows[] = $a[$tbl]->currentRow;
        $x = array();
        $x[$tablename] = $a[$tbl]->rows;
        echo json_encode($x);

    }

    function getDataJsonNEW($tablename, $recordId=-1) {
        $sql = $this->getDataSql($tablename, "", "", $recordId);
$sql[0] = "SELECT o.id as `organizations|id`,o.organization_name as `organizations|organization_name`,o.user_id as `organizations|user_id`,o.addr_street as `organizations|addr_street`,o.addr_city as `organizations|addr_city`,o.addr_state as `organizations|addr_state`,o.addr_zip as `organizations|addr_zip`,o.notes as `organizations|notes`,o.budget as `organizations|budget`,o.first_name as `organizations|first_name`,o.last_name as `organizations|last_name`,o.email as `organizations|email`,odt.documentTemplate_id as `organizations_documentTemplates|id`, odt.title as `organizations_documentTemplates|title`,'e' as `endmarker|342459`, oc.coach_id as `organizations_coachs|id`, oc.bio as `organizations_coachs|bio`,'e' as `endmarker|342450`  FROM organizations_users o LEFT JOIN organization_documentTemplates odt ON o.id=odt.organization_id LEFT JOIN organization_coachs oc ON o.id=oc.organization_id";

       // echo $sql[0];
       // return;
        $resource = $this->db->executeStmt($sql[0]);
        if (!$resource) $this->unauthorized();
        if ($this->db->numRows($resource)==0) $this->notFound();

        $a = array();
        $a[$tablename] = new RDO();
        $a[$tablename]->tablename=$tablename;
        $skipTable = false;
        while ($row = $this->db->row($resource, $this->userTimeZone, $this->userDaylightSavings)) {
            $tblarr = array();
            foreach ($row as $key => $value) {
                list($tbl, $col) = explode("|", $key);
                $parts = explode("_", str_replace("_users", "", $tbl));
                $tblname = end($parts);
                if ($col=="id") { //new table we're dealing with...  check if we've already got this record...
                    if (!isset($a[$tbl])) $a[$tbl] = new RDO();

                    $a[$tbl]->tablename = $tblname;

                    if ($a[$tbl]->current_id == $value) {
                        $skipTable = true;
                        continue;
                    } else {
                        //this is working with the former table/row to roll up all children
                        if ( count($a[$tbl]->children) > 0) {
                            //     echo "Adding children to table: $tbl " . count($a[$tbl]->children) . "\n";

                            foreach ( $a[$tbl]->children as $childTable) {
                                //   echo "Child: $childTable Name: ". $a[$childTable]->tablename . "\n";
                                $a[$tbl]->currentRow[$a[$childTable]->tablename] = $a[$childTable]->rows;
                                $a[$childTable] = null;
                            }
                        }
                        //echo "Adding row to `$tbl`\n";
                        if (isset($a[$tbl]->currentRow) && count($a[$tbl]->currentRow)>0) $a[$tbl]->rows[]=$a[$tbl]->currentRow;
                        //if (count($a[$tbl]->currentRow)==0) echo "ZERO COUNT FOR TABLE $tbl\n";
                        //echo "Adding row to $tbl " . count($a[$tbl]->rows) . "\n";
                        $a[$tbl]->currentRow = array();
                        $a[$tbl]->currentRow[$col] = $value;
                        $a[$tbl]->current_id = $value;

                        if (count($tblarr)>0 && !in_array($tbl, $a[end($tblarr)]->children)) {
                            //  echo "Adding child ($tbl) to parent " . end($tblarr) . "\n";
                            $a[end($tblarr)]->children[] = $tbl;
                        }
                        //else if (count($tblarr)==0 && !in_array($tbl, $a[$tablename]->children)) $a[$tablename]->children[] = $tbl;

                        array_push($tblarr, $tbl);
                        //echo "Adding $tbl\n";
                        $skipTable = false;
                    }
                }
                else if ($skipTable) continue;
                else if ($tbl == "endmarker") {
                    $currentTable = array_pop($tblarr);
                    //echo "Popped $currentTable\n";
                    //echo "endmarker $currentTable " . $a[$currentTable]->currentRow["id"] . "\n";
                    if ( count($a[$currentTable]->children) > 0) {
                        // echo "Adding children to table: $currentTable " . count($a[$currentTable]->children) . "\n";

                        foreach ( $a[$currentTable]->children as $childTable) {
                            // echo "Child: $childTable Name: ". $a[$childTable]->tablename . "\n";
                            $a[$currentTable]->currentRow[$a[$childTable]->tablename] = $a[$childTable]->rows;
                            $a[$childTable] = null;
                        }
                    }
                    if (isset($a[$currentTable]->currentRow)) $a[$currentTable]->rows[]=$a[$currentTable]->currentRow;


                    $a[$currentTable]->currentRow = array();
                    continue;
                }
                else {
                    $a[$tbl]->currentRow[$col] = $value;
                    //echo "v:$tbl - $col - $value \n";
                }

            }

        }

        $tbl = $tablename;
        foreach ( $a[$tbl]->children as $childTable) $a[$tbl]->currentRow[$a[$childTable]->tablename] = $a[$childTable]->rows;
        $a[$tbl]->rows[] = $a[$tbl]->currentRow;
        $x = array();
        $x[$tablename] = $a[$tbl]->rows;
        echo json_encode($x);

    }


    function getDataJsonNEW2($tablename, $recordId=-1) {
       $sql = $this->getDataSql($tablename, "", "", $recordId);
       echo $sql[0];
       return;
       $resource = $this->db->executeStmt($sql[0]);
        if (!$resource) $this->unauthorized();
        if ($this->db->numRows($resource)==0) $this->notFound();

       $a = array();
       //$a[$tablename] = new RDO();
       //$a[$tablename]->tablename=$tablename;
       //$skipTable = false;

       while ($row = $this->db->row($resource, $this->userTimeZone, $this->userDaylightSavings)) {
           $a[] = $row;
       }

       echo json_encode($this->getObject($a));

   }

    function getObject($a) {
        $keys = array_keys($a[0]);
        $cId = $a[0][$keys[0]];
        $sets = array();
        $currentRow = array();

        //group the rows into sets made up of a single top level record
        for ($i=0; $i<count($a); $i++) {
            $keys = array_keys($a[$i]);
            if ($cId!=$a[$i][$keys[0]]) {
                $sets[] = $currentRow;
                $cId = $a[$i][$keys[0]];
                $currentRow = array();
            }
            $currentRow[] = $a[$i];
        }



        $objects = array();
        foreach($sets as $set) {
            $r = $this->getObjectSet($set);
            $objects[] = $r[0];
        }

        return $objects;

    }

    function getObjectSet($set) {
        $firstRow = $set[0];
        $obj = array("id"=>$firstRow[0]);
        $endOfObject = -1;
        $endOfObjectType = "";
        for($i=1;$i<count($firstRow);$i++) {
            $keys = array_keys($firstRow);
            list($tbl, $col) = explode("|", $keys[$i]);
            $parts = explode("_", str_replace("_users", "", $tbl));
            $tblname = end($parts);
            if ($col=="id") {
                $endOfObject = $i;
                $endOfObjectType = $tblname;
                break;
            } else if ($tbl=="endmarker") {
               $endOfObject = $i;
                $endOfObjectType = "end";
                break;
            } else {
                $thekeys = array_keys($firstRow);
                $obj[$col] = $firstRow[$thekeys[$i]];
            }
        }

        if ($endOfObject == -1) {
            $endOfObjectType = "end";
        }

        for($i=0; $i<count($set); $i++) {
            $set[$i] = array_slice($set[$i], $endOfObject);
        }

        while ($endOfObjectType!="end") {
            $parts = $this->getObject($set);
            if (!isset($obj[$endOfObjectType])) $obj[$endOfObjectType] = array();
            $obj[$endOfObjectType][] = $parts[0];
            $set = $parts[1];
            $endOfObjectType = $parts[2];
        }

        return array($obj, $set, $endOfObjectType);

    }

    function getDataSql($tablename, $parentTable="", $parentNodes="", $recordId=-1) {

        $isChild = true;
        if ($parentTable!="")$parentTable = $parentTable . "_"; else ($isChild = false);

        $fields = "";
        $parent_fields = "";
        $joins = "";
        $where = "";
       // $user_tables = $this->db->getUserTables();
        $tblname = $this->db->checkUserTable($tablename);

        if ($recordId!=-1) $where = " WHERE $tblname.user_id=$recordId";

        $relationships = $this->db->getRelatedTables($tablename);
        $parentNodes .= "|$tablename|";

        $cols = $this->db->getTableColumns($tblname);
       foreach ($cols as $col) {
           //AA:AA
           $skipIt = false;
           foreach($this->restrictedFields as $rf) {
               if ($tablename==$rf["table"] && $rf["field"]==$col) {
                   //skip...
                   $skipIt = true;
                   break;
               }
           }
           if (!$skipIt) {
               $fields .= "$tblname.$col as `$parentTable$tablename|$col`,";
               $parent_fields .= "`$parentTable$tablename|$col`,";
           }

        }

        if (is_array($relationships['linked']) && !isChild){
            foreach ($relationships['linked'] as $p) {
                if ((strpos("|$parentNodes|", $p) === false) && (strpos("|$parentNodes|", str_replace("_users","",$p)) === false) ) {
                    $parts = explode("_", $p);
                    if ($parts[1]==$tablename) {
                        $jtbl = $parts[2];
                        $ltbl = "link_" . $tablename . "_" . $jtbl;
                    } else {
                        $jtbl = $parts[1];
                        $ltbl = "link_" . $jtbl . "_" . $tablename;
                    }

                    $joins .= " LEFT JOIN $ltbl ON $tblname.id=$ltbl." . $this->make_singular($tablename) .  "_id LEFT JOIN $jtbl ON $ltbl." . $this->make_singular($jtbl) . "_id=$jtbl.id ";
                    //echo "Link Table $jtbl $parentTable$tablename $parentNodes\n";
                    $lcols = $this->db->getTableColumns($jtbl);
                    foreach ($lcols as $col) {
                        //AA:AA
                        foreach($this->restrictedFields as $rf) {
                            if ($tablename==$rf["table"] && $rf["field"]==$col) {
                                //skip...
                                $skipIt = true;
                                break;
                            }
                        }
                        if (!$skipIt) {
                            $fields .= "$jtbl.$col as `$tablename"."_$jtbl|$col`,"  ;
                            $parent_fields .= "`$tblname"."_$jtbl|$col`," ;
                        }
                    }
                    $marker =  "'e' as `endmarker|" . rand(1000,1000000) . "`,";
                    $fields .= $marker;
                    $parent_fields .= $marker;
                }
            }
        }

        if (is_array($relationships['parents'])  && !$isChild){
            foreach ($relationships['parents'] as $jtbl) {

                $jtbl = $this->db->checkUserTable($jtbl);
                if ((strpos("|$parentNodes|", $jtbl) === false) && (strpos("|$parentNodes|", str_replace("_users","",$jtbl)) === false) ) {
                    $joins .= " LEFT JOIN $jtbl ON $jtbl.id=$tblname." . $this->make_singular($jtbl) .  "_id ";
                    $jcols = $this->db->getTableColumns($jtbl);
                    //echo "Parent Table $jtbl $parentTable$tablename $parentNodes\n";
                    foreach ($jcols as $col) {
                        //AA:AA
                        $skipIt = false;
                        foreach($this->restrictedFields as $rf) {
                            if ($tablename==$rf["table"] && $rf["field"]==$col) {
                                //skip...
                                $skipIt = true;
                                break;
                            }
                        }
                        if (!$skipIt) {
                            $fields .= "$jtbl.$col as `$tablename"."_$jtbl|$col`,";
                            $parent_fields .= "`$tablename"."_$jtbl|$col`,";
                        }
                    }
                    $marker =  "'e' as `endmarker|" . rand(1000,1000000) . "`,";
                    $fields .= $marker;
                    $parent_fields .= $marker;
                }
            }
        }

        if (is_array($relationships['children'])) {
            foreach ($relationships['children'] as $p) {
                if ((strpos("|$parentNodes|", $p) === false) && (strpos("|$parentNodes|", str_replace("_users","",$p)) === false) ) {
                    //echo "Child Table $p $parentTable$tablename, $parentNodes\n";
                    $child = $this->getDataSql($p, $parentTable . $tablename, $parentNodes);
                    $marker =  "'e' as `endmarker|" . rand(1000,1000000) . "`,";
                    $fields .= $child[1].$marker;
                    $parent_fields .= $child[1].$marker;
                    $joins .= " LEFT JOIN (" . $child[0] . ") _$p ON " . $tblname . ".id=_$p.`$parentTable$tablename"."_$p|" . $this->make_singular($tablename) . "_id` ";
                }
            }
        }

        $fields = substr_replace($fields ,"",-1);
        return array("SELECT $fields FROM $tblname $joins $where", $parent_fields);

    }

    function user_to_table_id($table, $userId) {
        $resource = $this->db->executeStmt("SELECT id FROM $table WHERE user_id=$userId");
        $row = $this->db->row($resource);
        return $row["id"];
    }
    function get_coach_for_client($clientId) {
        $resource = $this->db->executeStmt("SELECT coach_id FROM clients WHERE id=$clientId");
        if (!$resource) return -1;
        $row = $this->db->row($resource);
        if (!$row) return -1;
        return $row["coach_id"];
    }
}




function date_diff_minutes($d1, $d2){
    $d1 = (is_string($d1) ? strtotime($d1) : $d1);
    $d2 = (is_string($d2) ? strtotime($d2) : $d2);
    $diff_secs = abs($d1 - $d2);
    return floor($diff_secs / 60);
}


class RDO { //recursive data object
    public $tablename;
    public $full_tablename;
    public $current_id;
    public $rows = array();
    public $currentRow = array();
    public $children = array();

}

?>
