<?php

namespace DBE;

class DBE
{
	var $persist=false; //make it true for persistant connection.
	var $database="";   //variable for databse
	var $tablePrefix="";
	var $connectionId;
	var $result=false;
	var $return_die = 0;
	var $shutdown = 1 ;
	var $recordset = "";
	var $currentQuery = "";
	var $error;
	var $order;
	var $row;
	var $num;
	var $start;
	var $groupby ='';
	var $port = '';
	/*-------------------------------------------------------------------------*/
	// Set up required vars
	/*-------------------------------------------------------------------------*/
	function DBESetup($host,$user,$pass,$db,$port="",$pre="")
	{
		$this->host=$host;		//variable for mysql hostname
		$this->user=$user;		//variable for mysql username
		$this->password=$pass;	//variable for mysql password
		$this->database=$db;   //variable for databse
		$this->port=$port;
		if($pre!="")  $this->tablePrefix=$pre."_";
	}
	/*
	* -------------------------------------------------------------------------
	*/
	// Connect to the database
	/*
	* -------------------------------------------------------------------------
	*/
	function connect()
	{
		if (isset($this->connectionId)) 
		{
			return true;
		}
		// MySQL extension is deprecated in PHP 5.5
		$currentErrorLevel = error_reporting();
		error_reporting($currentErrorLevel ^ E_DEPRECATED);
		if ($this->persist)
			$this->connectionId = mysqli_pconnect($this->host, $this->user, $this->password); 
		else {
			if ($this->port != '')
				$this->connectionId = mysqli_connect($this->host, $this->user, $this->password, $this->database, $this->port);
			else
				$this->connectionId = mysqli_connect($this->host, $this->user, $this->password, $this->database);
		}
			
		error_reporting($currentErrorLevel);

		if (!$this->connectionId)
		{
			$this->fatalError("Database Connection error:");
			return FALSE;
		}
		/*if (!mysqli_select_db($this->database, $this->connectionId))
		{
			$this->fatalError("Unable to select the database");
			return FALSE;
		}*/
		mysqli_query($this->connectionId, "SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
		return TRUE;
	}
	
	function fatalError($the_error="")
	{
		$this->error    = mysqli_error($this->connectionId);
		$this->error_no = mysqli_errno($this->connectionId);
		if ($this->return_die == 1)
			return;
		$the_error .= "\n\nSQL error: ".$this->error."\n";
		$the_error .= "Date: ".date("l dS of F Y h:i:s A");
		$out = "<html><head><title>Database Error</title>
			<style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
			&nbsp;<br><br><blockquote><b>There appears to be an error with the database.</b><br>
			You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
			<br><br><b>Error Returned</b><br>
			<form name='mysql'><textarea rows=\"15\" cols=\"60\">".htmlspecialchars($the_error)."</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";
		print $out;
		exit();
	}

	function selectOrder($str)
	{
		$this->order = $str;
	}
    
    function selectGroupBy($values)
    {
        
          if(is_array($values))
            $values = implode(",",$values);
          $this->groupby = $values ;
    }

	function simpleSelect($get,$table,$where="",$limit=-1,$limitStart=0,$exe_query=true)
	{
		if(is_array($get))
			$get= implode(",",$get);
		        
		$this->currentQuery = "SELECT $get FROM ".$this->tablePrefix.$table;

		if ( $where != "" )
			$this->currentQuery .= " WHERE ".$where;		
        if(isset($this->groupby) && $this->groupby !='')
            $this->currentQuery .= " Group by ".$this->groupby;
        if(isset($this->order) && $this->order!='')
			$this->currentQuery.= " Order by ".$this->order;	
		if($limit!=-1)
			$this->currentQuery .= " Limit $limitStart , $limit";
			           
		$t=$this->query($this->currentQuery,$exe_query);
		if(isset($this->order) && $this->order!='')
			$this->selectOrder('');
		if($limit!=-1)      
			$this->selectLimit(-1);

		return $t;
	}

	function simpleSelectIn($get,$table,$where="", $col="",$in="",$not_in=1,$limit=-1,$limitStart=0,$exe_query=true)
	{
		/// this function is used for using the flexibility of the IN clause
		/*
		* The $not_in variable control that the query runs with IN clause or NOT IN clause
		*
		* here $in is for the values which will be in the result array
		*
		* this is added by debabrata in problem please contact with him
		*
		*/
		if(is_array($get))
			$get= implode(",",$get);
		$this->currentQuery = "SELECT $get FROM ".$this->tablePrefix.$table;
		if ( $where != "" )
		{
			$this->currentQuery .= " WHERE ".$where;
			if ($in!="")
			    $this->currentQuery .= " and ";
		} 
		else if ($in!="")
		{
			$this->currentQuery .= " WHERE ";
		}
		if ($not_in)
		{
			if ($in!="")
			{
				if ($col!="")              
					$this->currentQuery .= "`$col` not in (".$in.")";				
				else			                  
					$this->currentQuery .= "`id` not in (".$in.")";			
			}
		} 
		else
		{
			if ($in!="")
			{
			    if ($col!="")
			        $this->currentQuery .= "`$col` in (".$in.")";
			    else
			        $this->currentQuery .= "`id` in (".$in.")";
			}
		}

        if(isset($this->groupby) && $this->groupby !='')
            $this->currentQuery .= " Group by ".$this->groupby;
		if(isset($this->order) && $this->order!='')
			$this->currentQuery.= " Order by ".$this->order;
		if($limit!=-1)
			$this->currentQuery .= " Limit $limitStart , $limit";

		$t=$this->query($this->currentQuery,$exe_query);
		if(isset($this->order) && $this->order!='')
			$this->selectOrder('');
		return $t;
	}

	function query($the_query,$exe_query=true)
	{
		//-----------------------------------------
		// Run the query
		//-----------------------------------------
		$this->result = true;
		if(!$exe_query)
			return $the_query;

		if(!isset($this->connectionId))
			$this->connect();
			
		$this->recordset = mysqli_query($this->connectionId, $the_query);

		if (! $this->recordset )
		{
			$this->result = false;
			$this->fatalError($the_query);
		}
		$this->currentQuery = $the_query;
        return $this->result ;
	}
	/*-------------------------------------------------------------------------
	// SIMPLE: SELECT WITH JOIN
	$this->DBE->simpleSelectWithJoin(      'c.*',
 						array( 'comments' => 'c' ),
 						"event_id=$eventId and $type",
 						array( 0 => array( 'select' => 'm.f_name FirstName, m.s_name SurName',
 							               'from'   => array( 'user' => 'm' ),
 										   'where'  => 'm.id=c.user_id',
 										   'type'   => 'left'
	                                       )
	                    )
	);
	-------------------------------------------------------------------------*/
	function selectLimit($num,$start=0)
	{
		$this->start = $start;
		$this->num = $num;
	}

	function simpleSelectWithJoin( $get, $table, $where="", $add_join=array(),$exe_query= true )
	{
		//-----------------------------------------
		// OK, here we go...
		//-----------------------------------------
		$select_array   = array();
		$from_array     = array();
		$joinleft_array = array();
		$where_array    = array();
		$final_from     = array();
		$select_array[] = $get;
		$from_array[]   = $table;

		if ( $where )
			$where_array[]  = $where;
		//-----------------------------------------
		// Loop through JOINs and sort info
		//-----------------------------------------
		if ( is_array( $add_join ) and count( $add_join ) )
		{
			foreach( $add_join as $i => $join )
			{
      			if ( $join['select'] )
					$select_array[] = $join['select'];

				if ( $join['type'] == 'inner' )
				{
					# Join is inline
					$from_array[]  = $join['from'];
					if ( $join['where'] )
						$where_array[] = $join['where'];
				}
				else if ( $join['type'] == 'left' )
				{
					# Join is left
					$tmp = " LEFT JOIN ";
					foreach( $join['from'] as $tbl => $alias )
						$tmp .= $this->tablePrefix.$tbl.' '.$alias;
					if ( $join['where'] )
						$tmp .= " ON ( ".$join['where']." ) ";

					$joinleft_array[] = $tmp;
					unset( $tmp );
				}
				else
				{
						# Not using any other type of join
				}
			}
		}
		//-----------------------------------------
		// Build it..
		//-----------------------------------------
		foreach( $from_array as $i )
		{
			foreach( $i as $tbl => $alias )
			    $final_from[] = $this->tablePrefix.$tbl.' '.$alias;
		}

		$get   = implode( ","     , $select_array   );
		$table = implode( ","     , $final_from     );
		$where = implode( " AND " , $where_array    );
		$join  = implode( "\n"    , $joinleft_array );
		$this->currentQuery = "SELECT $get FROM $table";

		if( $join )
			$this->currentQuery .= " ".$join." ";

		if( $where != "" )
			$this->currentQuery .= " WHERE ".$where;

        if(isset($this->groupby) && $this->groupby !='')
            $this->currentQuery .= " Group by ".$this->groupby;

		if(isset($this->order)&&$this->order!="")
			$this->currentQuery.= " Order by ".$this->order;

		if($this->num>0)       
			$this->currentQuery.=" Limit ".$this->start.", ".$this->num;


		$res = $this->query( $this->currentQuery,$exe_query);
		if(isset($this->order) && $this->order!='')
			$this->selectOrder('');

		if($this->num!=-1)
			$this->selectLimit(-1);
		return $res;
	}
	/*
	* Executes passes SQL query
	* @Param :  sSql string sql
	* @return	object	Query ID
	*/
	function executeQuery($sSql,$exe_query=true)
	{
		$this->result = true;
		if(!$exe_query)
			return $sSql;
		if(!isset($this->connectionId))
			$this->connect();

		if(! mysqli_query($this->connectionId, $sSql) ) 
		{
			$this->result=false;
			$this->fatalError($sSql);
		}
        return $this->result ;
	}
	/*-------------------------------------------------------------------------*/
	// SIMPLE: DELETE
	/*-------------------------------------------------------------------------*/
	function simpleDelete( $tbl,$where,$exe_query=true)
	{
		$this->currentQuery= " DELETE FROM ".$this->tablePrefix.$tbl;
		if ( $where )
			$this->currentQuery .= " WHERE $where";
		return $this->executeQuery( $this->currentQuery,$exe_query= true );
	}
	/*-------------------------------------------------------------------------*/
	// Quick function: DO INSERT
	/*-------------------------------------------------------------------------*/
	function Insert( $tbl, $arr,$exe_query=true)
	{
		$dba   = $this->compile_db_insert_string( $arr );
		$query = "INSERT INTO ".$this->tablePrefix."$tbl ({$dba['FIELD_NAMES']}) VALUES({$dba['FIELD_VALUES']})";
		$this->query( $query,$exe_query);
		return $this->result;
	}
	/*
	* Quick function: Multiple values insert
	*/
	function multiInsert($tbl,$field,$values,$exe_query=true)
	{
		//-----------------------------------------
		// Form query
		//-----------------------------------------
		$dba_field   = $this->compile_db_insert_field_string( $field);

		$dba_values   = $this->compile_db_insert_values_string( $values);
		$query = "INSERT INTO ".$this->tablePrefix."$tbl ({$dba_field}) {$dba_values}";
		return $this->executeQuery( $query,$exe_query);
	}
	/*-------------------------------------------------------------------------*/
	// Create an array from a multidimensional array returning formatted
	// strings ready to use in an INSERT query, saves having to manually format
	// the (INSERT INTO table) ('field', 'field', 'field') VALUES ('val', 'val')
	/*-------------------------------------------------------------------------*/
	/**
	* Compiles SQL formatted insert fields
	*
	* @param	array	Array of field => value pairs
	*
	* @return	array	FIELD_NAMES (string) FIELD_VALUES (string)
	*/
	function compile_db_insert_string($data)
	{
		$field_names  = "";
		$field_values = "";
		foreach ($data as $k => $v)
		{
			$v = addslashes( $v );
			$field_names  .= "$k,";
			if($v == "NOW()") $field_values .= ($v.",");
			else if ( gettype( $v ) == 'string' )
				$field_values .= "'$v',";
			else if ( gettype( $v )  == 'int' )
				$field_values .= intval($v).",";
			else if ( gettype( $v )  == 'float' )
				$field_values .= floatval($v).",";
		}
		$field_names  = preg_replace( "/,$/" , "" , $field_names  );
		$field_values = preg_replace( "/,$/" , "" , $field_values );
		return array( 'FIELD_NAMES'  => $field_names,'FIELD_VALUES' => $field_values,);
	}

	/**
	* This function returns the formatted field values for multiple insert    *
	* @param mixed $datas
	* @return mixed
	*/
	function compile_db_insert_values_string($datas)
	{
		$field_values = "VALUES";
		for($i=0;$i<count($datas);$i++)
		{
			$field_values.="(";
			foreach ($datas[$i] as $v)
			{
				$v = addslashes( $v );
				if ( gettype( $v ) == 'string' )
					$field_values .= "'$v',";
				else if ( gettype( $v )  == 'int' )
					$field_values .= intval($v).",";
				else if ( gettype( $v )  == 'float' )
					$field_values .= floatval($v).",";
			}
			$field_values = preg_replace( "/,$/" , "" , $field_values );
			$field_values.=')';
			if($i!=count($datas)-1)
				$field_values.=",";
		}
		return $field_values;
	}
	/**
	* THis function returns the csv field  for single or multiple insert    *
	* @param mixed $data
	* @return mixed
	*/
	function compile_db_insert_field_string($data)
	{
		$field_names  = "";
		foreach ($data as $k)
			$field_names  .= "$k,";
		$field_names  = preg_replace( "/,$/" , "" , $field_names  );
		return  $field_names;
	}

	/**
	 * THis function returns the csv field  for single or multiple insert    *
	 * @param string
	 * @return boolean
	 */

	function is_table_exists($table = '') {

		$query = "SHOW TABLES LIKE '".$table."'";
		if(mysqli_num_rows(mysqli_query($this->connectionId, $query))==1) return true;
		else return false;
	}

	/**
	 * THis function returns the csv field  for single or multiple insert    *
	 * @param array $columns
	 * @param string $tableName
	 * @param string $primaryKey
	 */

	 function createTable($columns = array(), $tableName = '', $primaryKey = '') {

		 $sql =  "CREATE TABLE ".$tableName."( ";

		 foreach($columns as $key => $val) {

			 $sql .= $key . " " . $val['dbString'];

		 }

		 $sql .= $primaryKey;

         $this->currentQuery = $sql;

		 return $this->executeQuery( $this->currentQuery,$exe_query= true );

	 }

	/*-------------------------------------------------------------------------*/
	// Quick function: DO UPDATE
	/*-------------------------------------------------------------------------*/
	function Update( $tbl, $arr, $where="",$exe_query=true)
	{
		$dba   = $this->compile_db_update_string( $arr );
		$query = "UPDATE ".$this->tablePrefix."$tbl SET $dba";
		if ( $where  )
		   $query .= " WHERE ".$where;
		$this->executeQuery( $query,$exe_query );
		return $this->result;
	}
	/**
	* Compiles SQL formatted update fields
	*
	* @param	array	Array of field => value pairs
	*
	* @return	string	SET .... update string
	*/
	function compile_db_update_string($data)
	{
		$return_string = "";
		foreach ($data as $k => $v)
		{
			//-----------------------------------------
			// Adding slashes?
			//-----------------------------------------
			$v = addslashes( $v );
			//-----------------------------------------
			// No? best guess it is then..
			//-----------------------------------------
			if ( is_numeric( $v ) and intval($v) == $v )
			    $return_string .= $k . "=".$v.",";
			else
				$return_string .= $k . "='".$v."',"; 
		}
		$return_string = preg_replace( "/,$/" , "" , $return_string );
		return $return_string;
	}

	/*-------------------------------------------------------------------------*/
	// SIMPLE: UPDATE
	/*-------------------------------------------------------------------------*/
	function simpleUpdate( $tbl, $set, $where, $low_pro='',$exe_query=true )
	{
		if ( $low_pro )
			$low_pro = ' LOW_PRIORITY ';

		$this->currentQuery = "UPDATE ". $low_pro ." ".$this->tablePrefix ." $tbl SET $set";

		if ( $where )
			$this->currentQuery .= " WHERE $where";

		$this->executeQuery( $this->currentQuery,$exe_query);
	}
	/*-------------------------------------------------------------------------*/
	// Fetch a row based on the last query
	/*-------------------------------------------------------------------------*/
	function fetchRow( $resulttype=MYSQLI_ASSOC )
	{
		return $this->row = mysqli_fetch_array($this->recordset,$resulttype);
	}
	/*-------------------------------------------------------------------------*/
	// Fetch a row based on the last query in Object form
	/*-------------------------------------------------------------------------*/
	function fetchRowObject()
	{
		return $this->row = mysqli_fetch_object($this->recordset);
	}
	/*-------------------------------------------------------------------------*/
	// Fetch the number of rows affected by the last query
	/*-------------------------------------------------------------------------*/
	function getAffectedRows()
	{
		return mysqli_affected_rows($this->connectionId);
	}
	/*-------------------------------------------------------------------------*/
	// Fetch the number of rows in a result set
	/*-------------------------------------------------------------------------*/
	function getNumRows()
	{
		return mysqli_num_rows($this->recordset);
	}
    /*-------------------------------------------------------------------------*/
    // Fetch the last insert id from an sql autoincrement
    /*-------------------------------------------------------------------------*/
	function getInsertId()
	{
		return mysqli_insert_id($this->connectionId);
	}
    /*-------------------------------------------------------------------------*/
    // Free the result set from mySQLs memory
    /*-------------------------------------------------------------------------*/
	function freeResult($recordset="")
	{
		if ($recordset == "")
			$recordset = $this->recordset;
		mysqli_free_result($recordset);
	}
	/*-------------------------------------------------------------------------*/
	// Return an array of tables
	/*-------------------------------------------------------------------------*/
	function getTableNames()
	{
		$result     = mysqli_list_tables($this->database);
		$num_tables = @mysqli_num_rows($result);
		for ($i = 0; $i < $num_tables; $i++)
			$tables[] = mysqli_tablename($result, $i);
		mysqli_free_result($result);
		return $tables;
	}
	/*-------------------------------------------------------------------------*/
	// Return an array of fields
	/*-------------------------------------------------------------------------*/
	function getResultFields($recordset="")
	{
		if ($recordset == "")
		    $recordset = $this->recordset;

		while ($field = mysqli_fetch_field($recordset))
		    $Fields[] = $field;

		return $Fields;
	}
	/*-------------------------------------------------------------------------*/
	// Filtering the input string from xss
	/*-------------------------------------------------------------------------*/
	function safeString($string)
	{		
		if(get_magic_quotes_gpc())
			$string = stripslashes($string);	
		$string = mysqli_real_escape_string($string);
				
		return $string;
	}
	/*-------------------------------------------------------------------------*/
	// Endcoding html content to protect mysql injection attacks
	/*-------------------------------------------------------------------------*/
	function safeContent($string,$type="encode")
	{		
		if($type=="encode")
		{
			$result = htmlentities ($string,ENT_COMPAT);		
			$result = htmlentities ($result,ENT_COMPAT,UTF-8);
		}
		else
			$result = html_entity_decode($string);
						
		return $result;
	}
	/*-------------------------------------------------------------------------*/
	// Shut down the database
	/*-------------------------------------------------------------------------*/
	function close_db()
	{
		if ( $this->connectionId )
			return @mysqli_close( $this->connectionId );
	}
    
	function getConId()
	{
		if(!isset($this->connectionId))
			$this->connect();
		return $this->connectionId;
	}

	function reset()
	{
		unset($this->order);
		unset($this->start);
		unset($this->num);
	}



	/*-------------------------------------------------------------------------*/
	// Quick function: DO UPDATE IN
	/*-------------------------------------------------------------------------*/
	function UpdateIn( $tbl, $arr, $inColumn, $in = array(),$exe_query=true)
	{
		$dba   = $this->compile_db_update_string( $arr );
		$query = "UPDATE ".$this->tablePrefix."$tbl SET $dba";
		if ( $in ) {
			$inList = '(' . implode( $in, ',' ) . ')';
			$query .= " WHERE " . $inColumn . " IN " . $inList;
		}
		$this->executeQuery( $query,$exe_query );
		return $this->result;
	}


	/*-------------------------------------------------------------------------*/
	// SIMPLE: DELETE IN
	/*-------------------------------------------------------------------------*/
	function simpleDeleteIn( $tbl, $inColumn, $in = array() ,$exe_query=true)
	{
		$this->currentQuery= " DELETE FROM ".$this->tablePrefix.$tbl;
		if ( $in ) {
			$inList = '(' . implode( $in, ',' ) . ')';
			$this->currentQuery .= " WHERE " . $inColumn . " IN " . $inList;
		}
		return $this->executeQuery( $this->currentQuery,$exe_query= true );
	}
}
