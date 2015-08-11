<?php
class SearchTableController extends SearchAppController {

	var $name = 'SearchTable';
	var $helpers = array('Html', 'Form', 'Text');
	
	function search() {
		//pr($this->data);
		//exit();
		if(isset($this->data['SearchTable']['q'])) {
			$keyword = $this->data['SearchTable']['q'];
		}
		elseif(isset($this->params['named']['q'])) {
			$keyword = utf8_decode($this->params['named']['q']);
		}
		else {
			$keyword = '';
		}
		
		$originalkeyword = $keyword;
		
		$new_keyword_arr = explode(' ', $keyword);
		$new_kw = array();
		
		foreach($new_keyword_arr as $nk) {
			$new_kw[] = mysql_real_escape_string($this->SearchTable->cleanSearchData($nk).'*');
			$new_kw[] = mysql_real_escape_string($this->SearchTable->cleanSearchData($nk . 's').'*');
		}
		
		$keyword = join(' ', $new_kw);
		
		$keyword = mysql_real_escape_string($keyword);
		
		$fields = array(
			'*',
			"((5 * (MATCH(`SearchTable`.`title_for_search`) AGAINST ('$keyword'))) + (1.5 * (MATCH(`SearchTable`.`content_for_search`) AGAINST ('$keyword')))) AS `relevance`"
		);
		
		$having = '`relevance` > 0';
		
		$this->paginate['limit'] = 12;
		$this->paginate['order'] = '`relevance` DESC';
		$this->paginate['fields'] = $fields;
		$this->paginate['conditions'] = array(
			'or'=>array(
				'SearchTable.content_for_search <>' => '',
				'SearchTable.title_for_search <>' => '',
			), 
			"((5 * (MATCH(`SearchTable`.`title_for_search`) AGAINST ('$keyword'))) + (1.5 * (MATCH(`SearchTable`.`content_for_search`) AGAINST ('$keyword')))) >" => 0
		);
		
		$results = $this->paginate();
		
		$this->set(compact('keyword', 'results', 'originalkeyword'));
	}
	
	function admin_build() {
		$go = false;
		$check_models = array();
		if(!empty($this->data)){
			$check_models = $this->data['SearchTable']['models'];
			$go = true;
		}
		if(!$go){
			$models = App::objects('model');
			$models = array_combine($models,$models);
			unset($models['AppModel']);
			$plugins = App::objects('plugin');
			if (!empty($plugins)) {
				foreach ($plugins as $plugin) {
					$pluginModels = App::objects('model', App::pluginPath($plugin) . 'models' . DS, false);
					if (!empty($pluginModels)) {
						foreach ($pluginModels as $pluginModel) {
							$models[$plugin][$plugin.'.'.$pluginModel] = $pluginModel;
						}
					}
				}
			}
			$this->set('models',$models);
		}
		if($go){
			set_time_limit(0);
			
			$this->autoRender = false;
			// Empty search table
			//$this->SearchTable->query('TRUNCATE `search_tables`');
			//debug($check_models);
			foreach($check_models as $modelName){
				echo '<strong>'.str_replace('{model}',$modelName,__('Checking {model}',true)).'</strong>';
				@ob_flush();
				flush();
				$parts = explode('.',$modelName,2);
				if(count($parts)>1){
					$plugin = $parts[0];
					$model = $parts[1];
				}else{
					$plugin = null;
					$model = $parts[0];
				}
				$ModelClass = ClassRegistry::init($modelName);
				if(in_array('Search.Searchable',$ModelClass->actsAs)){
					echo ' : ';
					
					echo __('Deleting indexes',true).'<br />';
					@ob_flush();
					flush();
					$this->SearchTable->deleteAll(array('plugin'=>$plugin,'model'=>$model));
					
					echo __('Retrieving data',true).'<br />';
					@ob_flush();
					flush();
					$cond = array();
					if($ModelClass->hasField('active')) {
						$cond['active'] = 1;
					}
					$ModelClass->recursive = -1;
					$entries = $ModelClass->find('all',array('conditions'=>$cond));
					
					echo __('Building indexes',true).'<br />';
					@ob_flush();
					flush();
					$nb = count($entries);
					foreach($entries as $key=>$entry){
						$ModelClass->updateSearchTable($entry[$ModelClass->alias]['id'], $entry, true);
						if(($key+1)%5 == 0 || $key+1 == $nb){
							echo ($key+1).'/'.$nb.'<br />';
							@ob_flush();
							flush();
						}
					}
					
					echo __('Done',true);
					@ob_flush();
					flush();
				}else{
					echo ' : '.__('Ignored',true);
					@ob_flush();
					flush();
				}
				echo '<br />';
			}
			echo '<strong>'.__('All Done',true).'</strong>';
		}
	}
}
?>