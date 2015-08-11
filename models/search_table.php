<?php
class SearchTable extends SearchAppModel {
	var $name = 'SearchTable';
	var $actsAs = array('Locale');
	
	function cleanSearchData($data){
		if(is_array($data)){
			foreach($data as &$d){
				$d = $this->cleanSearchData($d);
			}
			return $data;
		}else{
			return strtolower(str_replace('_',' ',Inflector::slug(strip_tags($data))));
		}
	}
}
?>