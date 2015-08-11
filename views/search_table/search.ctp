<div class="page formulaire">
	<?php
		echo $this->Form->create('SearchTable', array('class' => 'search', 'url' => array('plugin'=>'search','controller'=>'search_table','action' => 'search')));
		echo $this->Form->input('q', array(
									'class' => 'keyword', 
									'label' => false, 
									'after' => $form->submit(__('Search', true), array('div' => false))
								));
		echo $this->Form->end();
	?>	
		<div class="wrapper">
	<div class="resultat_search">
		<h1><?php __('Résultats de recherche'); ?></h1>
		<?php
			if(!empty($results)) {
				$i = 1;
				foreach($results as $r) {
					?>
					<div class="contenu_pages">
						<h3><?php echo $i; ?>. <a href="<?php echo $html->url($r['SearchTable']['link']); ?>"><?php echo $r['SearchTable']['title']; ?></a></h3>
						<p><?php echo $text->highlight($text->excerpt($r['SearchTable']['content'], $originalkeyword, 100), explode(' ', $originalkeyword)); ?></p>
					</div>
					<?php
					$i++;
				}
			}
			else {
			?>
				<div class="contenu_pages">
					<h3><?php __('Aucun résultat ne correspond à votre recherche.'); ?></a></h3>
				</div>
			<?php
			}
		?>
	</div>
	</div>
</div>