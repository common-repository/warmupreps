<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Fired during plugin activation
 *
 * @link       https://uniquehosting.net/
 * @since      1.0.0
 *
 * @package    WarmupReps
 * @subpackage WarmupReps/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WarmupReps
 * @subpackage WarmupReps/includes
 * @author     Chris Newell @ Unique Technologies <chris@uniquetech.net>
 */
class Warmup_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate($network_wide='') {
		global $wpdb;

		if ( is_network_admin() && is_multisite() ) {

			$old_blog = $wpdb->blogid;
			$blogids  = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		} else {

			$blogids = array( false );

		}
		foreach ( $blogids as $blog_id ) {

			if ( $blog_id ) {
				switch_to_blog( $blog_id );
			}
			self::wmp_db_structure();
			self::save_warmup_db_data();

		}

		if ( $blog_id ) {
			switch_to_blog( $old_blog );
			return;
		}
	}
	public static function wmp_db_structure() {
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
	static function save_warmup_db_data() {

		$is_data_imported = get_option('wmp_default_data_imported');
		if($is_data_imported) return false;

		$workouts = self::warmup_workout_content();
		$user_id = get_current_user_id();
		$default_programs = self::warmup_programs();
		$default_excercises = array('Squats','Bench Press','Deadlifts','Overhead Press','Power Cleans','Barbell Rows');

		global $wpdb;
		$ex_table = $wpdb->prefix.'wmp_excercise';
		foreach ($default_excercises as $key => $ex_title) {
			$data = array('title'=>$ex_title,'user_id'=>$user_id,'created_at'=>date('Y-m-d H:i:s'));
			$wpdb->insert($ex_table,$data);
		}

		$wo_table = $wpdb->prefix.'wmp_workout';
		foreach ($default_programs as $key => $row) {
			$max_weight =50;
			$program_name = $row['name'];
			$args =array('post_title'=>$program_name,'post_type'=>'wmp-program','post_status'=>'publish');
			$program_id = wp_insert_post($args);
			foreach ($row['excercise'] as $key => $ex_id) {
				$max_weight +=100;
				$data = array('content'=>$workouts,'max_weight'=>$max_weight,'program_id'=>$program_id,'exercise_id'=>$ex_id,'user_id'=>$user_id,'created_at'=>date('Y-m-d H:i:s'));
				$wpdb->insert($wo_table,$data);
			}
		}
		update_option('wmp_default_data_imported','yes');
		wp_send_json(array('status'=>true));
	}
	static function warmup_workout_content() {

		$workout_content = array();
		$workout_content[] = array('sets'=>2,'reps'=>5,'multiplier'=>0);
		$workout_content[] = array('sets'=>1,'reps'=>5,'multiplier'=>0.4);
		$workout_content[] = array('sets'=>1,'reps'=>3,'multiplier'=>0.6);
		$workout_content[] = array('sets'=>1,'reps'=>2,'multiplier'=>0.8);
		$workout_content[] = array('sets'=>3,'reps'=>5,'multiplier'=>1);
		return json_encode($workout_content);
	}
	static function warmup_programs() {
		$default_programs = array();
		$default_programs[] = array('name'=>'Starting Strength','excercise'=>array(1,2,3,4,5));
		$default_programs[] = array('name'=>'5x5','excercise'=>array(4,5,6,7));
		$default_programs[] = array('name'=>'Max Single','excercise'=>array(2,3));
		$default_programs[] = array('name'=>'Greyskull LP','excercise'=>array(2,3,1));
		return $default_programs;
		//array('Starting Strength','5x5','Max Single','Greyskull LP','excercise'=>array(4,5));

	}
}
