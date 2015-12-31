<?php
/* Database class.

This class was written by Ezriilc Swifthawk.
Anyone is free to use and/or modify this code, with proper credit given as above.

Sample code:

include_once('class/Database');
$db = new Database;
$db_err = $db->get_error();
if( ! empty($db_err) ){
    die(get_called_class().': Can\'t open DB.');
}
$table = '';
$read_data = $db->read($table,'*',array('id'=>$id));
$db_err = $db->get_error();
if(
    ! empty($read_data)
    AND empty($db_err)
){
    $write_data = array(
        array( // Fields
            ''
        ),
        array( // Values
            
        ),
    );
    $row_count = $db->write($table,$write_data,true);
    $db_err = $db->get_error();
    if(
        ! empty($row_count)
        AND empty($db_err)
    ){
        
    }else{
        // Write failure.
    }
}else{
    // Read failure.
}

*/
CLASS Database{
    
    static $default_db_file = './_sqlite/Kerbaltek.sqlite3';
    
    function __construct($db_file=''){
		if( empty($db_file) ){
            $this->file = static::$default_db_file;
        }else{
            $this->file = $db_file;
        }
        $this->init($this->file);
    }
    
    function get_error(){
        if( ! empty($this->error) ){
            $error = get_called_class().': '.$this->error;
        }else{$error = '';}
		$this->error = '';
        return $error;
    }
    
	private function init($db_file){
		$this->error = '';
		if(
            ! @is_writable($db_file)
            OR ! @is_writable(dirname($db_file))
        ){
            sleep(5);
            if(
                ! @is_writable($db_file)
                OR ! @is_writable(dirname($db_file))
            ){
                $this->error .= 'Missing/un-writable DB file/path: '.$db_file.'';
                return;
            }
        }
        try{
            $this->db_cnnx = new PDO('sqlite:'.$db_file);
        }catch( PDOException $Exception ){
            $this->db_cnnx = null;
			$this->error .= 'PDOException.';
			return;
        }
	}
    
    function remove($table='',$where=array()){
		$this->error = '';
        $where_string = '';
        if( empty($table) ){
			$this->error .= 'Remove: No table name given.';
			return;
		}
        if( empty($where) ){
            $where_string .= "";
        }else{
            $where_string .= "
WHERE";
            $i=1;
            foreach( $where as $prop => $val ){
                if( ! is_integer($val) ){
                    $val = "'".$val."'";
                }
                $where_string .= " ".$prop."=".$val;
                if( count($where)-$i ){
                    $where_string .= " AND";
                }
                $i++;
            }
        }
        $stmt_string = "
DELETE FROM ".$table.$where_string."
";
		if(
			$stmt = $this->db_cnnx->prepare($stmt_string)
            AND $stmt->execute()
            AND $count = $stmt->rowCount()
		){
			return $count;
		}else{
            $this->error .= 'Remove from '.$table.' failed: bad query.';
            return;
		}
    }
    
    function read($table='',$fields='',$where=array()){
		$this->error = '';
        if( empty($table) ){
			$this->error .= 'No table given.';
			return;
		}
		if( empty($fields) ){ $fields = "*"; }
        
        // Read columns info.
        if(
            ! $stmt = $this->db_cnnx->prepare("
PRAGMA table_info(".$table.");
")
            OR ! $stmt->execute()
        ){
            $this->error .= 'Write to '.$table.' failed: can\'t read columns.';
            return;
        }
        if( ! $cols = $stmt->fetchall(PDO::FETCH_ASSOC) ){
            $this->error .= 'Write to '.$table.' failed: table has NO columns.';
            return;
        }
        $cols_temp = array();
        foreach( $cols as $col ){
            $cols_temp[$col['name']] = $col;
        }
        $cols = $cols_temp;
        
        // Escape all non-bindable data.
        $table = SQLite3::escapeString($table);
        $fields = explode(',',$fields);
        foreach( $fields as & $field ){
            if( $field === '*' ){continue;}
            $field = preg_replace('/\W/i','_',$field);
            $field = SQLite3::escapeString($field);
        }unset($field);
        $fields = implode(',',$fields);
        
		$where_string = "";
		if( ! empty($where) AND is_array($where) ){
			$where_string .= "
WHERE";
			$i=1;
            $where_temp = array();
			foreach( $where as $prop => $val ){
                $prop = preg_replace('/\W/i','_',$prop);
				$prop = SQLite3::escapeString($prop);
                $where_temp[$prop] = $val;
                $where_string .= " ".$prop."=:bind_".$prop;
				if( count($where)-$i ){
					$where_string .= " AND";
				}
				$i++;
			}
            $where = $where_temp;
		}
        $stmt_string = "
SELECT ".$fields." FROM '".$table."'".$where_string."
";
        if( ! $stmt = $this->db_cnnx->prepare($stmt_string) ){
			$this->error .= 'Read from '.$table.' failed: Can\'t prepare statement.';
            return;
        }
        
        if( ! empty($where) ){
            foreach( $where as $prop => $val ){
                $col_type = $cols[$prop]['type'];
                switch( $col_type ){
                    case 'INTEGER':
                        if( ! is_numeric($val) ){
                            $this->error .= 'Read from '.$table.' WHERE failed: Datatype mismatch.';
                            return;
                        }
                        $val += 0;
                        $param_type = PDO::PARAM_INT;
                    break;
                    case 'BLOB':
                        $param_type = PDO::PARAM_LOB;
                    break;
                    case 'REAL':
                    case 'TEXT':
                        $val .= '';
                        $param_type = PDO::PARAM_STR;
                    break;
                    default:
                        $this->error .= 'Write to '.$table.' WHERE failed: Strange DB datatype.';
                        return;
                }
                if( ! $stmt->bindValue(':bind_'.$prop, $val, $param_type) ){
                    $this->error .= 'Read from '.$table.' WHERE failed: Can\'t bind value.';
                    return;
                }
            }
        }
        
        if( ! $stmt->execute() ){
			$this->error .= 'Read from '.$table.' failed: Can\'t execute().';
            return;
		}
        return $stmt->fetchall(PDO::FETCH_ASSOC);
    }
    
    function write($table='',$data=array(),$overwrite=false){
        // $data: 2-dimension - 1st element is $fields, rest are $values.
        // $fields and values must be in proper order!  Need to check for this.
        
		$this->error = '';
        
        if( empty($table) OR empty($data) ){
			$this->error .= 'Write to table:"'.$table.'" failed: table/data missing.';
			return;
		}
        if(
            ! is_array($data)
            OR count($data) < 2
        ){
            $this->error .= 'Write to '.$table.' failed: Bad data format.';
            return;
        }
        
		foreach( $data as $val ){
			if(
                empty($data[0])
                OR count($val) != count($data[0])
            ){
                $this->error .= 'Write to '.$table.' failed: field/value count mismatch.';
                return;
			}
		}unset($val);
        
        $fields = array_shift($data);
        $values = $data;
        
        // Escape all incoming data.
        $table = SQLite3::escapeString($table);
        foreach( $fields as & $field ){
            $field = preg_replace('/\W/i','_',$field);
            $field = SQLite3::escapeString($field);
        }unset($field);
        
        // Read columns info.
        if(
            $stmt = $this->db_cnnx->prepare("
PRAGMA table_info(".$table.");
")
            AND $stmt->execute()
        ){
            if( ! $cols = $stmt->fetchall(PDO::FETCH_ASSOC) ){
                $this->error .= 'Write to '.$table.' failed: table has NO columns.';
                return;
            }
            $cols_temp = array();
            foreach( $cols as $col ){
                $cols_temp[$col['name']] = $col;
            }
            $cols = $cols_temp;
        }else{
            $this->error .= 'Write to '.$table.' failed: can\'t read columns.';
            return;
        }
        
        if( empty($fields) ){
            $fields = array();
            foreach( $cols as $col ){ $fields[] = $col['name']; }
        }
        $cols_temp = array();
        foreach( $cols as $col ){
            if( ! in_array($col['name'],$fields) ){continue;}
            $cols_temp[] = $col;
        }
        $cols = $cols_temp;
        
        // Setup statement string.
        $command = "
INSERT";
		$command .= empty($overwrite) ? "" : " OR REPLACE";
        
        $stmt_string = "";
        $vals_string = "";
        
        $stmt_string .= $command." INTO ".$table." (";
        $binds = array();
        
        $i = 1;
        foreach( $cols as $col ){
            $stmt_string .= $col['name'];
            $vals_string .= ":bind_".$col['name'];
            $binds[$col['name']] = $col['type'];
            if( $i < count($cols) ){
                $stmt_string .= ",";
                $vals_string .= ",";
            }
            $i++;
        }
        $stmt_string .= ")
VALUES (".$vals_string.")
;";
        $row_count = 0;
        foreach( $values as $vals ){
            $vals = array_values($vals);
            if( ! $stmt = $this->db_cnnx->prepare($stmt_string) ){
                $this->error .= 'Write to '.$table.' failed: can\'t prepare statement.';
                return;
            }
            
            foreach( $fields as $field_key => $field ){
                $gettype = gettype($vals[$field_key]);
                if(
                    $vals[$field_key] === ''
                    OR $gettype == NULL
                    OR preg_match('/^null$/i',$vals[$field_key])
                ){
                    $vals[$field_key] = NULL;
                    $param_type = PDO::PARAM_NULL;
                }else{
                    switch( $binds[$field] ){
                        case 'INTEGER':
                            if( ! is_numeric($vals[$field_key]) ){
                                $this->error .= 'Write to '.$table.' failed: Datatype mismatch.';
                                return;
                            }
                            $vals[$field_key] += 0;
                            $param_type = PDO::PARAM_INT;
                        break;
                        case 'BLOB':
                            $param_type = PDO::PARAM_LOB;
                        break;
                        case 'REAL':
                        case 'TEXT':
                            $vals[$field_key] .= '';
                            $param_type = PDO::PARAM_STR;
                        break;
                        default:
                            $this->error .= 'Write to '.$table.' failed: Strange DB datatype.';
                            return;
                    }
                }
                
                if( ! $stmt->bindValue(':bind_'.$field, $vals[$field_key], $param_type) ){
                    $this->error .= 'Write to '.$table.' failed: can\'t bind values.';
                    return;
                }
            }
            $this_row_count = 0;
            if(
                ! $stmt->execute()
                OR ! $this_row_count = $stmt->rowCount()
            ){
                $this->error .= 'Write to '.$table.' failed: can\'t save.';
                return;
            }
            $row_count += $this_row_count;
        }
        return $row_count;
    // END write().
    }
	
    function check_add_tables($db_tables=array()){
		$this->error = '';
        $output = true;
        foreach( $db_tables as $name => $table ){
            if(
                $stmt = $this->db_cnnx->prepare("
SELECT name FROM sqlite_master
WHERE type='table' AND name='".$name."';
")
                AND $stmt->execute()
                AND $row = $stmt->fetch(PDO::FETCH_ASSOC)
            ){ continue; } // Table already exists.
            
            // Table does NOT exist.
            $stmt_string = "
CREATE TABLE ".$name."(".$table['schema'].");
";
            if(
                $stmt = $this->db_cnnx->prepare($stmt_string)
                AND $stmt->execute()                
            ){
                $output .= $name.' table created.';
                if( ! empty($table['fields']) AND ! empty($table['values']) ){
                    $data = array_merge(array($table['fields']),$table['values']);
                    if( $count = $this->write($name,$data) ){
                        $output .= $count.' '.$name.' rows added.';
                    }else{
                        $this->error .= $name.' records creation failed. Reversing operation...';
                        if(
                            $stmt = $this->db_cnnx->prepare("
DROP TABLE ".$name.";
")
                            AND $stmt->execute()                
                        ){
                            $this->error .= $name.' table dropped.';
                        }else{
                            $this->error .= $name.' TABLE DROP FAILED.';
                        }
                        return;
                    }
                }
            }else{
                $this->error .= $name.' table creation failed.';
//var_dump($stmt_string);
				return;
            }
        }
		return $output;
        // END of init().
    }
	
    // END of CLASS
}
?>