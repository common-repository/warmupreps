<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function uniwmp_get_measurement_units(){
	$units =array('lbs'=>'Pounds','kgs'=>'kilograms');
	return apply_filters('wmp_measurement_units_settings',$units);
}
function uniwmp_get_bar_types(){
	$bar_types = array();
	$bar_types[] = array('name'=>'Olympic','unit'=>45); 
	$bar_types[] = array('name'=>'Womens','unit'=>35); 
	$bar_types[] = array('name'=>'Standard','unit'=>20); 
	$bar_types[] = array('name'=>'Technique','unit'=>15); 
	return apply_filters('wmp_bar_types_settings',$bar_types);
}
function uniwmp_get_all_excercises() {
	global $wpdb;
	$table_name =$wpdb->prefix.'wmp_excercise';
	$query =$wpdb->prepare("SELECT * from $table_name where 1=%s",1);
	$all_rows = $wpdb->get_results($query);
	return !empty($all_rows) ? $all_rows : false;
}
function uniwmp_get_all_programs() {
	global $wpdb;
	$custom_post_type ='wmp-program';
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s and post_status = 'publish'", $custom_post_type ) );
	return  !empty($results) ? $results : false;

}
function uniwmp_get_excercise_title($ex_id=0) {
	global $wpdb;
	$table_name =$wpdb->prefix.'wmp_excercise';
	$query =$wpdb->prepare("SELECT title from  $table_name where ID=%d",$ex_id);
	$title =$wpdb->get_var($query);
	return !empty($title) ? $title : '';
}
function uniwmp_render_html_workout_layout($content='') {

	$wo_html='';
	if(!empty($content)){
		$wo_rows =json_decode($content,true);
		foreach ($wo_rows as $key => $row) {
			$action =(0==$key) ? 'add' : 'remove';
			$wo_html.= uniwmp_workout_template($row,$action);
		}
	}
	else {
		$wo_html = uniwmp_workout_template();
	}
	$html_data ='<div class="repeater-metabox"><div class="repeater">'.$wo_html.'</div></div>';
	return $html_data;
}
function uniwmp_workout_template($row=array(),$action='add'){
	if(empty($row)){
		$row= array('sets'=>'','reps'=>'','multiplier'=>'');
	}
	$icon_class = ($action=='add') ? 'dashicons-plus-alt green' : 'dashicons-minus red';
	$action_class = ($action =='add') ? 'add_new_row' : 'remove_row';

	return '<div class="form-group" data-repeatable>
		 <input type="number" name="wmp-sets[]" placeholder="Enter Sets" class="required" value="'.esc_attr($row['sets']).'">
		 <input type="number" name="wmp-reps[]" placeholder="Enter Reps" class="required" value="'.esc_attr($row['reps']).'">
		 <input type="number" name="wmp-multiplier[]" placeholder="Enter multiplier" class="required" step="0.1" value="'.esc_attr($row['multiplier']).'">
		 <a class="'.$action_class.'" href="javascript:void(0)"><i class="dashicons-before '.$icon_class.'"></i></a>
	</div>';
}
function uniwmp_is_post_has_workout($post_id=0) {
	global $wpdb;
	$table_name = $wpdb->prefix.'wmp_workout';
	$where = !empty($post_id) ? ' and post_id ='.$post_id : '';
	$query = $wpdb->prepare("SELECT * from $table_name where 1= %s $where ",1);
	$get_results = $wpdb->get_results($query);
	return !empty($get_results) ? $get_results : false;
}
function uniwmp_create_warmup_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$ex_table_name = $wpdb->prefix . 'wmp_excercise';
		if($wpdb->get_var( "show tables like '$ex_table_name'" ) != $ex_table_name) 
    	{
			$sql = "CREATE TABLE $ex_table_name (
				ID bigint(20)  NOT NULL AUTO_INCREMENT,
				title varchar(255)  NOT NULL,
				status tinyint(2)  NOT NULL,
				user_id bigint(11)  NOT NULL,
				created_at datetime DEFAULT '0000-00-00 00:00:00'  NOT NULL,	
				PRIMARY KEY  (ID)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		$workout_table = $wpdb->prefix.'wmp_workout';
		if($wpdb->get_var( "show tables like '$workout_table'" ) != $workout_table) 
    	{

    		$sql = "CREATE TABLE $workout_table (
				id bigint(20)  NOT NULL AUTO_INCREMENT,
				title varchar(255)  NOT NULL,
				user_id int(11)  NOT NULL,
				program_id bigint(20)  NOT NULL,
				exercise_id bigint(20)  NOT NULL,
				max_weight decimal(10,2)  NOT NULL,
				content text  NOT NULL,
				created_at datetime DEFAULT '0000-00-00 00:00:00'  NOT NULL,
				updated_at datetime DEFAULT '0000-00-00 00:00:00'  NOT NULL,	
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
    	}
	}
