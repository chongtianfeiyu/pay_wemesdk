<?php
	/* current php process directory
	 *
	 */
	$_str_current_dir = dirname(__FILE__).'/';

	/* common base include file
	 *
	 */
	include($_str_current_dir.'x.lib/config/default.php'			);	/* mysql config					*/
	include($_str_current_dir.'x.lib/config/define/const.php'		);	/* const define					*/
	include($_str_current_dir.'x.lib/fun/fun_base.php'				);	/* base common function			*/
	include($_str_current_dir.'x.lib/fun/fun_comm.php'				);	/* common function				*/
	
	/* auto load all common class  
	 *
	 */
	gf_load_class_by_dir_ex($_str_current_dir.'x.lib/class.interface/');

	/* php environment ini
	 *
	 */
	include($_str_current_dir.'x.lib/fun/fun_page_start.php');	
?>