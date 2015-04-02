<?php
interface i_paging{

	function get_one_page();
}
class c_paging implements i_paging{

	function get_one_page(){

		/* get current page index
		 *
		 */
		$this->m_n_current_page_number = gf_get_value_n('v_current_page_number');
	}
	function paging_first_page($v_class,$v_method,$v_more_parameters='',$_callback_ini=''){
		

		return	'	<div id=id_data_contianer>'.gf_loading_get_img().'</div>
					<script>g_js.exec.loading(function(){js_paging.more('.$v_class.','.$v_method.',"'.$v_more_parameters.'","'.$_callback_ini.'");});</script>
				';
	}
	function paging_one_page_offset($n_one_page_size=16){

		return gf_paging_by_page_number($this->m_n_current_page_number,$n_one_page_size);
	}
}?>