<?php


class MQL {
/**
 *   Benchmarking
 */
var $callstack = array();
function callstack_push($name){
    global $callstack;
    $callstack[] = array(
        'name'      =>  $name
    ,   'microtime' =>  microtime()
    );
}
/*****************************************************************************
 *   General Functions
 ******************************************************************************/
function get_last_json_error(){
    if (function_exists('json_last_error')){
        $error = json_last_error();
        $message = $error.': ';
        switch($error){
            case JSON_ERROR_NONE:
                $message .= 'No error has occurred';
                break;
            case JSON_ERROR_DEPTH:
                $message .= 'The maximum stack depth has been exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR	:
                $message .= 'Control character error, possibly incorrectly encoded';
                break;
            case JSON_ERROR_SYNTAX:
                $message .= 'Syntax error';
                break;
            case JSON_ERROR_UTF8:
                $message .= 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
        }
    }
    else {
        $message = 'function json_last_error() does not exist - no error information available. PHP version: '.phpversion();
    }
    return $message;
}
/*****************************************************************************
 *   MQL processing Functions
 ******************************************************************************/
function analyze_type($type) {
    $type_pattern = '/^\/(\w+)\/(\w+)$/';
    $matches = array();
    if (preg_match($type_pattern, $type, $matches)){
        return array(
            'domain'    => $matches[1]
        ,   'type'     => $matches[2]
        );
    }
    return FALSE;
}

function is_filter_property($value){
    if ($value===NULL) {
        return FALSE;
    }
    else
        if (is_object($value) && count(get_object_vars($value))===0){
            return FALSE;
        }
        else
            if (is_array($value) && count($value)===0) {
                return FALSE;
            }
            else {
                return TRUE;
            }
}

function analyze_property($property_name, $property_value){
    //                      12   2 1 345          5  4 6      647                                 7
    $property_pattern = '/^(((\w+):)?(((\/\w+\/\w+)\/)?(\w+|\*))(=|<=?|>=?|~=|!=|\|=|!\|=|\?=|!\?=)?)$/';
    $matches = array();
    if (preg_match($property_pattern, $property_name, $matches)){
        return array(
            'prefix'        =>  $matches[3]
        ,   'qualifier'     =>  $matches[6]
        ,   'name'          =>  $matches[7]
        ,   'operator'      =>  isset($matches[8])? $matches[8] : NULL
        ,   'qualified'     =>  $matches[5]? TRUE : FALSE
        ,   'value'         =>  $property_value
        ,   'is_filter'     =>  $this->is_filter_property($property_value)
        ,   'is_directive'  =>  FALSE
        ,   'schema'        =>  NULL
        );
    }
    return FALSE;
}

function get_type_from_schema($domain, $type){
     $domains = $this->metadata['domains'];
    if (isset($domains[$domain])){
        $domain = $domains[$domain];
    } else {
        return NULL;
    }
    $types = $domain['types'];
    if (isset($types[$type])){
        return $types[$type];
    }
    else {
        return NULL;
    }
}

//helper for process_mql_object
function get_parent_type($parent, &$types){
    if (isset($parent) && isset($parent['schema'])) {
        $parent_schema_type_name = $parent['schema']['type'];
        $parent_schema_type = $this->analyze_type($parent_schema_type_name);
        $parent_schema_type_domain = $parent_schema_type['domain'];
        $parent_schema_type_type = $parent_schema_type['type'];
        $parent_schema_type = $this->get_type_from_schema($parent_schema_type_domain, $parent_schema_type_type);
        if (!$parent_schema_type) {
            exit('The parent type "/'
                .$parent_schema_type_domain.'/'.$parent_schema_type_type
                .'" was not found in the schema.'
                .' This indicates a logical error in the schema.'
            );
        }
        $types[$parent_schema_type_name] = $parent_schema_type;
    }
}

//helper for process_mql_object
function check_types($types) {
    switch (count($types)) {
        case 0:
            exit('Could not find a type. Currently we rely on a known type');
            break;
        case 1:
            //assigning the contents of the array to the $type variable.
            //php gurus, any better way to do this?
            foreach($types as $type_name => $type){}
            break;
        default:
            exit('Found more than one type. Currently we can handle only one type.');
    }
}

//helper for process_mql_object
function expand_star($source_properties, &$target_properties) {
    unset($target_properties['*']);
    foreach ($source_properties as $property_name => $property) {
        if (isset($target_properties[$property_name])){
            continue;
        }
        if (isset($property['column_name'])) {
            $target_properties[$property_name] = array(
                'is_directive'  =>  FALSE,
                'qualifier'     =>  '',
                'name'          =>  $property_name,
                'value'         =>  NULL,
                'is_filter'     =>  FALSE,
                'operator'      =>  NULL
            );
        }
    }
}

//helper for process_mql_object
function pre_process_properties($object_vars, &$properties, &$types, &$star_property){
    foreach ($object_vars as $property_key => $property_value) {
        if (!($property = $this->analyze_property($property_key, $property_value))){
            exit('Property "'.$property_key.'" is not valid.');
        }
        $operator = $property['operator'];
        if ($operator) {
            $operator_in = ($operator==='|=')||($operator==='!|=');
            if ($property_value === NULL
                ||  is_object($property_value)
                || ($operator_in && is_array($property_value) && count($property_value)===0)
            ){
                exit("Operator ".$operator.' '
                    .(($operator==='|=' || $operator==='!|=')
                        ? 'takes a non-empty list of values'
                        : 'takes a single value (not an object or an array)')
                );
            }
        }
        $property_qualifier = $property['qualifier'];
        $property_name      = $property['name'];

        switch ($property_name) {
            case 'type':
            case 'creator':
            case 'guid':
            case 'id':
            case 'key':
            case 'name':
            case 'permission':
            case 'timestamp':
                if ($property_qualifier==='') {
                    $property['qualifier'] = '/type/object';
                }
                break;
            case 'limit':
            case 'optional':
            case 'return':
            case 'sort':
            case '*':
                if ($property_qualifier==='' ) {
                    $property['is_directive'] = TRUE;
                    switch ($property_name) {
                        case 'optional':
                            $parent['optional'] = ($property_value===TRUE || $property_value==='optional');
                            break;
                        case '*':
                            $star_property = TRUE;
                            break;
                    }
                }
            default:
                if ($property_qualifier === '/type/object') {
                    exit('"'.$property_name.'" is not a universal property, and may not have the qualifier "'.$property_qualifier.'".');
                }
        }

        if ($property['qualifier'] === '/type/object'
            &&  $property_name         === 'type'
            &&  isset($property_value)
            && !isset($types[$property_value])
        ) {

            $type = $this->analyze_type($property_value);

            if (!$type) {
                exit('"'.$property_value.'" is not a valid type identifier.');
            }
            $domain = $type['domain'];
            $domain_type = $type['type'];
            $type = $this->get_type_from_schema($domain, $domain_type);
            if (!$type) {
                exit('Type "/'.$domain.'/'.$domain_type.'" not found in schema.');
            }
            $types[$property_value] = $type;
        }
        $properties[$property_key] = $property;
    }
}

//helper for process_mql_object
function process_properties(&$properties, $type_name, $type) {
    foreach ($properties as $property_name => &$property){
        if ($property['is_directive']===TRUE || !isset($property['name'])) {
            continue;
        }
        switch ($property['qualifier']) {
            case '/type/object':
                continue;
            case '':
                $schema_property = $type['properties'][$property['name']];
                if ($schema_property ) {
                    $property['qualifier'] = $type_name;
                    $property['schema'] = $schema_property;
                    if (isset($schema_property['join_condition'])) {
                        $property['types'][] = $schema_property['type'];
                        $property_value = &$property['value'];
                        if (is_object($property_value) || is_array($property_value)) {
                            $this->process_mql($property_value, $property);
                        }
                    }
                }
                else {
                    exit('No property "'.$property['name'].'" in type "'.$type_name.'".');
                }
                break;
            default:
                if ($property['qualifier']!==$type_name) {
                    exit('Property "'.$property['qualifier'].'/'.$property['name']
                        .'" does not belong to the type "'.$type_name.'". This feature is not supported yet.');
                }
        }

    }
}

function process_mql_object(&$mql_object, &$parent){
    $object_vars = get_object_vars($mql_object);
    $properties = array();
    $type = NULL;
    $types = array();
    $star_property = FALSE;
    $parent['properties'] = &$properties;


    $this->get_parent_type($parent, $types);

    $this->pre_process_properties($object_vars, $properties, $types, $star_property);
    $this->check_types($types);

    foreach($types as $type_name => $type){}    //extract the type name
    $parent['types'] = array_keys($types);

    if ($star_property===TRUE) {
        $this->expand_star($type['properties'], $properties);
    };
    $this->process_properties($properties, $type_name, $type);
}

function process_mql_array($mql_array, &$parent){
    $count = count($mql_array);
    switch ($count) {
        case 0:
            break;
        case 1:
            $parent['entries'] = array();
            if (array_key_exists('schema', $parent)) {
                $parent['entries']['schema'] = $parent['schema'];
            }
            $this->process_mql($mql_array[0], $parent['entries']);
            break;
        default:
            exit('Expected a dictionary or a list with one element in a read (were you trying to write?)');
    }
}

function process_mql($mql, &$parent){
    if ($mql===NULL) {
    }
    else
        if (is_object($mql)){
            $this->process_mql_object($mql, $parent);
        }
        else
            if (is_array($mql)){
                $this->process_mql_array($mql, $parent);
            }
            else {
                exit('mql query must be an object or an array, not "'.gettype($mql).'".');
            }
}
/*****************************************************************************
 *   SQL generation Functions
 ******************************************************************************/
var $t_alias_id = 0;
var $c_alias_id = 0;
var $p_id = 0;

function reset_ids(){
    $this->t_alias_id = 0;
    $this->c_alias_id = 0;
    $this->p_id = 0;
}

function get_t_alias(){
    return 't'.(++$this->t_alias_id);
}

function get_c_alias($new=TRUE){
    if ($new){
        $this->c_alias_id++;
    }
    return 'c'.$this->c_alias_id;
}

function get_p_name(){
    return 'p'.(++$this->p_id);
}

function is_optional($mql_node){
    $optional = FALSE;
    if (is_array($mql_node)) {
        if (array_key_exists('properties', $mql_node)){
            $properties = $mql_node['properties'];
            if (count($properties)===0){
                $optional = TRUE;
            }
            else
                if (isset($properties['optional'])) {
                    $optional_property = $properties['optional'];
                    $value = $optional_property['value'];
                    switch ($value) {
                        case TRUE:
                        case 'optional':
                            $optional = TRUE;
                    }
                }
        }
        else
            if (array_key_exists('entries', $mql_node)){
                $entries = $mql_node['entries'];
                if (count($entries)===NULL){
                    $optional = TRUE;
                }
            }
            else
                if (array_key_exists('value', $mql_node)) {
                    $value = $mql_node['value'];
                    if ($value===NULL) {
                        $optional = TRUE;
                    }
                }
    }
    else {
        print_r("\ntype is ".gettype($mql_node)."\n");
    }
    return $optional;
}

function get_from_clause(&$mql_node, $t_alias, $child_t_alias, $schema_name, $table_name, &$query){
    $schema = isset($mql_node['schema'])?$mql_node['schema']:NULL;
    $from = &$query['from'];
    $count_from = count($from);
    $from_line = array();
    $join_condition = '';
    if (isset($schema['direction'])) {
        $direction = $schema['direction'];
        if (($optional = $this->is_optional($mql_node))===TRUE){
            $mql_node['outer_join'] = TRUE;
            $outer_join = TRUE;
        }
        else
            if (isset($mql_node['outer_join'])) {
                $outer_join = $mql_node['outer_join'];
            }
            else {
                $outer_join = FALSE;
            }

        $from_line['join_type'] = ($outer_join===TRUE) ? 'LEFT' : 'INNER';

        switch ($direction) {
            case 'referencing->referenced':     //lookup (n:1 relationship)           
                break;
            case 'referenced<-referencing':     //lookdown (1:n relationship) - starts a separate query.
                $select = &$query['select'];
                $order_by = &$query['order_by'];
                $merge_into = &$query['merge_into'];
                $merge_into_columns = &$merge_into['columns'];
                break;
        }

        foreach ($schema['join_condition'] as $columns) {
            $join_condition .= ($join_condition==='')? 'ON':"\nAND";
            switch ($direction){
                case 'referencing->referenced':
                    $referenced_column = $t_alias.'.'.$columns['referenced_column'];

                    if ($outer_join===TRUE && $join_condition === 'ON'){
                        if ($optional===TRUE) {
                            $from_line['optionality_group'] = $t_alias;
                        }
                        else {
                            if ($count_from) {
                                $from_line['optionality_group'] = $from[$child_t_alias]['optionality_group'];
                            }
                            else {
                                $from_line['optionality_group'] = $child_t_alias;
                            }
                        }
                        $from_line['optionality_group_column'] = $referenced_column;
                    }

                    $join_condition .= ' '  .$child_t_alias.'.'.$columns['referencing_column']
                        .  ' = '.$referenced_column;

                    break;
                case 'referenced<-referencing':
                    $column_ref = $t_alias.'.'.$columns['referencing_column'];
                    $alias = $t_alias.$this->get_c_alias();
                    $merge_into_columns[] = $alias;
                    $select[$column_ref] = $alias;
                    $order_by .= ($order_by===''? 'ORDER BY ' : "\n, ");
                    $order_by .= $alias;
                    break;
            }
        }
    }
    $from_line['table'] = ($schema_name? $schema_name.'.' : '').$table_name;
    $from_line['alias'] = $t_alias;
    if ($join_condition) {
        $from_line['join_condition'] = $join_condition;
    }
    $from[$t_alias] = $from_line;
}

function map_mql_to_pdo_type($mql_type){
    switch ($mql_type){
        case '/type/boolean':
            $pdo_type = PDO::PARAM_BOOL;
            break;
        case '/type/content':
            $pdo_type = PDO::PARAM_LOB;
            break;
        case '/type/datetime':
        case '/type/text':
        case '/type/float': //this feels so wrong, but PDO doesn't seem t support any decimal/float type :(
            $pdo_type = PDO::PARAM_STR;
            break;
        case '/type/int':
            $pdo_type = PDO::PARAM_INT;
            break;
        case '/type/rawstring':
            $pdo_type = PDO::PARAM_STR;
            break;
    }
    return $pdo_type;
}

function add_parameter(&$where, &$params, $value, $pdo_type){
    $where .= ':'.($param_name = $this->get_p_name());
    $params[] = array(
        'name'  =>  $param_name
    ,   'value' =>  $value
    ,   'type'  =>  $pdo_type
    );
}

function add_parameter_for_property(&$where, &$params, $property){
    $property_value = $property['value'];
    $mql_type = $property['schema']['type'];
    $pdo_type = $this->map_mql_to_pdo_type($mql_type);
    if (is_array($property_value)) {
        $num_entries = count($property_value);
        for ($i=0; $i<$num_entries; $i++) {
            if ($i){
                $where .= ', ';
            }
            $this->add_parameter($where, $params, $property_value[$i], $pdo_type);
        }
    }
    else {
        $this->add_parameter($where, $params, $property_value, $pdo_type);
    }
}

function handle_filter_property(&$queries, $query_index, $t_alias, $column_name, $property){
    $query = &$queries[$query_index];
    $from = &$query['from'];
    $params = &$query['params'];
    $where = &$query['where'];

    $num_from_lines = count($from);
    if ($num_from_lines > 1){
        $from_line = &$from[$num_from_lines -  1];
        $from_or_where = &$from_line['join_condition'];
        $from_or_where .= "\n".'AND '.$t_alias.'.'.$column_name;

    }
    else {
        $from_or_where = &$query['where'];
        $from_or_where .= ($from_or_where? "\n".'AND' : 'WHERE')
            .' '.$t_alias.'.'.$column_name;
    }

    //prepare right hand side of the filter expression
    $add_closing_parenthesis = FALSE;
    $add_closing_escape_clause = FALSE;
    if ($operator = $property['operator']) {
        //If an operator is specified, 
        //the expression is used in the WHERE clause.
        switch ($operator) {
            case '~=':  //funky mql pattern matcher
                //not implemented yet. 
                //most likely it will be very hard 
                //to implement this in a rdmbs-independent way
                //let alone efficiency
                break;
            case '<': case '>': case '<=': case '>=': case '!=':
            case '=': //note that = is an extension. Silly it's not standard.
                $from_or_where .= ' '.$operator.' ';
                break;
            case '!|=':
                $from_or_where .= ' NOT';
            //fall through is intentional, keep the !|= and |= together please, in order.
            case '|=':
                $from_or_where .= ' IN (';
                $add_closing_parenthesis = TRUE;
                break;
            case '!?=': //extension. Ordinary database NOT LIKE
                $from_or_where .= ' NOT';
            //fall through is intentional, keep the !?= and ?= together please, in order.
            case '?=':  //extension. Ordinary database LIKE
                $from_or_where .= ' LIKE ';
                $add_closing_escape_clause = TRUE;
                break;
        }
    }
    else {
        //If no operator is specified, 
        //the comparison is automatically with equals.
        $from_or_where .= ' = ';
    }
    //prepare the right hand side of the comparison expression
    $this->add_parameter_for_property($from_or_where, $params, $property);
    if ($add_closing_parenthesis) {
        $from_or_where .= ')';
    }
    else
        if ($add_closing_escape_clause) {
            $from_or_where .= " ESCAPE '\\'";
        }

}

function handle_non_filter_property($t_alias, $column_name, &$select, &$property){
    $c_alias = $t_alias.$this->get_c_alias();
    $column_ref = $t_alias.'.'.$column_name;
    $select[$column_ref] = $c_alias;
    $property['alias'] = $c_alias;
}

function generate_sql(&$mql_node, &$queries, $query_index, $child_t_alias=NULL, &$merge_into=NULL){
    if (isset($mql_node['entries'])) {
        $this->generate_sql($mql_node['entries'], $queries, $query_index, $child_t_alias, $merge_into);
        return;
    }

    if (!isset($mql_node['query_index'])){
        $mql_node['query_index'] = $query_index;
    }

    $query = &$queries[$query_index];
    if (!$query){
        $query = array(
            'select'                =>  array()
        ,   'from'                  =>  array()
        ,   'where'                 =>  ''
        ,   'order_by'              =>  ''
        ,   'limit'                 =>  ''
        ,   'params'                =>  array()
        ,   'mql_node'              =>  &$mql_node
        ,   'indexes'               =>  array()
        ,   'merge_into'            =>  $merge_into
        ,   'results'               =>  array()
        );
        $queries[$query_index] = &$query;
    }
    $select = &$query['select'];
    $from   = &$query['from'];
    $where  = &$query['where'];
    $params = &$query['params'];
    $indexes = &$query['indexes'];

    $type = $this->analyze_type($mql_node['types'][0]);
    $domain_name = $type['domain'];
    $domains = $this->metadata['domains'];
    $schema_domain = $domains[$domain_name];
    $type_name = $type['type'];
    $schema_type = $schema_domain['types'][$type_name];

    //table_name is either explicitly specified, or we take the type name
    if (isset($schema_type['table_name'])){
        $table_name = $schema_type['table_name'];
    }
    else {
        $table_name = $type_name;
    }

    //schema_name is either explicitly specified, or we take the domain name
    if (isset($schema_type['schema_name'])) {   //schema_name is defined at the type level
        $schema_name = $schema_type['schema_name'];
    }
    else                                        //schema_name is defined at the domain level     
        if (isset($schema_domain['schema_name'])){
            $schema_name = $schema_domain['schema_name'];
        }
        else {                                      //schema_name not defined, settle for the domain name
            $schema_name = $domain_name;
        }

    $t_alias = $this->get_t_alias();

    $this->get_from_clause($mql_node, $t_alias, $child_t_alias, $schema_name, $table_name, $query);
    if (array_key_exists('properties', $mql_node)) {
        $properties = &$mql_node['properties'];
        foreach ($properties as $property_name => &$property) {

            if ($property['is_directive']) {
                switch ($property_name) {
                    case 'limit':
                        $limit = intval($property['value']);
                        if ($limit < 0) {
                            exit('Limit must not be less than zero.');
                        }
                        $query['limit'] = $limit;
                        break;
                }
            }
            else
                if (isset($mql_node['outer_join'])){
                    $property['outer_join'] = $mql_node['outer_join'];
                }

            $schema = $property['schema'];
            if (isset($schema['direction'])) {
                $direction = $schema['direction'];
                if ($direction === 'referenced<-referencing'){
                    $index_columns = array();
                    $index_columns_string = '';
                    foreach ($schema['join_condition'] as $columns) {
                        $column_ref = $t_alias.'.'.$columns['referenced_column'];
                        if (isset($select[$column_ref])) {
                            $c_alias = $select[$column_ref];
                        }
                        else {
                            $c_alias = $t_alias.$this->get_c_alias();
                            $select[$column_ref] = $c_alias;
                        }
                        $index_columns_string .= $c_alias;
                        $index_columns[] = $c_alias;
                    }
                    if (!isset($indexes[$index_columns_string])){
                        $indexes[$index_columns_string] = array(
                            'columns'   =>  $index_columns
                        ,   'entries'   =>  array()
                        );
                    }
                    $merge_into = array(
                        'query_index'   =>  $query_index
                    ,   'index'         =>  $index_columns_string
                    ,   'columns'       =>  array()
                    );
                    $new_query_index = count($queries);
                }
                else
                    if ($direction === 'referencing->referenced') {
                        $merge_into = NULL;
                        $new_query_index = $query_index;
                    }
                $property['query_index'] = $new_query_index;
                $this->generate_sql($property, $queries, $new_query_index, $t_alias, $merge_into);
            }
            else
                if ($column_name = $schema['column_name']){
                    if ($property['is_filter']) {
                        $this->handle_filter_property($queries, $query_index, $t_alias, $column_name, $property);
                    }
                    else {
                        $this->handle_non_filter_property($t_alias, $column_name, $select, $property);
                    }
                }
        }
    }
    else
        if (array_key_exists('default_property', $schema_type)) {
            $default_property_name = $schema_type['default_property'];
            $properties = $schema_type['properties'];
            if (!array_key_exists($default_property_name, $properties)) {
                exit('Default property "'.$default_property_name.'" specified but not found in "/'.$domain_name.'/'.$type_name.'"');
            }
            $default_property = $properties[$default_property_name];
            $column_name = $default_property['column_name'];
            $property = &$mql_node;
            $schema = &$property['schema'];
            $schema['type'] = $default_property['type'];
            if ($property['is_filter']) {
                $this->handle_filter_property($where, $params, $t_alias, $column_name, $property);
            }
            else {
                $this->handle_non_filter_property($t_alias, $column_name, $select, $property);
            }
        }
}

/*****************************************************************************
 *   Execute query / render result
 ******************************************************************************/
function map_mql_to_php_type($mql_type){
    switch ($mql_type){
        case '/type/boolean':
            $php_type = 'bool';
            break;
        case '/type/content':
        case '/type/datetime':
        case '/type/text':
        case '/type/rawstring':
            $php_type = 'string';
            break;
        case '/type/float':
            $php_type = 'float';
            break;
        case '/type/int':
            $php_type = 'integer';
            break;
        default:
            throw new Exception('No php type defined for mql type: '.$mql_type);
    }
    return $php_type;
}

var $statement_cache = array();

function prepare_sql_statement($statement_text){
    if (isset($this->statement_cache[$statement_text])){
        $statement_handle = $this->statement_cache[$statement_text];
    } else {
        $statement_handle = $this->pdo->prepare($statement_text);
        $this->statement_cache[$statement_text] = $statement_handle;
    }
    return $statement_handle;
}

function &execute_sql($statement_text, $params, $limit){



    if ($this->noexecute){
        return array();
    }
    try {
        $statement_handle = $this->prepare_sql_statement($statement_text);
        foreach($params as $param_key => $param){
            $statement_handle->bindValue(
                $param['name']
                ,   $param['value']
                ,   $param['type']
            );
        }
        $statement_handle->execute();
        if ($limit === -1) {
            $result = $statement_handle->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            $result = array();
            while ($limit-- && $row = $statement_handle->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }
        $statement_handle->closeCursor();
    } catch (Exception $exception) {



        throw new Exception(
            $exception->getMessage().
                ' Offending statement: '.$statement_text
        );
    }
    return $result;
}

function get_query_sql($query){

    $identifier_quote_start =$this->sql_dialect['identifier_quote_start'];
    $identifier_quote_end = $this->sql_dialect['identifier_quote_end'];

    $sql = 'SELECT';
    if ($select_columns = $query['select']) {
        foreach ($select_columns as $column_ref => $column_alias) {
            $sql .= ($sql==='SELECT'? '  ' : "\n, ").$column_ref.' AS '.$column_alias;
        }
    }
    else {
        $sql .= ' NULL';
    }
    $optionality_groups = array();
    foreach ($query['from'] as $index => $from_line) {
        if (isset($from_line['optionality_group'])) {
            $optionality_group_name = $from_line['optionality_group'];
            if (!array_key_exists ($optionality_group_name, $optionality_groups)) {
                $optionality_groups[$optionality_group_name] = array();
            }
            $optionality_group = &$optionality_groups[$optionality_group_name];
            $optionality_group[] = $from_line['optionality_group_column'];
        }
        $from_or_join = $index && (isset($from_line['join_type']));
        if ($from_or_join) {
            $sql .= "\n".$from_line['join_type'].' JOIN '
                .$from_line['table'].' '.$from_line['alias']
                ."\n".$from_line['join_condition']
            ;
        }
        else
            if (array_key_exists('table', $from_line)) {
                $sql .= "\nFROM ".$from_line['table'].' '.$from_line['alias'];
            }
            else
                if ($from_line['join_condition']){
                    //these are filter condition but we write them in the join
                    //this is required to handle outer joins.
                    $sql .= "\n".$from_line['join_condition'];
                }
    }
    $where = $query['where'];
    foreach ($optionality_groups as $k => $v) {
        $condition_null = '';
        $condition_not_null = '';
        foreach ($v as $optionality_group_column) {
            if ($condition_null !== '') {
                $condition_null.= ' AND ';
            }
            $condition_null .= $optionality_group_column.' IS NULL';
            if ($condition_not_null !== '') {
                $condition_not_null.= ' AND ';
            }
            $condition_not_null .= $optionality_group_column.' IS NOT NULL';
        }
        if ($where) {
            $where .= "\nAND";
        }
        else {
            $where .= "\nWHERE";
        }
        $where.= ' (('.$condition_null.') OR ('.$condition_not_null.'))';
    }

    $sql .= ($where? "\n".$where : '')
        .       ($query['order_by']? "\n".$query['order_by'] : '')
    ;
    //TODO: this implementation of limit is buggy!
    //It works fine if applied to a top-level mql node,
    //When used for a nested mql node, it does not take into 
    //account that the limit should be applied only to the nested node
    if ($query['limit']) {
        if ($this->sql_dialect['supports_limit']) {
            $sql .= "\nLIMIT ".$query['limit'];
        }
    }
    return $sql;
}

function execute_sql_query(&$sql_query){
    if ($sql_query['limit'] && !$this->sql_dialect['supports_limit']) {
        //TODO: this implementation of limit is buggy!
        //It works fine if applied to a top-level mql node,
        //When used for a nested mql node, it does not take into 
        //account that the limit should be applied only to the nested node
        $limit = $sql_query['limit'];
    }
    else {
        //limit has been implemented directly in SQL
        $limit = -1;
    }


    $sql = $this->get_query_sql($sql_query);
    $sql_query['sql'] = $sql;

    return $this->execute_sql($sql, $sql_query['params'], $limit);
}

function get_result_object(&$mql_node, $query_index, &$result_object=NULL, $key=NULL){
    if($mql_node['query_index']!==$query_index){
        return;
    }
    $object = array();

    if (is_array($result_object)) {
        $result_object[$key] = &$object;
    }
    else {
        $result_object = &$object;
    }

    if (isset($mql_node['entries'])) {
        $this->get_result_object($mql_node['entries'], $query_index, $object, 0);
    }
    else
        if (isset($mql_node['properties'])) {
            foreach ($mql_node['properties'] as $property_key => $property) {
                if ($property['operator'] || $property['is_directive']) {
                    continue;
                }
                $value = $property['value'];
                if (is_object($value) || is_array($value)){
                    $this->get_result_object($property, $query_index, $object, $property_key);
                }
                else {
                    $object[$property_key] = $value;
                }
            }
        }
    $mql_node['result_object'] = $object;
    return $object;
}

function fill_result_object(&$mql_node, $query_index, $data, &$result_object){
    if($mql_node['query_index']!==$query_index){
        return;
    }

    if ($entries = &$mql_node['entries']) {
        $this->fill_result_object($entries, $query_index, $data, $result_object[0]);
    }
    else
        if ($properties = &$mql_node['properties']) {
            foreach ($result_object as $key => $value) {
                $property = $properties[$key];
                if (is_object($value) || is_array($value)){
                    $this->fill_result_object($property, $query_index, $data, $result_object[$key]);
                }
                else
                    if (isset($property['alias'])) {
                        $alias = $property['alias'];
                        if ($this->explicit_type_conversion) {
                            if (!is_null($data[$alias])) {
                                settype($data[$alias], $this->map_mql_to_php_type($property['schema']['type']));
                            }
                        }
                        $result_object[$key] = $data[$alias];
                    }
            }
        }

}

function add_entry_to_indexes(&$indexes, $row_index, &$row) {
    foreach($indexes as $index_name => &$index) {
        $entries = &$index['entries'];
        $cols = $index['columns'];
        $colcount = count($cols) - 1;
        for ($i=0; $i<$colcount; $i++){
            $col = $cols[$i];
            $sub_entries = &$entries[$row[$col]];
            if (!$sub_entries) {
                $sub_entries = array();
                $entries[$row[$col]] = &$sub_entries;
            }
            $entries = &$sub_entries;
        }
        $entries[$row[$cols[$i]]] = $row_index;
    }

}

function &get_entry_from_index(&$query, $index_name, $key){
    $index = $query['indexes'][$index_name]['entries'];
    foreach ($key as $k) {
        $index = $index[$k];
    }
    $results = &$query['results'];
    return $results[$index];
}

function merge_result_object(&$mql_node, &$result_object, $query_index, &$data, $from, $to){
    if (isset($mql_node['entries'])) {
        $this->merge_result_object($mql_node['entries'], $result_object[0], $query_index, $data, $from, $to);
    }
    else
        if (isset($mql_node['properties'])) {
            $properties = $mql_node['properties'];
            foreach ($properties as $property_key => $property) {
                if ($property['operator']) {
                    continue;
                }
                if (isset($property['query_index']) && ($property['query_index']===$query_index)) {
                    $result_object[$property_key] = array();
                    $target = &$result_object[$property_key];
                    for ($i=$from; $i<=$to; $i++){
                        $target[] = &$data[$i];
                    }
                }
                else {
                    $value = $property['value'];
                    if (is_object($value) || is_array($value)){
                        $this->merge_result_object($property, $result_object[$property_key], $query_index, $data, $from, $to);
                    }
                }
            }
        }
}

function merge_results(&$queries, $query_index, $key, $from, $to){
    if ($from===-1){
        return;
    }
    $query = &$queries[$query_index];
    $merge_into = $query['merge_into'];
    $target_query_index = $merge_into['query_index'];
    $target_query = &$queries[$target_query_index];
    $index_name = $merge_into['index'];
    $merge_target = &$this->get_entry_from_index($target_query, $index_name, $key);
    $this->merge_result_object($target_query['mql_node'], $merge_target, $query_index, $query['results'], $from, $to);
}

function create_inline_table_for_index_entry(&$entries, $columns, $column_index, &$statement, &$row){
    global $pdo, $sql_dialect;
    $single_row_from_clause = $this->sql_dialect['single_row_from_clause'];
    foreach ($entries as $key => $value) {
        $row    .=  ($row === ''? 'SELECT ' : ', ')
            .   (is_string($key)? $this->pdo->quote($key) : $key)
            .   ($statement === '' ? ' AS '.$columns[$column_index] : '')
        ;

        if (is_array($value)){
            $this->create_inline_table_for_index_entry($value, $columns, $column_index+1, $statement, $row);
        }
        else
            if (is_int($value)) {
                $statement .= ($statement==='' ? '' : "\nUNION ALL\n").$row.$single_row_from_clause;
                $row = '';
            }
    }
}

function create_inline_table_for_index(&$index){
    $statement = '';
    $row = '';
    $this->create_inline_table_for_index_entry($index['entries'], $index['columns'], 0, $statement, $row);
    $statement = "(\n".$statement."\n)";
    $index['inline_table'] = $statement;
}

function create_inline_tables_for_indexes(&$indexes){
    foreach ($indexes as &$index) {
        $this->create_inline_table_for_index($index);
    }
}

/**
 *   execute_sql_queries(&$sql_queries)
 *   Executes multiple SQL queries
 *
 *   arguments:
 *   sql_queries:    an arrra
 *
 *   return:
 */
function execute_sql_queries(&$sql_queries) {
    foreach($sql_queries as $sql_query_index => &$sql_query){

        $indexes = &$sql_query['indexes'];

        $mql_node = $sql_query['mql_node'];
        $this->get_result_object($mql_node, $sql_query_index);
        $result_object = $mql_node['result_object'];

        if ($merge_into = $sql_query['merge_into']) {

            $merge_into_columns = $merge_into['columns'];
            $select_columns = $sql_query['select'];
            $merge_into_values_new = array();
            $merge_into_values_old = array();
            $offset = -1;

            $index_name = $merge_into['index'];
            $index = $sql_queries[$merge_into['query_index']]['indexes'][$index_name];
            $index_columns = $index['columns'];
            $extra_from_line = array(
                'table' => $index['inline_table']
            ,   'alias' => $index_name
            );
            $join_condition = '';


                $join_condition .= ($join_condition==='' ? 'ON' : "\nAND").' '
                    .   $index_name.'.'.$index_column.' = '
                    .   array_search($merge_into_columns[$position], $select_columns, TRUE)
                ;
            }
            $from = &$sql_query['from'];
            //php guru's, isn't the a func to get the first element of an array?
            foreach ($from as &$first_from_line) { break; }
            $first_from_line['join_condition'] = $join_condition;
            $first_from_line['join_type'] = 'INNER';
            array_unshift($from, $extra_from_line);
        }

        $result = &$sql_query['results'];
        $rows = $this->execute_sql_query($sql_query);

        foreach($rows as $row_index => $row){
            if ($merge_into){
                foreach ($merge_into_columns as $col_index => $alias){
                    $merge_into_values_new[$col_index] = $row[$alias];
                }
                if ($merge_into_values_new !== $merge_into_values_old){
                    $this->merge_results($sql_queries, $sql_query_index, $merge_into_values_old, $offset, $row_index);
                    $offset = $row_index;
                }
                $merge_into_values_old = $merge_into_values_new;
            }
            $this->fill_result_object($mql_node, $sql_query_index, $row, $result_object);
            $result[$row_index] = $result_object;
            $this->add_entry_to_indexes($indexes, $row_index, $row);

        }
        $this->create_inline_tables_for_indexes($indexes);
        if (isset($merge_into_values_old) && count($merge_into_values_old)) {
            $this->merge_results($sql_queries, $sql_query_index, $merge_into_values_old, $offset, $row_index);

        }

    }

}
/*****************************************************************************
 *   Handle request
 ******************************************************************************/

/**
 *   handle_query($mql_query)
 *   Executes a single MQL query.
 *
 *   arguments:
 *   $mql_query: a mql query object (decoded from JSON)
 *
 *   return:     a result envelope (as associative PHP array)
 */
function handle_query($mql_query_envelop, $query_key=0){

    $this->callstack_push('begin query #'.$query_key);
    //check if the query parameter is valid MQL query envelope
    if (!property_exists($mql_query_envelop, 'query')) {
        exit('MQL query envelope must have a query attribute. ' . $mql_query_envelop);
    }


    $mql_query = $mql_query_envelop->query;



    //enforce permissions on user
    list($e, $d, $t) = split('/',$mql_query->type);
    $x = $this->get_type_from_schema($d, $t);

    if ($x["permissions"]!="*" && $x["permissions"]!="user") {
        exit("You do not have permission to access this object.");
    } else if ($x["permissions"]=="user") {
        $mql_query->user_id = $this->userId;
    }

    $tree = array();
    $this->reset_ids();
    $this->process_mql($mql_query, $tree);

    $this->generate_sql($tree, $sql_queries, 0);


    $this->execute_sql_queries($sql_queries);


    $result = &$sql_queries[0]['results'];




    /*
        $topObj = split('/', $result[0]["type"]);
        $topObj = $topObj[2];
        $i = 0 ;
        while($topObj!=$oType && $i<1000) {
            foreach ($result[0] as $key => $value) {
                if ($key!="user_id" && $key!="type") {
                    $result[0] = $value[0];
                    $topObj = $key;
                    break;
                }
                $i++;
            }
        }

        $result[0] = array_reverse($result[0], true);
        $result[0]["type"] = "/$d/$oType";
        $result[0] = array_reverse($result[0], true);
    */


    //get the sql statements out for debugging purposes
    $sql_statements = array();
    $return_value = array(
        'code'      =>  '/api/status/ok'
    ,   'result'    =>  $result
    );
    if ($this->debug_info) {
        foreach ($sql_queries as $sql_query_index => $sql_query) {
            $sql_statements[] = array(
                'statement' =>  $sql_query['sql']
            ,   'params'    =>  $sql_query['params']
            );
        }
        $return_value['sql'] = $sql_statements;
        $this->callstack_push('end query #'.$query_key);
        $return_value['timing'] = $this->callstack;
    }
    return $return_value;
}

/**
 *   handle_queries($queries)
 *   Executes multiple MQL queries.
 *
 *   arguments:
 *   queries:    an associative array of mql query objects (decoded from JSON)
 *
 *   return:     an associative array of result envelopes
 */
function handle_queries($queries){
    $queries = get_object_vars($queries);
    $results = array(
    );
    foreach ($queries as $query_key => $query){
        $result = $this->handle_query($query, $query_key);
        $results[$query_key] = $result;
    }
    return $results;
}

/*****************************************************************************
 *   Schema
 ******************************************************************************/
//$metadata_file_name is defined in config.php
var $metadata = NULL;

function init_metadata($schema){

    if (!$this->metadata = json_decode($schema, TRUE)) {
        exit('schema is not valid json ('.$this->get_last_json_error().').');
    }
}
/*****************************************************************************
 *   Database (PDO)
 ******************************************************************************/
//$connection_file_name is defined in config.php
var $pdo = NULL;
var $explicit_type_conversion = NULL;
var $userId;
var $sql_dialect  = array(
'identifier_quote_start' => '`',
'identifier_quote_end' => '`',
'single_row_from_clause' => '',
'supports_limit' => TRUE
);

function init_pdo($dsn, $user, $pass){

    $this->pdo = new PDO(
        $dsn
        ,   $user
        ,   $pass
        ,   array()
    );
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, FALSE);
    $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
    $this->explicit_type_conversion = true;
}

/*****************************************************************************
 *   Queries
 ******************************************************************************/
var $query_or_queries = NULL;
var $query = NULL;
var $queries = NULL;
var $debug_info = NULL;
var $query_decode = NULL;

function init_queries($query){

    //check if the query parameter is present
    if (!isset($query)) {
        exit('Query must be specified');
    }

    $this->query = $query;

    //immunize against magic quoting
    if (get_magic_quotes_gpc() === 1) {
        $this->query = stripslashes($this->query);
    }






    //check if the query parameter is valid JSON
    $this->query_decode = json_decode($this->query);
    if ($this->query_decode===NULL) {
        exit('query or queries not valid JSON ('.$this->get_last_json_error().')');
    }

    //testing if the envelope is an object (not some other random JSON value)
    if (!is_object($this->query_decode)) {
        exit('Envelope must be an object');
    }

    //replace * with properties...



    $this->debug_info = property_exists($this->query_decode,'debug_info')? $this->query_decode->debug_info : FALSE;
    $this->noexecute = property_exists($this->query_decode,'noexecute')? $this->query_decode->noexecute : FALSE;
}

/**
 *
 */
function handle_request(){

    if (isset($queries)) {
        $result = $this->handle_queries($this->query_decode);
    }
    else {
        $result = $this->handle_query($this->query_decode);
    }

    return json_encode($result["result"]);
}
/*****************************************************************************
 *   run
 ******************************************************************************/
function run($userId, $query, $schema, $dsn, $user, $pass) {
    $this->userId = $userId;
    $this->init_metadata($schema);
    $this->init_pdo($dsn, $user, $pass);
    $this->init_queries($query, null);
    return $this->handle_request();
}

    function runFromTable($userId, $table, $schema, $dsn, $user, $pass) {
        $this->userId = $userId;
        $this->init_metadata($schema);
        $this->init_pdo($dsn, $user, $pass);

        $md = $this->metadata["domains"]["cti_corporate_engagement"]["types"];
        $props = $md[$table]["properties"];
        $query = '{"query":{"type":"/cti_corporate_engagement/' . $table . '",';

        $query = $this->getPropsFromSchema($query, $props, $md, 0);
        $query .= "}}";
        $query = str_replace(',}','}', $query );
        exit( $query );
        $this->init_queries($query);

        return $this->handle_request();
    }

    function getPropsFromSchema($q, $props, $md, $recursiveIndex) {
        $recursiveLimit = 1;
        foreach($props as $key=>$val) {
            if (isset($val["column_name"])) {
                $q .= '"' . $key . '":null,';
            } else if ($recursiveIndex < $recursiveLimit) {
                $q .= '"' . $key . '":[{';
                $typ = split('/', $val["type"]);
                $typ = $typ[2];
                $newprops = $md[$typ]["properties"];
                $q = $this->getPropsFromSchema($q, $newprops, $md, $recursiveIndex+1);
                $q .= '}],';
            }
        }
        return $q;
    }

}