<?php

/**
 * PHP REST SQL XML renderer class
 * This class renders the REST response data as XML.
 */
class PHPRestSQLRenderer {

    /**
     * @var PHPRestSQL PHPRestSQL
     */
    var $PHPRestSQL;
   
    /**
     * Constructor.
     * @param PHPRestSQL PHPRestSQL
     */
    function render($PHPRestSQL) {
        $this->PHPRestSQL = $PHPRestSQL;
        switch($PHPRestSQL->display) {
            case 'database':
                $this->database();
                break;
            case 'table':
                $this->table();
                break;
            case 'row':
                $this->row();
                break;
            default:
                echo "Error - no return type specified.";
                break;
        }
    }
    
    /**
     * Output the top level table listing.
     */
    function database() {
        header('Content-Type: application/json');
        if (isset($this->PHPRestSQL->output['database'])) {
            $isfirst = true;
            foreach ($this->PHPRestSQL->output['database'] as $table) {
                if (!$isfirst) echo ",";
                $isfirst = false;
                echo '"' . $table['value'].'":"['.$table['xlink'].']"';
            }
        }
        echo '}';
    }
    
    /**
     * Output the rows within a table.
     */
    function table() {
        header('Content-Type: application/json');
        echo "{";
        if (isset($this->PHPRestSQL->output['table'])) {
            //$isfirst = true;
            //$tbl = $this->PHPRestSQL->output['table'];
            //$rows = $tbl['value'];
            //foreach ($rows as $row) {
            //    if (!$isfirst) echo ",";
            //    $isfirst = false;
            //    echo $this->rowdata($row);
            //}
            //echo print_r($this->PHPRestSQL->output['table']);
            //return;


            echo $this->rowdata($this->PHPRestSQL->output['table']);
        }
        echo "}";
    }
    
    /**
     * Output the entry in a table row.
     */
    function row() {
        if (isset($GLOBALS["override_content_type"]))
            header("Content-Type: " . $GLOBALS["override_content_type"]);
        else
            header('Content-Type: application/json');
        echo $this->rowdata($this->PHPRestSQL->output['row']);
    }

    function rowdata($row) {

        if (isset($row)) {
            $isfirst = true;
            //echo print_r($row);

            if (isset($row['istable'])) {
               // echo "IS TABLE";
                $rows = $row['value'];
                //echo print_r($rows);
                $isrowfirst = true;
                if (!$isfirst) echo ",";
                $isfirst = false;
                if (is_array($rows) && count($rows) > 0) {
                    echo '"'.$row['field'].'":[';

                    foreach ($rows as $r) {
                        //echo "!!!!$r!!!!";
                        if (!$isrowfirst) echo ",";
                        $isrowfirst = false;
                        echo $this->rowdata($r);
                    }
                    echo "]";
                }
            } else if (is_array($row) && count($row)>0) {
                echo "{";
                $isrowfirst = true;
                foreach($row as $r) {
                    if (!$isrowfirst) echo ",";
                    $isrowfirst = false;


                    if (is_array($r['value'])) {
                        if (count($r['value'])==0) {
                            if ($r['field']!="") echo '"'.$r['field'].'":[]'; else $isrowfirst = true;
                        } else {
                            if (!isset($r['value']['istable'])) echo '"'.$r['field'].'":[';
                            echo $this->rowdata($r['value']);
                            if (!isset($r['value']['istable'])) echo "]";
                        }
                    } else {
                        if ($r['field']!="") echo '"'.$r['field'].'":"'.strip_utf($r['value']) . '"'; else $isrowfirst = true;
                    }
                }
                echo '}';
            } else {
                if ($row['field']!="") echo '"'.strip_utf($row['value']) .'"';

                if (isset($field['xlink'])) {
                    echo ',"xlink":"['.$field['xlink'].']"';
                }
            }



        }

    }

}

function strip_utf($string) {
    for ($i = 0; $i < strlen ($string); $i++)
        if (ord($string[$i]) > 127)
            $string[$i] = " ";
    return $string;
}
