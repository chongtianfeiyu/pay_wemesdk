<?php
	interface i_db_query_callback {

		public function query($sql,$callback_fun);
	}
	interface i_db{
		
		public function db_get_connection_info	($db_name);
		public function db_query				($str_sql,$db_name,$_b_debug=false);
		public function db_query_ex				($str_sql,$db_name,$_b_debug=false);
		public function db_exec					($str_sql,$db_name,$_b_debug=false);
		public function db_get_value			($str_sql,$db_name,$_b_debug=false);
	}
	class c_rs implements Iterator {
				
		private $_m_n_index;
		private $_m_ar_data;

		function __construct($_ar)					{$this->_m_ar_data=$_ar;$this->_m_n_index = 0;}
		function rewind		()						{$this->_m_n_index = 0;}
		function current	()						{return $this;}
		function key		()						{return $this->_m_n_index;}
		function next		()						{++$this->_m_n_index;}
		function valid		()						{return isset($this->_m_ar_data[$this->_m_n_index]);}
		function get		($_key,$_b_dump=false)	{
			
			if(sizeof($this->_m_ar_data)==0){return '';}else{$_ar_x=$this->_m_ar_data[$this->_m_n_index];if($_b_dump){print_r($_ar_x);}else{return $_ar_x[$_key];}}
		}
		function get_json	()						{return json_encode($this->_m_ar_data);}
		function get_array	()						{return	$this->_m_ar_data;}
	}	
?>