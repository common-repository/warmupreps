<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpdb;
$table_name = $wpdb->prefix.'wmp_workout';
$default = array(
    'id' => 0,
    'program_id'=>'',
    'exercise_id'=>'',
    'max_weight'=>'',
    'content'=>'',
);
$item = $default;
if (isset($_REQUEST['workout'])) {
    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", sanitize_text_field($_REQUEST['workout'])), ARRAY_A);
    if (!$item) {
        $item = $default;
        $notice = __('workout has not found', 'custom_table_example');
    }
}
//print_r($item);
$exercise = uniwmp_get_all_excercises();
$program = uniwmp_get_all_programs();
?>
 <div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>
<div id="wmp-message" class=""></div>
<form id="workout-form" name="formWorkout" method="POST" onsubmit="return false;"> 
	<input type="hidden" name="action" value="render_workout_request">
	<input type="hidden" name="row_id" value="<?php echo esc_attr($item['id']); ?>">

<table class="form-table-1" role="presentation">
	<tbody>
       	<tr>
       		<td width="150">
  				<label for="wmp-program" class="wmp-label">Program </label> 
 				<select name="wmp-program" class="wmp-control required" id="wmp-program">
 					<option value="">Select Program</option>
					<?php foreach ($program as $key => $row) {
						$selected = ($row->ID==$item['program_id']) ? 'selected' : '';
						echo '<option value="'.$row->ID.'" '.$selected.'>'.$row->post_title.'</option>';
					} ?>
				</select>
			</td>
			<td width="150">
				<label for="wmp-exercise" class="wmp-label">Exercise </label>
				<select name="wmp-exercise" class="wmp-control required" id="wmp-exercise">
				<option value="">Select Exercise</option>
				<?php foreach($exercise as $key => $row) {
					$selected = ($row->ID==$item['exercise_id']) ? 'selected' : '';
					echo '<option value="'.$row->ID.'" '.$selected.'>'.$row->title.'</option>';
				}?>
				</select>
			</td>
			<td width="150">
				<label for="wmp-max-weight" class="wmp-label">Max Weight </label>
				<input class="wmp-control required" placeholder="Enter Max height" type="number" name="wmp-max-weight" id="wmp-max-weight" value="<?php echo esc_attr($item['max_weight']);?>"> 
			</td>
		</tr>
	</tbody>
</table>
<!-- <div class="sec-row">
	<a href="javascript:void(0)" class="add_new_row button button-primary ">Add Row</a>
</div> -->
<?php 
echo uniwmp_render_html_workout_layout($item['content']);
echo '<div class="wmp-submit">';
submit_button('','btnWorkoutSubmit','submit',false); 
echo '<span class="spinner"></span></div>';
?>  


</form>
<style type="text/css">
	.errorClass{
		border: 1px solid tomato !important;
	}
</style>
<?php
	$wmp_wo = new Wmp_Workout_List_Table();
	$wmp_wo->prepare_items();

    if ('delete' === $wmp_wo->current_action()) {
    	
        $message =  __('Item(s) has been deleted', 'custom_table_example');
    }

?>
<?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

<form id="excecise-filter" method="get">
	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
	<?php 
		if(isset($_REQUEST['post_type'])){
			echo '<input type="hidden" name="post_type" class="post_type_page" value="'.esc_attr($_REQUEST['post_type']).'">';
		}
	?>
	<!-- Now we can render the completed list table -->
	<?php $wmp_wo->display() ?>
</form>
