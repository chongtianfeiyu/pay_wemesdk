<?php
class c_memcache{

	function __construct(){

		$this->m_server_ip		= cst_message_weme_svr_memcache			;
		$this->m_server_port	= cst_message_weme_svr_memcache_port	;
   	}
	function get($_key){
		
		$_o_mc			= new Memcache();$_o_mc->connect($this->m_server_ip, $this->m_server_port);$r=$_o_mc->get($_key);$_o_mc->close();return $r;
 	}
	function set($_key,$_value,$_n_time_out=3600){

		/* compress
		 *
		 */
		$_b_compress	= (is_string($_value)||is_array($_value))?MEMCACHE_COMPRESSED:false;

		/* connect memcache server
		 *
		 */
		$_o_mc			= new Memcache();$_o_mc->connect($this->m_server_ip, $this->m_server_port);
		
		/* is array object
		 *
		 */
		$_value			= is_array($_value)?json_encode($_value):$_value;

		/* save
		 *
		 */
		$r				= $_o_mc->set($_key, $_value, $_b_compress,$_n_time_out);$_o_mc->close();return $r;
	}
	function del($_key){

		/* del a object
		 *
		 */
		$_o_mc		= new Memcache();$_o_mc->connect($this->m_server_ip, $this->m_server_port);$r=$_o_mc->delete($_key);$_o_mc->close();return $r;
	}
}?>