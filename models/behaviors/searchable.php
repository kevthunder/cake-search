<?php
class SearchableBehavior extends ModelBehavior {
	
	var $defaultSettings = array(
		'url'=> array(
			'prefix' => false,
			'admin' => false,
			'plugin'=>'{plugin}',
			'controller'=>'{controller}',
			'action'=>'view',
			'{id}',
			'lang'=>'{lang}',
			'id'=>'{id}',
			'slug'=>'{slug}',
			'slug2'=>'{slug2}'
		),
		'urlKeepEmpty'=>array(
			'prefix', 'admin', 'plugin'
		),
		'langs' => array(
			'fre', 'eng'
		),
		'title'=>'title',
		'type' => null,
		'desc'=>array('short_desc','short_text','desc','text'),
		'content'=>array('short_desc','short_text','desc','text'),
		'plugin'=>null
	);
	
	function setup(&$Model, $settings) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaultSettings;
		}
		if(isset($Model->searchOptions)){
			$settings = array_merge((array)$Model->searchOptions, (array)$settings);
		}
		$subMerges = array('url','content');
		forEach($subMerges as $subMerge){
			if(!empty($settings[$subMerge]) && is_array($settings[$subMerge])){
				$settings[$subMerge] = array_merge($this->settings[$Model->alias][$subMerge], (array)$settings[$subMerge]);
			}
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (array)$settings);
		$Model->searchOptions = $this->settings[$Model->alias];
		
		$Model->bindModel(
			array('hasMany' => array(
					'SearchTable' => array(
						'className'    => 'Search.SearchTable',
						'foreignKey'   => 'foreign_id',
						'conditions'   => array('SearchTable.model' => $Model->alias),
						'dependent'    => true
					)
				)
			)
		,false);

	}
	
	function afterSave(&$model, $created) {
		$this->updateSearchTable($model, $model->id, $model->data, $created);
	}
	
	function updateSearchTable(&$Model, $id=null, $data=array(), $created = false) {
		if(empty($id)){
			$id = $Model->id;
		}
		if(!empty($id)){
			if(empty($data)){
				$data = $Model->data;
			}
			if(empty($data)){
				$data = $Model->read(null,$id);
			}
			if(!empty($data['SearchTable'])){
				$search_data = $data['SearchTable'];
			}
			if(isset($data[$Model->alias]['active'])){
				$search_data['active'] = $data[$Model->alias]['active'];
			}elseif(!$Model->hasField('active')) {
				$search_data['active'] = 1;
			}
			$plugin = $Model->searchOptions['plugin'];
			$model = $Model->alias;
			if (!$created) {
				$Model->SearchTable->recursive = -1;
				$cond = array('model' => $model, 'foreign_id' => $id);
				if(!is_null($plugin)){
					if(empty($plugin)){
						$cond['plugin'] = null;
					}else{
						$cond['plugin'] = $plugin;
					}
				}
				$searchTable = $Model->SearchTable->find('first',array('conditions'=>$cond));
				if($searchTable){
					/****** UPDATE ******/
					$search_data['id']=$searchTable['SearchTable']['id'];
					$plugin=$searchTable['SearchTable']['plugin'];
				}else{
					$created = true;
				}
			}
			if ($created) {
				/****** ADD ******/
				if(is_null($plugin)){
					$parent_class = get_parent_class($model);
					if($parent_class != 'AppModel'){
						if(preg_match("/^([\w]+)AppModel$/",$parent_class, $matches)){
							$plugin = $matches[1];
						}
					}
				}
				$search_data['foreign_id'] = $id;
				if(empty($plugin)){
					$search_data['plugin'] = null;
				}else{
					$search_data['plugin'] = $plugin;
				}
				$search_data['model'] = $model;
				
				$data[$Model->alias]['id'] = $id;
			}
			$modelName = implode('.',array_filter(array($plugin,$model)));
			
			/****** Calculate title ******/
			$paths = (array)$Model->searchOptions['title'];
			App::import('Lib', 'Search.SetMulti');
			foreach($Model->searchOptions['langs'] as $lang){
				$l_paths = $this->_addBasePath($this->_addLangPath($paths,$lang),$model);
				$search_data['title_'.$lang] = SetMulti::extractHierarchic($l_paths,$data);
				$search_data['title_for_search_'.$lang] = $Model->SearchTable->cleanSearchData($search_data['title_'.$lang]);
			}
			
			/****** Calculate desc ******/
			$paths = (array)$Model->searchOptions['desc'];
			foreach($Model->searchOptions['langs'] as $lang){
				$l_paths = $this->_addBasePath($this->_addLangPath($paths,$lang),$model);
				$search_data['content_'.$lang] = SetMulti::extractHierarchic($l_paths,$data);
			}
			
			
			/****** Calculate content for search ******/
			$paths = (array)$Model->searchOptions['content'];
			foreach($Model->searchOptions['langs'] as $lang){
				$l_paths = $this->_addBasePath($this->_addLangPath($paths,$lang),$model);
				$search_data['content_for_search_'.$lang] = implode(' ',$Model->SearchTable->cleanSearchData(array_filter(SetMulti::extractMulti($l_paths,$data))));
			}
			
			/****** Calculate URL ******/
			$urlData = array(
				'plugin'=>Inflector::underscore($plugin),
				'controller'=>Inflector::tableize($model),
				'id'=>$id
			);
			foreach($Model->searchOptions['langs'] as $lang){
				$l_urlData = $urlData;
				$l_urlData['lang'] = $lang;
				$url = $Model->searchOptions['url'];
				if(is_array($url)){
					foreach($url as $paramName =>&$param){
						preg_match("/{([\w]+)}/",$param, $matches);
						if(!empty($matches)){
							$srch = $matches[0];
							$dname = $matches[1];
							$rep = '';
							if(!empty($l_urlData[$dname])){
								$rep = $l_urlData[$dname];
							}
							$param = str_replace($srch,$rep,$param);
						}
						if(empty($param) && !in_array($paramName,$Model->searchOptions['urlKeepEmpty'])){
							unset($url[$paramName]);
						}
					}
				}
				if(method_exists($Model,'search_url')){
					$res = $Model->search_url($url,$data,$lang);
					if(is_array($res) || is_string($res)){
						$url = $res;
					}
				}
				$base = Router::url('/');
				$search_data['link_'.$lang] = preg_replace('/^'.preg_quote($base, '/').'/','/',Router::url($url));
			}
			
			/****** Calculate type ******/
			foreach($Model->searchOptions['langs'] as $lang){
				if(empty($Model->searchOptions['type'])){
					$search_data['type_'.$lang] = $this->_translate($lang,$model);
				}elseif(is_array($Model->searchOptions['type'])){
					$search_data['type_'.$lang] = $Model->searchOptions['type'][$lang];
				}else{
					$search_data['type_'.$lang] = $Model->searchOptions['type'];
				}
			}
			
			/****** SAVE ******/
			$Model->SearchTable->create();
			//debug($search_data);
			$Model->SearchTable->save($search_data);
		}
		
	}
	
	function cleanSearchData($Model,$data){
		if(is_array($data)){
			foreach($data as &$d){
				$d = $this->cleanSearchData($Model,$d);
			}
			return $data;
		}else{
			return strtolower(str_replace('_',' ',Inflector::slug(strip_tags($data))));
		}
	}
	
	function _addBasePath($paths,$basePath){
		$newPath = array();
		forEach($paths as $i=>$path){
			$newPath[$i*2] = $basePath.'.'.$path;
			$newPath[$i*2+1] = $path;
		}
		return $newPath;
	}
	function _addLangPath($paths,$lang){
		$newPath = array();
		forEach($paths as $i=>$path){
			$newPath[$i*2] = $path.'_'.$lang;
			$newPath[$i*2+1] = $path;
		}
		return $newPath;
	}
	function _translate($lang, $singular, $plural = null, $domain = null, $category = 6, $count = null){
		$curLang = Configure::read('Config.language');
		Configure::write('Config.language',$lang);
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}
		$res = I18n::translate($singular, $plural = null, $domain = null, $category = 6, $count);
		Configure::write('Config.language',$curLang);
		return $res;
	}

}
?>