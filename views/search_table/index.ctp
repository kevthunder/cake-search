<div class="search_table index">
	<h1><?php __('Résultats de la recherche');?></h1>
	
		<?php
			$i = 0;
			$bool = array(__('No', true), __('Yes', true));
			foreach ($$items as $$item) {
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
				/*	echo '<div class="item clearfix">';
						echo '<div class="r resume">';
							echo '<h4>'.$activity['Activity']['title'].'</h4>';
							//echo '<div class="date">'.date_('d F Y', strtotime($campaign['Campaign']['date_begin']), 'fre').'</div>';
							echo $this->Text->truncate($activity['Activity']['text'], $length =350, array('ending'=>'...','exact'=>'false','html'=>'true'));
							echo '<div class="ensavoirplus">'.$this->Html->link('En savoir plus',array( 'action'=>'view','id'=>$activity['Activity']['id'])).'</div>';
						echo '</div>';
					echo '</div>'; */
			}
		?>

	<div class="paging">
		<?php echo $this->Paginator->prev('« '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 |
		<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true).' »', array(), null, array('class' => 'disabled'));?>
	</div>

</div>