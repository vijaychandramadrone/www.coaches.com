<?php
/*
PHP REST SQL: A HTTP REST interface to relational databases
written in PHP

mysql.php :: MySQL database adapter
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
*/

/* $id$ */

/**
 * PHP REST MySQL class
 * MySQL connection class.
 */
class mysql {

    /**
     * @var resource Database resource
     */
    var $db;
    var $dbname;
    var $lastSqlStatement;
    var $logfile = '../log.txt';
    var $table_relationships;
    var $table_relationships_set = false;
    var $table_columns;
    var $table_columns_set = false;
    var $user_table_list;
    var $user_table_list_set = false;

    var $time_fields = array("session_datetime", "session_start_datetime", "session_end_datetime");

    /**
     * Connect to the database.
     * @param str[] config
     */
    function connect($config) {
        $this->dbname = $config['database'];
        if ($this->db = @mysql_pconnect(
            $config['server'],
            $config['username'],
            $config['password']
        )) {
            if ($this->select_db($config['database'])) {

                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Close the database connection.
     */
    function close() {
        mysql_close($this->db);
    }

    /**
     * Use a database
     */
    function select_db($database) {
        if (mysql_select_db($database, $this->db)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get the columns in a table.
     * @param str table
     * @return resource A resultset resource
     */
    function getColumns($table) {
        $this->lastSqlStatement = sprintf('SHOW COLUMNS FROM %s', $table);
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement, $this->db);
    }

    /**
     * Get a row from a table.
     * @param str table
     * @param str where
     * @return resource A resultset resource
     */
    function getRow($table, $where) {
        //echo sprintf('SELECT * FROM %s WHERE %s', $table, $where);
        $this->lastSqlStatement = sprintf('SELECT * FROM %s WHERE %s', $table, $where);
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Get the rows in a table.
     * @param str primary The names of the primary columns to return
     * @param str table
     * @return resource A resultset resource
     */
    function getTable($primary, $table) {
        $this->lastSqlStatement = sprintf('SELECT * FROM %s', $table);
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Get the rows in a table.
     * @param str primary The names of the primary columns to return
     * @param str table
     * @return resource A resultset resource
     */
    function getJoinTable($table1, $jtable, $table2, $table1id) {
        $this->lastSqlStatement = "SELECT b.* FROM $jtable j, $table2 b WHERE j.".$this->make_singular($table1)."_id=$table1id AND b.id=j." . $this->make_singular($table2) .  "_id";
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Get the tables in a database.
     * @return resource A resultset resource
     */
    function getDatabase() {
        $this->lastSqlStatement = 'SHOW TABLES';
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Get the primary keys for the request table.
     * @return str[] The primary key field names
     */
    function getPrimaryKeys($table) {
        $resource = $this->getColumns($table);
        $primary = NULL;
        if ($resource) {
            while ($row = $this->row($resource)) {
                if ($row['Key'] == 'PRI') {
                    $primary[] = $row['Field'];
                }
            }
        }
        return $primary;
    }


    /**
     * Get the tables with foreign key relationships with the request table.
     * @return str[] The table names
     */
    function getRelatedTables($table='all') {

        if (!$this->table_relationships_set) {

            mysql_query("use information_schema;");


            $this->lastSqlStatement = "SELECT  TABLE_NAME, REFERENCED_TABLE_NAME, (CASE WHEN REFERENCED_COLUMN_NAME = 'id' AND TABLE_NAME NOT LIKE 'link_%' THEN 1 ELSE 0 END) as CHILD_RELATIONSHIP, (CASE WHEN REFERENCED_COLUMN_NAME = 'id' THEN 1 ELSE 0 END) as PARENT_RELATIONSHIP, (CASE WHEN  REFERENCED_COLUMN_NAME = 'id' AND TABLE_NAME LIKE 'link_%' THEN 1 ELSE 0 END) as LINK_RELATIONSHIP FROM KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='" . $this->dbname . "' AND ((REFERENCED_COLUMN_NAME = 'id' AND TABLE_NAME NOT LIKE 'link_%') OR (REFERENCED_COLUMN_NAME = 'id') OR (REFERENCED_COLUMN_NAME = 'id' AND TABLE_NAME LIKE 'link_%'))";
            //$this->logToFile($this->lastSqlStatement);

            $fks = mysql_query($this->lastSqlStatement);
            $result = array();

            while ($row = mysql_fetch_array($fks)) {

                if ($row["CHILD_RELATIONSHIP"] == "1") $result[$row['REFERENCED_TABLE_NAME']]['children'][] = $row['TABLE_NAME'];
                if ($row["PARENT_RELATIONSHIP"] == "1") $result[$row['TABLE_NAME']]['parents'][] = $row['REFERENCED_TABLE_NAME'];
                if ($row["LINK_RELATIONSHIP"] == "1") $result[$row['REFERENCED_TABLE_NAME']]['linked'][] = $row['TABLE_NAME'];
            }

            $this->table_relationships = $result;

            mysql_query("use " . $this->dbname);
        }
        $this->table_relationships_set = true;
        if ($table=="all") return $this->table_relationships;
        else return $this->table_relationships[$table];
    }

    /**
     * Get the tables with foreign key relationships with the request table.
     * @return str[] The table names
     */
    function getTableColumns($table='all') {

        if (!$this->table_columns_set) {

            mysql_query("use information_schema;");


            $this->lastSqlStatement = "SELECT * FROM `COLUMNS` WHERE TABLE_SCHEMA='" . $this->dbname . "'";

            $cols = mysql_query($this->lastSqlStatement);
            $result = array();

            while ($row = mysql_fetch_array($cols)) {
                if (!isset($result[$row["TABLE_NAME"]])) $result[$row["TABLE_NAME"]] = array();
                $result[$row["TABLE_NAME"]][] = $row["COLUMN_NAME"];
            }

            $this->table_columns = $result;

            mysql_query("use " . $this->dbname);
        }
        $this->table_columns_set = true;
        if ($table=="all") return $this->table_columns;
        else return $this->table_columns[$table];
    }

    function checkUserTable($tbl) {
        if (!$this->user_table_list_set) {
           // echo "setting user table list"  . "\n";
            mysql_query("use information_schema;");
            $r = mysql_query("SELECT a.TABLE_NAME FROM `TABLES` a, TABLES b WHERE b.TABLE_NAME LIKE CONCAT(a.TABLE_NAME,'%users') AND b.TABLE_TYPE='VIEW' AND a.TABLE_NAME!=b.TABLE_NAME;");
            $this->user_table_list = array();
            while ($rr = mysql_fetch_array($r)) {
                $this->user_table_list[] = $rr["TABLE_NAME"];
            }
            $this->user_table_list_set = true;
          //  echo print_r($this->user_table_list) . "\n";
        }
        //echo $tbl . in_array($tbl, $this->user_table_list)  . "\n";
        if (in_array($tbl, $this->user_table_list)) return $tbl . "_users"; else return $tbl;
    }


    /**
     * Update a row.
     * @param str table
     * @param str values
     * @param str where
     * @return bool
     */
    function updateRow($table, $values, $where) {
        $this->lastSqlStatement = str_replace("'null'", 'null', sprintf('UPDATE %s SET %s WHERE %s', $table, $values, $where));
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Insert a new row.
     * @param str table
     * @param str names
     * @param str values
     * @return bool
     */
    function insertRow($table, $names, $values) {
       // echo sprintf('INSERT INTO %s (`%s`) VALUES ("%s")', $table, $names, $values);
        $this->lastSqlStatement = str_replace('"null"', 'null', sprintf('INSERT INTO %s (`%s`) VALUES ("%s")', $table, $names, $values));
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Get the columns in a table.
     * @param str table
     * @return resource A resultset resource
     */
    function deleteRow($table, $where) {
        $this->lastSqlStatement= sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($this->lastSqlStatement);
    }

    /**
     * Escape a string to be part of the database query.
     * @param str string The string to escape
     * @return str The escaped string
     */
    function escape($string) {
        return mysql_escape_string($string);
    }

    /**
     * Escape a string to be part of the database query.
     * @param str string The string to escape
     * @return str The escaped string
     */
    function dateToUtc($date, $tz, $dls=0) {
        if ($dls && isDaylightSavings) $tz += 1;
        if (is_string($date)) return date('Y-m-d H:i:s', strtotime(str_replace("-","/",$date))-$tz*60*60);
        return $date - $tz * 60 * 60;
    }
    function dateFromDatabase($date, $tz, $dls=0) {
        if ($dls && isDaylightSavings) $tz += 1;
        if (is_string($date)) return date('m-d-y h:i a', strtotime(str_replace("-","/",$date))+$tz*60*60);
        return $date + $tz * 60 * 60;
    }

    function isDaylightSavings($tz) {
        date_default_timezone_set("UTC");
        $todayDate = date("Y-m-d g:i a");// current date
        $currentTime = time($todayDate); //Change date into time
        $currentUserTime = $currentTime+$tz*60*60;
        $month = date('m', $currentUserTime);
        $day = date('d', $currentUserTime);
        $dow = date('N', $currentUserTime);
        //January, february, and december are out.
        if ($month < 3 || $month > 11) { return false; }
        //April to October are in
        if ($month > 3 && $month < 11) { return true; }
        $previousSunday = $day - $dow;
        //In march, we are DST if our previous sunday was on or after the 8th.
        if ($month == 3) { return $previousSunday >= 8; }
        //In november we must be before the first sunday to be dst.
        //That means the previous sunday must be before the 1st.
        return $previousSunday <= 0;
    }

    /**
     * Fetch a row from a query resultset.
     * @param resource resource A resultset resource
     * @return str[] An array of the fields and values from the next row in the resultset
     */
    function row($resource, $tz=0, $dls=0) {
        $r = mysql_fetch_assoc($resource);

        if ($tz!=0) {
            foreach($this->time_fields as $fld => $value) {
                if (isset($r[$value])) {
                    $r[$value] = $this->dateFromDatabase($r[$value], $tz, $dls);

                }
            }
        }
        return $r;
    }

    /**
     * The number of rows in a resultset.
     * @param resource resource A resultset resource
     * @return int The number of rows
     */
    function numRows($resource) {
        return mysql_num_rows($resource);
    }

    /**
     * The number of rows affected by a query.
     * @return int The number of rows
     */
    function numAffected() {
        return mysql_affected_rows($this->db);
    }

    /**
     * Get the ID of the last inserted record.
     * @return int The last insert ID
     */
    function lastInsertId() {
       // echo "ID" . mysql_insert_id();

        return mysql_insert_id();
    }

    function executeStmt($sql) {
        $this->lastSqlStatement = $sql;
        $this->logToFile($this->lastSqlStatement);
        return mysql_query($sql);
    }

    function make_singular($string) {
        return substr_replace($string ,"",-1);
    }

    function getLastSQLStatement() {
        return $this->lastSqlStatement;
    }

    function logToFile($msg) {
        $fd = fopen($this->logfile, "a"); // append date/time to message
        $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
        fwrite($fd, $str . "\n");
        fclose($fd);
    }

    function getError() {


        return mysql_errno();
    }

    function getColumnCount($r) {
        mysqli_field_count($r);
    }

    function getColumnName($r, $i) {
        mysql_field_name($r , $i);
    }
}
?>