<?php
	/* database helper class,mysqli driver right now => by Breezer 
	 *
	 */	
	class c_mysql implements i_db{

		private $_m_o_mysqli;
		function db_open($db_name,$_b_read=true){	

			/* get a mysql connection string
			 *
			 */
			$_ar				= $this->db_get_connection_info($db_name,$_b_read);			

			/* create a connection by mysqli
			 *
			 */
			$this->_m_o_mysqli	= new mysqli((cst_database_connection_status_for_persistent?'p:':'').$_ar['db_server_name'],$_ar['db_database_login_username'],$_ar['db_database_login_password'],$_ar['db_database_name']);
			if(mysqli_connect_error()){

				die('	<br>
						<div style="color:white;background:#8abd00;font-size:9px;font-weight:bold;border:solid 1px #669900;height:32px;line-height:32px;text-align:center;">
							Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error().'
						</div>
					');
			}else{

				/* mysql client charset
				 *
				 */
				$this->_m_o_mysqli->set_charset('utf8');
			}
		}
		function db_close(){	

			$this->_m_o_mysqli->close();
		}

		/* mysql svr read and write balance 
		 * 
		 */
		function db_get_connection_info($db_name,$_b_read=true)
		{
			global $g_mysql_read/* read pool */,$g_mysql_write/* write pool */;
			
			$ar_sql_svr	= $_b_read?$g_mysql_read[$db_name]:$g_mysql_write[$db_name];
			$_n_index	= mt_rand(0,count($ar_sql_svr)-1);

			return $ar_sql_svr[$_n_index];	
		}
		function db_query_ex($_str_sql,$db_name,$_b_debug=false){	

			$rsx = new c_rs(array());foreach($this->db_query($_str_sql,$db_name,$_b_debug) as $rs){

				$rsx = $rs;break;
			}
			return $rsx;
		}		
		function db_query_error($sql){

			$_ar_x	= array();if(cst_database_sql_debug===true){echo '<div>'.$sql.'</div>';};$_c_rs=new c_rs($_ar_x);return $_c_rs;
		}
		
		function db_query($_str_sql,$db_name,$_b_debug=false)
		{	
			 $_ar_x	= array();
			 if($_b_debug)
			 {
			 	$_c_rs=$this->db_query_error($_str_sql);
			 }
			 else
			 {
			 	$this->db_open($db_name);
			 	$_rs=$this->_m_o_mysqli->query($_str_sql);
			 	if($_rs===false)
			 	{	
					/* db query error
					 *
					 */
				 	$_c_rs = $this->db_query_error($_str_sql);
				}
				else
				{
					/* success
					 *
					 */			
					for(;;)
					{	
						$_q = $_rs->fetch_array(MYSQLI_BOTH);if($_q==NULL){break;}else{$_ar_x[]=$_q;};unset($_q);$_q=null;
					}
					$_rs->free();$this->db_close();unset($_rs);$_rs=null;$_c_rs=new c_rs($_ar_x);unset($_ar_x);$_ar_x=null;
				}
			}
			return $_c_rs;
		}
		
		function db_exec($_str_sql,$db_name,$_b_debug=false)
		{	
			if($_b_debug)
			{
				echo ($_str_sql);
			}
			else
			{

				$this->db_open($db_name,false);
				$this->_m_o_mysqli->query($_str_sql);
				$_id_x=$this->_m_o_mysqli->insert_id;
				$this->db_close();
				
				
				
				return $_id_x;
			}
			
		}		
		function db_get_value($str_sql,$db_name,$_b_debug=false){	

			$_sx='';foreach($this->db_query($str_sql,$db_name,$_b_debug) as $rs){$_sx=$rs->get(0);break;}return $_sx;
		}
	}
?>