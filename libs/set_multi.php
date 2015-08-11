<?php
class SetMulti {
	function extractHierarchic($paths, $data = null, $options = array()) {
		$defaultOptions = array(
			'allowEmptyString' => false,
			'allowFalse' => true,
			'allowZero' => true
		);
		$options = array_merge($defaultOptions,$options);
		foreach((array)$paths as $path){
			$val = Set::extract($path, $data, $options);
			
			if(!is_null($val) 
				&& ($val!=='' || $options['allowEmptyString']) 
				&& ($val!==false || $options['allowFalse']) 
				&& ($val!==0 || $options['allowZero'])
			){
				return $val;
			}
		}
		return null;
	}
	function extractMulti($paths, $data = null, $options = array()) {
		$defaultOptions = array(
		
		);
		$options = array_merge($defaultOptions,$options);
		$res = array();
		foreach((array)$paths as $key => $path){
			$res[$key] = Set::extract($path, $data, $options);
		}
		return $res;
	}
	function extractHierarchicMulti($pathsAssoc, $data = null, $options = array()) {
		$defaultOptions = array(
			'extractNull' => true,
		);
		$options = array_merge($defaultOptions,$options);
		$res = array();
		foreach($pathsAssoc as $name => $paths){
			$val = SetMulti::extractHierarchic($paths, $data, $options);
			if(!is_null($val) || $options['extractNull']){
				$res[$name] = $val;
			}
		}
		return $res;
	}
	function isAssoc($array) {
		return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
	} 
	
	function pregFilter($pattern,$array){
		$res = array();
		foreach($array as $key => $val){
			if(preg_match($pattern,$val) ){
				if(is_int($key)){
					$res[] = $val;
				}else{
					$res[$key] = $val;
				}
			}
		}
		return $res;
	}
	function pregFilterKey($pattern,$array){
		$res = array();
		foreach($array as $key => $val){
			if(preg_match($pattern,$key) ){
				$res[$key] = $val;
			}
		}
		return $res;
	}
}
?>