<?php   
/*
// +---------------------------------------------------------------------------+	
// | PHP 5 MySQLi database class extension - 2010							   |
// | A perfect (IMO) MySQLi extension which I wrote a few years back that 	   |
// | extends the PHP 5 MySQLi object   								 		   |
// | Version 1.1															   |
// | twitter.com/andymeek													   |
// | github.com/andymeek													   |
// | andymeek.com / uandi-digital.com   			 						   |																				 |
// +---------------------------------------------------------------------------+
*/
if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
    die('No MySQLi');
}
class MySQLiExtension extends MySQLi {
	
	/*
   * Construct function
   * Uses constants defined in config.php
   */
   
  final function __construct() {
		parent::__construct(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
		if(mysqli_connect_errno()) {
			$this->dbError("Can't connect to MySQL Server : ".mysqli_connect_error());
		}
  }
	
	/* 
	* Object Array Query
	* Usage : returns object array
	* @ Param sql	SQL statement
	*/
	
	 public function doQuery($sql) {
     	$a = array();
	   	$q = parent::query($sql);
	    if ($q->num_rows > 0) {
	    	while ($r = $q->fetch_object()){
		    	$a[] = $r;  
	    	}
	  	}
	  	return $a;
    }
	
	/* 
	* Assoc Array Query
	* Usage : returns assoc array
	* @ Param sql	SQL statement
	* 
	*/
	
	 public function doQueryAssoc($sql) {
     	$a = array();
	   	$q = parent::query($sql);
			if(!is_object($q)) return;
	    if ($q->num_rows > 0) {
	    	while ($r = $q->fetch_assoc()){
		    	$a[] = $r;  
	    	}
	  	}
	  	return $a;
    }
		
	/* 
	* Fetch Row
	* Usage : returns MySQL Row
	* @ Param sql	SQL statement
	*/
	
	 public function fetchRow($sql) {
     	$a = array();
	   	$q = parent::query($sql);
	    if ($q->num_rows > 0) {
		    $a = $q->fetch_object();  
	  	}
	  	return $a;
    }
	
	/* 
	:: 	INSERT query
		
	:: 	You must use an assoc array for this to work.  The field names and must be correct and inserted before the field data.
	:: 	For example:
		
			$arr = array (
				"first_name" => "Andy",
				"last_name" => "Meek"
			);

			$ins = $db->insert("the_table", $arr);
		
	:: 	Also use this for debugging - the final param needs to be set to 1:
		
			$ins = $db->insert("the_table", $arr ,1);
		
	*/
	
	public function insert($the_table,$array_data,$debug=0) {
		$show_columns = "SHOW COLUMNS FROM $the_table";
		$col_array = $this->doQueryAssoc($show_columns);
		//print_r($col_array);
		$the_query = "INSERT INTO $the_table (";
		$insert_col = "";
		$insert_val = "";
		for($i=0; $i< count($col_array); $i++ ){
			$col = $col_array[$i];
			$field =$col["Field"];
			$type = $col["Type"];
			//echo "on field $field of type $type <br />";
			if(array_key_exists($field, $array_data)){
				//echo "Getting insert value for $field, ".$array_data[$field]."<br />";
				$insert_col .= $field. ", ";
				$insert_val .=  "'".$this->makeSafe($array_data[$field]). "', ";
				//echo "insertcol: $insert_col AND insert val $insert_val<br />";
			}
		}
		if(!$insert_col) {
			$this->dbError("INSERT ERROR");
			return false;
		}
		//Remove the last space & comma
		$insert_col = substr($insert_col, 0,strlen($insert_col)-2);
		$insert_val = substr($insert_val, 0,strlen($insert_val)-2);
		$the_query = $the_query.$insert_col.") VALUES ( ".$insert_val." )";
		//echo $the_query;
		if ($debug == 1) {
			echo $the_query; 
		}
		$result = parent::query($the_query);
		return true;
	}
	
	/*
	* UPDATE query	
	* Usage : 
		
			$arr = array (
				"first_name" => "Napolean",
				"last_name" => "Dynamite"
			);

			$up= $db->update("first_last", " WHERE  id = '1'", $arr);
		
	* Also, use this for debugging - the final param needs to be set to 1:
		
			$up= $db->update("first_last", " WHERE  id = '1'", $arr, 1);
	*/
	
	public function update($the_table, $where="", $array_data, $debug=0) {
		$show_columns = "SHOW COLUMNS FROM $the_table";
		$col_array = $this->doQueryAssoc($show_columns);
		//echo "<pre>";
		//var_dump($col_array);
		//echo "</pre>";
		$the_query = "UPDATE $the_table SET ";
		$insert_val = "";
		for($i=0; $i< count($col_array); $i++ ){
			$col = $col_array[$i];
			$field =$col["Field"];
			$type = $col["Type"];
			if(array_key_exists($field, $array_data)){
				$insert_val .=  $field. " = '".$this->makeSafe($array_data[$field]). "', ";
			}
		}
		if(!$insert_val) {
			$this->dbError("UPDATE ERROR");
			return false;
		}
		//Remove the last space & comma
		$insert_val = substr($insert_val, 0,strlen($insert_val)-2);
		$the_query = $the_query."  ".$insert_val."  $where";
		if ($debug == 1) {
			echo $the_query; 
		}
		$result = parent::query($the_query);
		return true;
	}
	/* 
	* Save to DB
	* @Param        $the_table (String)
	* @Param        $array_data (Array)
	* @Param    		$index_field (String) - Optional
	* @Param    		$index_val (Int) - Optional
	* @Param    		$array_data (Int) - Optional
	* Return    		true (Bool)
	
	* Usage :
		$save = $db->save('admins', $array_data=array('first_name' => 'ANDY', 'last_name' => 'MEEK'), $index_field='admin_id', $index_id=7);
	*/           
	public function save($the_table, $array_data=array(), $index_field="", $index_val="", $debug=0){
		$show_columns = "SHOW COLUMNS FROM $the_table";
		$col_array = $this->doQueryAssoc($show_columns);
		$the_query = "INSERT INTO $the_table (";
		$insert_col = "";
		$insert_val = "";
		$update_val = "";
		if ($index_field != "" && $index_val != ""){
			$insert_col.= $index_field. ", ";
		}
		for($i=0; $i< count($col_array); $i++ ){
			$col = $col_array[$i];
			$field =$col["Field"];
			$type = $col["Type"];
			if(array_key_exists($field, $array_data)){
				$insert_col .= $field. ", ";
				$insert_val .=  "'".$this->makeSafe($array_data[$field]). "', ";
				$update_val .=  $field. " = '".$this->makeSafe($array_data[$field]). "', ";
			}
		}
		//Remove the last space & comma
		$insert_col = substr($insert_col, 0,strlen($insert_col)-2);
		$print_index = ($index_field != "" && $index_val != "") ? $index_val.', ' : '';
		$the_query = $the_query.$insert_col.") VALUES (".$print_index.substr($insert_val, 0,strlen($insert_val)-2)." )";
		if ($index_field != "" && $index_val != ""){
			$update_val = substr($update_val, 0,strlen($update_val)-2);
			$the_query.= " ON DUPLICATE KEY UPDATE ".$index_field. " = ".$index_val.", ".$update_val;
		}
		if ($debug == 1) {
			echo $the_query; 
		}              
		$result = parent::query($the_query);
		if($result) return true;
	}
	
	/*
	* Show Table fields
	* @param           		$table name (String)
	* Return              $all_fields (Array)
	*/
	
	public function showFields($table_name) {
		$result = $this->doQueryAssoc("SHOW FIELDS FROM ".$table_name);
		foreach ($result as $row) {
			$ftype  = $row['Type'];
			$fname  = $row['Field'];
			$fnull   = $row['Null'];
			$fkey = $row['Key'];
			$fdefault = @$row['Default'];
			$fextra = $row['Extra'];
			$all_fields[] = array("name"=>$fname,"type"=>$ftype,"null"=>$fnull,"key"=>$fkey,"default"=>$fdefault,"extra"=>$fextra);
		}                                              
		return $all_fields;
	}
	
	/*
	* Return INDEX table information
	* @param : table name
	*/
	
	public function showIndex($table_name) {
		$result = $this->doQueryAssoc("SHOW INDEX FROM ".$table_name);
		foreach ($result as $row) {
			$fkeyname  = $row['Key_name'];
			$fseq  = $row['Seq_in_index'];
			$fcolumn   = $row['Column_name'];
			$fnonunique   = $row['Non_unique'];
			$all_fields[] = array("key"=>$fkeyname,"seq"=>$fseq,"column"=>$fcolumn,"nonunique"=>$fnonunique);
		}                                              
		return $all_fields;
	}
	/*
	* Make Safe
	* @param : val (string)
	* This should always be called before inserting / updating strings into any field	
	*/
	public function makeSafe($text_field){	
		$text_field = parent::real_escape_string(htmlentities($text_field));
		return $text_field;
	}
	
	/*
	* Return SQL 'NOW()' in datetime field type format
	*/
	
	public function dateTimeNow() {
		return date("Y-m-d H:i:s");
	}
	
	/*
	* DB Error
	* prints last error string
	* @param : error	 string
	* @param : sql (optional)	 			string
	*/
	
	private function dbError($error, $sql=""){
		echo($error);
		if($sql != ""){ 
			echo "---------------------------------------------------------------------<br />";
			echo "$sql<br />";
			echo "---------------------------------------------------------------------<br />";
		}
		die();
	}
}

// Create the DB instance
$db = new MySQLiExtension();
?>
