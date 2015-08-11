# Search Plugin
for cakePHP 1.3

## Installation

1. Place the folder "search" in "app/plugins/".
2. Run "database.sql" in the database.

## Getting started

1. Add the Searchable behavior to the desired models
  ```php
  var $actsAs = array('Search.Searchable');
  ```
2. Reindex the content by navigating to `admin/search/search_table/build`
3. You can now start doing search by navigating to `search/search_table/search`
3. You can can add it to your layout :
  ```php
	<?php
		echo $this->Form->create('SearchTable', array('class' => 'search', 'url' => array('plugin'=>'search','controller'=>'search_table','action' => 'search')));
		echo $this->Form->input('q', array(
									'class' => 'keyword', 
									'label' => false, 
									'after' => $form->submit(__('Search', true), array('div' => false))
								));
		echo $this->Form->end();
	?>	
  ```
  
## Config

  ```php
  var $actsAs = array(
    // Url the thumnails will point to
    'url'=> array(
			'prefix' => false,
			'admin' => false,
			'plugin'=>'{plugin}',
			'controller'=>'{controller}',
			'action'=>'view',
			'{id}', // "{id}" is a placeholder that will be replaced by the real id
			'lang'=>'{lang}', // "{lang}" is a placeholder that will be replaced by the real language
		),
    // Field that will be used as the title of the thumnail
		'title'=>'title',
    // Field that will be used as the text below the title in the thumnail
		'desc'=>'desc',
    // Fields that will be used for the full-text search
		'content'=>array('short_desc','short_text','desc','text'),
    // You must set this if this model is within a plugin
		'plugin'=>null
  );
  ```