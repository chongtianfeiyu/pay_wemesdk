<?php
/* mysql asynchronous query proxy class will be used to multi databases instance concurrent query
 * powered by native mysqlnd driver in the php extension
 *
 * Author : Breezer , 2014.08.07
 * 
 *
 */
class c_swoole_sql_query implements i_db_query_callback {
	
	public function __construct(){

		/* ini 
		 *
		 */
		$this->m_ar_data	 = array()	;	/* memory key value		*/
	 	$this->m_sql		 = ''		;	/* mysql query string	*/

		/* ini swoole 
		 *
		 */
		$this->swoole_sql_query_start();
	}
	public function query($sql,$callback_fun){

		$this->swoole_sql_query($sql,$callback_fun);
	}
	private function swoole_sql_query($sql,$callback_fun){

		$this->m_sql								= $sql;
		$this->m_ar_data['db_swoole_callback_fun'] 	= $callback_fun;
		$this->m_ar_data['db_index'] 				= 0;
		$i											= 0;for(;;){$db_key = 'mysqli_'.$i;if(isset($this->m_ar_data[$db_key])){

				/* mysqli async query
				 *
				 */
				$this->m_ar_data['db_index']++;$db = $this->m_ar_data[$db_key];$db->query($sql, MYSQLI_ASYNC);
			}else{

				break;
			}
			$i++;
		}
	}
	private function swoole_sql_query_end(){

		/* free mysqli
		 *
		 */
		$i=0;for(;;){

			$db_key = 'mysqli_'.$i;if(isset($this->m_ar_data[$db_key])){

				$db = $this->m_ar_data[$db_key];swoole_event_del(swoole_get_mysqli_sock($db));$db->close();
			}else{

				break;
			}
			$i++;
		}

		/* free swoole
		 *
		 */
		swoole_event_exit();
		
		/* free memory
		 *
		 */
		unset($this->m_ar_data);$this->m_ar_data=null;
	}	
	private function swoole_sql_query_start(){

		/* ini one mysqli object
		 *
		 */
		function mysqli_ini			($p_this){
			
			$i=0;foreach(c_db_pool::$slave_dbs as $v){$p_this->m_ar_data['mysqli_'.$i] = mysqli_init();$i++;}
		}
		function mysqli_connect_ex	($db,$db_ip,$db_login_user_name,$db_login_password,$db_name){

			/* connect mysql.svr perhaps will be failed
			 *
			 */
			$r 			= false;
			$r_options 	= $db->options(MYSQLI_OPT_CONNECT_TIMEOUT,1);
			@$db->real_connect($db_ip,$db_login_user_name,$db_login_password,$db_name);if($db->connect_error){}else{$r=true;};return $r;
		}

		/* ini all mysqli object
		 *
		 */
		$ar_mysqli 		= array();mysqli_ini($this);

		/* connect to mysql.svr 
 	  	 *
		 */
		$i=0;for(;;){

			$db_key = 'mysqli_'.$i;if(isset($this->m_ar_data[$db_key])){

				/* get one mysqli object
				 *
				 */			
				$db			= $this->m_ar_data[$db_key];

				/* try to connect mysql.svr by mysqli client object
				 *
				 */
				$_key_mysql = 'slave_db_'.(1+$i);
				$rx			= mysqli_connect_ex	(
													$db																	,
													c_db_pool::$slave_dbs[$_key_mysql]['db_server_name'				]	,
													c_db_pool::$slave_dbs[$_key_mysql]['db_database_login_username'	]	,
													c_db_pool::$slave_dbs[$_key_mysql]['db_database_login_password'	]	,
													c_db_pool::$slave_dbs[$_key_mysql]['db_database_name'			]				
												);
				
				/* hold connect successfully mysqli object
				 *
				 */
				if($rx===false){}else{$ar_mysqli []=$db;}
			}else{
				
				break;
			}

			/* next item
			 *
			 */
			$i++;unset($this->m_ar_data[$db_key]);
		}

		/* reset all valid mysql.svr
		 *
		 */
		$i=0;foreach($ar_mysqli as $db){$this->m_ar_data['mysqli_'.$i]=$db;$i++;$this->swoole_sql_query_ex($db);}

		/* free memory
		 *
		 */
		unset($ar_mysqli);$ar_mysqli=null;
	}
	private function swoole_sql_query_ex_error(){

		if(isset($this->m_ar_data['db_swoole_query'])){

			/* is empty result ?
			 *
			 */
			if(count($this->m_ar_data['db_swoole_query'])==0){$this->m_ar_data['db_swoole_query'] = array();}
		}else{

			/* first asynchronous query complete,but i have found a query error ,so i reset empty array give it
			 *
			 */
			$this->m_ar_data['db_swoole_query'] = array();
		}
	}
	private function swoole_sql_query_ex($db){

		$db_sock_ex												= swoole_get_mysqli_sock($db);
		$this->m_ar_data['db_sock_ex_2_mysqli_'.$db_sock_ex]	= $db;

		/* register mysqli to swoole
		 *
		 */
		swoole_event_add($db_sock_ex, function($db_sock){

			/* one mysql svr complete query
			 *
			 */
			$db = $this->m_ar_data['db_sock_ex_2_mysqli_'.$db_sock];$res=$db->reap_async_query();if(is_object($res)){if(($res->num_rows)==0){
					
					/* i get empty query result
					 *
					 */
					$this->swoole_sql_query_ex_error();
				}else{

					/* query data success,save it to memory buffer
					 *
					 */
					foreach($res->fetch_all(MYSQLI_ASSOC)as $rs){$this->m_ar_data['db_swoole_query'][]=$rs;};$res->free();
				}
			}else{

				/* sql.svr error when asynchronous query
 				 *
				 */
				if(cst_database_sql_debug_for_swoole){echo $this->m_sql;};$this->swoole_sql_query_ex_error();
			}

			/* query reference count check point
			 *
			 */
			$this->m_ar_data['db_index']--;if($this->m_ar_data['db_index']==0){
				
				/* all mysql.svr asynchronous query complete,so i can call user callback function
				 *
				 */
				$callback_fun = $this->m_ar_data['db_swoole_callback_fun'];if(is_object($callback_fun)){
				
					/* callback user function 
					 *
					 */
					$callback_fun($this->m_ar_data['db_swoole_query'],$this);unset($this->m_ar_data['db_swoole_query']);$this->m_ar_data['db_swoole_query']=null;
					
					/* all query complete notify swoole kernel exit waiting
					 *
					 */
					if($this->m_ar_data['db_index']==0){$this->swoole_sql_query_end();}
				}
			} 
		});
	}
}
?>