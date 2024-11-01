<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://uniquehosting.net/
 * @since      1.0.0
 *
 * @package    WarmupReps
 * @subpackage WarmupReps/admin/partials
 */
/*$all_post_types = get_post_types();
print_r($all_post_types);*/
?>
<style type="text/css">
	.warm-row{display: flex;}
	.col-6{width: 50%;}
	.wmp-btn-box { margin: 20px 0; }
</style>
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php 
    if ( isset( $_GET['updated'] ) ) {
	?><div id="message" class="updated notice is-dismissible"><p><?php _e( 'Settings saved.' ); ?></p></div>
	   	<?php
}
?>
	<div class="warm-row">
<?php 
		echo '<div class="col-6">';
   		echo "<form action='" . get_admin_url() . "admin-post.php' method='post' id='wp_wmp_options'>";
		echo '<input name="action" value="wp_wmp_settings_page" type="hidden"/>';
		 wp_nonce_field('wp_wmp_settings_page', 'wp_wmp_settings_page_nonce');
    ?>
        <table class="form-table" role="presentation">
        	<tbody>
        		<tr>
				<th scope="row"><label for="measurement_units">Measurement units</label></th>
				<td>
					<select name="measurement_units" id="measurement_units" class="regular-text">
						<option value="">Select Measurement units</option>
						<?php $units = uniwmp_get_measurement_units();
							$selected_unit = get_option('wmp_measurement_units');
							foreach($units as $key => $name) {
								$selected = ($selected_unit==$key) ? 'selected' : '';
								echo '<option value="'.$key.'" '.$selected.'>'.$name.'</option>';
							}

						 ?>
					</select>
				</td>
				</tr>
				<tr>
				<th scope="row"><label for="bar_type">Bar Type (in lbs) </label></th>
				<td>
					<select name="bar_type" id="bar_type" class="regular-text">
						<option value="">Select Bar type</option>
						<?php $bar_types = uniwmp_get_bar_types();
							$selected_bar_type = get_option('wmp_bar_type');
							foreach($bar_types as $key => $row) {
								$selected = ($selected_bar_type==$row['name']) ? 'selected' : '';
								echo '<option value="'.$row['name'].'" '.$selected.'>'.$row['name'].' ('.$row['unit'].')</option>';
							}

						 ?>
					</select>
				</td>
				</tr>
				<!-- <tr>
					<th scope="row"><label for="show_all_pages">Do you want to show shortcode on all pages? </label></th>
					<td>
						<?php 
						$isChecked= !empty(get_option('wmp_show_on_all_pages')) ? 'checked' : '';
						?>
						<input type="checkbox" <?php echo $isChecked; ?> name="show_all_pages" id="show_all_pages" value="yes">
					</td>
				</tr>-->
				<tr valign="top">
					<th scope="row"> Warmup Shortcode Selector</th>
					<td>
						<input class="regular-text" type="text" name="wmp_wrapper_class" value="<?php echo esc_attr(get_option('wmp_wrapper_class')); ?>">
						<p>Warmup shortcode [show_all_workouts] will show at the end of this selector.</p>
					</td>
				</tr>	
				<tr> 
					<th> <label> Display shortcode in blog? </label> </th>
					<td>  
						<?php  $display_blog = get_option('wmp_display_blog');?>
						<label for='display_blog_1'>  <input type="radio" name="display_blog"  value="yes"  id="display_blog_1" <?php echo ($display_blog=='yes') ? 'checked' : ''; ?> /> Yes </label>
						<label for='display_blog_2'>  <input type="radio" name="display_blog"  value="no"  id="display_blog_2" <?php echo ($display_blog=='no') ? 'checked' : ''; ?> /> No </label>
					 </td>
				</tr>			 

        	</tbody>
        </table>
        <?php submit_button(); ?>  
    	</form> 
	</div>
	<div class="col-6">
		<?php 
		//	if(get_option('wmp_default_data_imported')) :
			echo '<div class="wmp-btn-box">

				<a href="javascript:void(0)" class="button button-primary wmp_import_data">Import Default Data <span class="spinner"></span></a>
				<div class="wmp-response"></div>
			</div>';
	//	endif;
		?>
	</div>
</div>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
