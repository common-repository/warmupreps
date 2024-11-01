<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="warm-up-content">
	<div class="warmup-main-section">
			<div class="warmup-programs warmup-step">
			</div>
			<div class="warmup-excercises warmup-step">
			</div>
			<div class="warmup-workout warmup-step">
			</div>
	</div>

	<div class="warmup-settings">
		<div class="warmup-row">
			<div class="warm-up-heading">Unit system:</div>

			<div class="warmup-field-group wmp-radio-toolbar">
				<?php $units = uniwmp_get_measurement_units();
				foreach ($units as $key => $item) {
					$id = 'units_'.$key;
					echo '<input type="radio" name="units" id="'.$id.'" value="'.$key.'" />
	     				<label for="'.$id.'">'.ucwords($item).'</label>';
				}
				?>
			</div>
		</div>
		<div class="warmup-row">
			<div class="warm-up-heading">Bar type (in lbs):</div>
			<div class="warmup-field-group wmp-radio-toolbar">
				<?php $bar_types = uniwmp_get_bar_types();
				foreach ($bar_types as $key => $row) {
					$id = 'bar_type_'.$row['name'];
					echo '<input type="radio" name="bar-type" id="'.$id.'" value="'.$row['unit'].'" />
	     				<label for="'.$id.'">'.ucwords($row['name']).' ('.$row['unit'].')</label>';
				}
				?>
			</div>
		</div>
	</div>
	<?php

		$categories = get_categories( array('hide_empty' =>false) );

//print_r($categories);
/*
<div class="warmup-categories select-dropdown"> 
				<select name="categories_ids" id="categories_ids" class=""> 
				<option>Select Category</option>
				';
				foreach( $categories as $category ) {
					$sub_category = !empty($category->category_parent) ? '-- ' : '';
					echo '<option value="'.$category->term_id.'">'.$sub_category.''.$category->name.'</option>';
				}
				echo '</select></div>
*/

		$user = wp_get_current_user();
		$allowed_roles = array('editor', 'administrator', 'author','contributor');
		if( array_intersect($allowed_roles, $user->roles ) ) { 
			echo '<div class="wmp_publish_to_blog hide">
				
				<div class="warmup-button">
				<a href="javascript:void(0)" class="button button-inline post_to_blog"> Post To Blog <i class="dashicons dashicons-update hide spin"></i></a>
				</div>
				<div class="warmup-response"></div>
			</div> ';
		}

	?>
</div>
