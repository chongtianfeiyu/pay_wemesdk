<?php
	/* class auto load
	 *
	 */
	function __autoload($class_name){

		require_once(realpath(__DIR__.'/../class/').'/'.$class_name.'.php');
	}

	/* load all class and interface in the special directory
	 *
	 */
	function gf_load_class_by_dir_ex($_str_class_comm_dir){

		/* all dir
		 *
		 */
		$_ar_file = array();foreach (glob($_str_class_comm_dir.'*') as $file){if(is_dir($file)){$_ar_file [] = $file;}else{require_once($file);}

		/* load all file
		 *
		 */
		asort($_ar_file);foreach($_ar_file as $dir){foreach(scandir($dir) as $file){$_php_file = $dir.'/'.$file;if(is_file($_php_file)){require_once($_php_file);}}}}
	}
?>