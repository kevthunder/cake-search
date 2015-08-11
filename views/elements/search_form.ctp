<?php
	echo $this->Form->create('SearchTable', array('url'=>array('plugin'=>'search', 'controller'=>'search_table', 'action'=>'search')));
		echo $this->Form->input('q', array('label'=>__('Search', true)));
	echo $this->Form->end(__('Submit', true));
?>