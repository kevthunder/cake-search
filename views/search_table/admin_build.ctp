<div class="search_tables form">
	<?php echo $this->Form->create('SearchTable',array('url'=>array('controller'=>'search_table','action'=>'build')));?>
		<fieldset>
			<legend><?php printf(__('Edit %s', true), __('SearchTable', true)); ?></legend>
			<?php
				echo $this->Form->input('models', array(
					'label'=>__('Models to check',true),
					'options' => $models, 
					'multiple'=>true, 
					'selected'=>$models,
					'after'=>'<div>'.__('Update the seach indexation of the selected models. Any model that does not have the Searchable behavior will be ignored.',true).'</div>'
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__('Submit', true));?>
</div>