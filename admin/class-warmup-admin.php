<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
// error_reporting(E_ALL);
// ini_set('display_errors', 'ON');
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://uniquehosting.net/
 * @since      1.0.0
 *
 * @package    WarmupReps
 * @subpackage WarmupReps/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WarmupReps
 * @subpackage WarmupReps/admin
 * @author     Chris Newell @ Unique Technologies <chris@uniquetech.net>
 */
class Warmup_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = time();
		$this->plugin_postype ='wmp-program';
		add_action('init',array($this,'init_hook'));
	}
	function init_hook() {
		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);    
		add_action('admin_post_wp_wmp_settings_page', array($this,'render_wmp_settings_page'));
		add_action('wp_ajax_render_workout_request',array($this,'render_workout_request'));
		add_action('wp_ajax_nopriv_render_workout_request',array($this,'render_workout_request'));
		//add_action( 'add_meta_boxes', array($this,'warmup_add_custom_box' ));
		add_action( 'show_user_profile', [$this, 'add_extra_user_fields'],1 );
		add_action( 'edit_user_profile', [$this, 'add_extra_user_fields'],1 );

		add_action( 'personal_options_update', [$this,'save_extra_user_fields'] );
		add_action( 'edit_user_profile_update', [$this,'save_extra_user_fields'] );

		add_action( 'wp_ajax_wmp_ajax_import_data',[$this,'wmp_ajax_import_data']);

		$this->wmp_post_types();

	}
	function warmup_add_custom_box() {
		$screen ='post';
		add_meta_box(
            'warmup_workout_box',                 // Unique ID
            'Manage Workout',      // Box title
            [$this,'render_warmup_workout_box'],  // Content callback, must be of type callable
            $screen                            // Post type
        );
	}
	function render_warmup_workout_box($post) {
		$htmldata ='';
		$add_link = admin_url('edit.php').'?post_type=wmp-program&page=warmup-workout';
		$htmldata.= '<div class="wo_btn"> <a class="button" href="'.$add_link.'">Add Workout</a></div>';
		global $wpdb;
		$table_name = $wpdb->prefix.'wmp_workout';
		$ex_table  = $wpdb->prefix.'wmp_excercise';
		$query = $wpdb->prepare("SELECT wo.*,we.title as ex_title FROM $table_name wo inner join $ex_table we on wo.exercise_id = we.ID WHERE `post_id` = %s",$post->ID);
		$get_results = $wpdb->get_results($query);
		if(!empty($get_results)) {

			$htmldata.= '<table cellspacing=2 cellpadding=5 border=1>';
			$htmldata.= '<tr> <th> Program</th> <th>Excercise </th> <th> Max weight</th> <th>Date</th> <th>Action </th> </tr>';

			foreach ($get_results as $key => $row) {
				$link =admin_url('admin.php').'?page=warmup-workout&action=edit&workout='.$row->id;
				$htmldata.= '<tr>';
				$htmldata.= '<td>'.get_the_title($row->program_id).'</td>';
				$htmldata.= '<td>'.$row->ex_title.'</td>';
				$htmldata.= '<td>'.$row->max_weight.'</td>';
				$htmldata.= '<td>'.$row->created_at.'</td>';
				$htmldata.= '<td><a href="'.$link.'" target="__blank"> Edit</a></td>';
				$htmldata.= '</tr>';
			}
			$htmldata.= '</table>';
		}
		echo $htmldata;
	}
	function render_workout_request() {
		$data = $_POST;
		$workout_content = $item =array();
		for ($i=0; $i <count($data['wmp-sets']) ; $i++) { 
			$temp =array();
			$temp['sets'] = sanitize_text_field($data['wmp-sets'][$i]);
			$temp['reps'] =sanitize_text_field($data['wmp-reps'][$i]);
			$temp['multiplier'] = sanitize_text_field($data['wmp-multiplier'][$i]);
			$workout_content[] = $temp;
		}
		$item['created_at'] = 	date('Y-m-d H:i:s');
		$item['max_weight'] =	sanitize_text_field($data['wmp-max-weight']);
		$item['program_id'] = 	sanitize_text_field($data['wmp-program']);
		$item['exercise_id'] = 	sanitize_text_field($data['wmp-exercise']);
		$item['content'] = json_encode($workout_content);
		//$item['post_id'] = $data['post_id'];
		global $wpdb;
		$table_name = $wpdb->prefix.'wmp_workout';
		if(!empty($data['row_id']))
		{
			$item['updated_at'] = date('Y-m-d H:i:s');
			$result	= 	$wpdb->update($table_name,$item,array('id'=>sanitize_text_field($data['row_id'])));
		}
		else {
			$item['user_id'] = get_current_user_id();
			$result	= 	$wpdb->insert($table_name,$item);
		}
		
		$response =array('status'=>false,'msg'=>'Error: Invalid data.');
		if($result)
		{
			$response['status'] = true;
			$response['msg'] = 'Workout routine has been saved';
		}
		wp_send_json($response);
	}

	function addPluginAdminMenu() {
		//add_menu_page($this->plugin_name, 'Warmup Reps ', 'administrator', $this->plugin_name, array( $this, 'displayPluginAdminDashboard' ), 'dashicons-admin-settings', 26 );
		add_submenu_page('edit.php?post_type='.$this->plugin_postype, 'Exercises ', 'All Exercises', 'manage_options', $this->plugin_name.'-exercises', array( $this, 'render_wmp_exercises' ));
		add_submenu_page('edit.php?post_type='.$this->plugin_postype, 'Workout Routines', 'Workout Routines', 'manage_options', $this->plugin_name.'-workout', array( $this, 'render_wmp_workout_page' ));
		add_submenu_page('edit.php?post_type='.$this->plugin_postype, 'Settings', 'Settings', 'manage_options', $this->plugin_name.'-settings', array( $this, 'displayPluginAdminSettings' ));


	}
	function displayPluginAdminDashboard() {
				
	}
	function render_wmp_workout_page() {
		require_once 'partials/warmup-admin-workout-page.php';
	}
	function render_wmp_exercises() {
		require_once 'partials/warmup-admin-excercise-page.php';
	}
	function displayPluginAdminSettings() {
		require_once 'partials/warmup-admin-settings.php';
	}
	
	function render_wmp_settings_page() {
		if(!wp_verify_nonce($_POST['wp_wmp_settings_page_nonce'], 'wp_wmp_settings_page'))
		{
			http_response_code(403);
			exit;
		}	
		if( isset( $_POST['measurement_units'] ) )
		{ 
			update_option( 'wmp_measurement_units', sanitize_text_field( trim($_POST['measurement_units'] )) ); 
		}
		if( isset( $_POST['bar_type'] ) )
		{ 
			update_option( 'wmp_bar_type', sanitize_text_field( trim($_POST['bar_type'] )) ); 
		}
		if(isset($_POST['show_all_pages'])) {
			update_option('wmp_show_on_all_pages',sanitize_text_field($_POST['show_all_pages']));
		} 
		if(isset($_POST['page_id'])) {
			update_option('wmp_dropdown_page',sanitize_text_field($_POST['page_id']));
		} 
		if(isset($_POST['wmp_wrapper_class']))  {
			update_option('wmp_wrapper_class',sanitize_text_field($_POST['wmp_wrapper_class']));
		}
		if(isset($_POST['display_blog'])) {
			update_option('wmp_display_blog',sanitize_text_field($_POST['display_blog']));	
		} 
		$page_url =get_admin_url() . 'admin.php?page='.$this->plugin_name.'-settings';
		wp_safe_redirect(
            esc_url_raw(
                add_query_arg( 'updated', 'true', $page_url )
            )
        );

		//wp_redirect(get_admin_url() . 'admin.php?page='.$this->plugin_name.'');
		exit;

	}
	function wmp_post_types() {

		//$default_post_types = array('wmp-program');
		$key= 'wmp-program';
		$label ='Programs';
		register_post_type($key,
				// let's now add all the options for this post type
				array(
					'labels' => array(
						'name' => $label,
						 /* This is the Title of the Group */
						'singular_name' => sprintf(esc_html__('%s Post' ), $label),
						 /* This is the individual type */
						'all_items' => sprintf(esc_html__('All %s' ), $label),
						 /* the all items menu item */ 
						'menu_name' => _x( 'Warmup Reps', 'admin menu'),
		     			'name_admin_bar' => _x('Warmup Reps', 'admin bar'),
						'add_new' => esc_html__('Add New') ,
						 /* The add new menu item */
						'add_new_item' => sprintf(esc_html__('Add New %s' ), $label),
						 /* Add New Display Title */
						'edit' => esc_html__('Edit') ,
						 /* Edit Dialog */
						'edit_item' => sprintf(esc_html__('Edit %s' ), $label),
						 /* Edit Display Title */
						'new_item' => sprintf(esc_html__('New %s' ), $label),
						 /* New Display Title */
						'view_item' => sprintf(esc_html__('View %s' ), $label),
						 /* View Display Title */
						'search_items' => sprintf(esc_html__('Search %s' ), $label),
					) ,
					 /* end of arrays */
					'public' => false,
					'publicly_queryable' => true,
					'exclude_from_search' => false,
					'show_ui' => true,
					'query_var' => true,
					'menu_position' => 7,
					'menu_icon' => 'dashicons-list-view',
					 /* this is what order you want it to appear in on the left hand side menu */
					'rewrite' => false ,
					 /* you can specify its url slug */
					'has_archive' => false,
					'capability_type' => 'post',
					'hierarchical' => true,
					/* the next one is important, it tells what's enabled in the post editor */
					'supports' => array(
						'title',
						'editor',						
						
						'thumbnail',
						'custom-fields'
					),
					// For Gutenberg
					'show_in_rest' => true
				)
			 /* end of options */
			);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Warmup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Warmup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/warmup-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Warmup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Warmup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/warmup-admin.js', array( 'jquery' ), $this->version, false );
		$vars= array('ajax_url'=>admin_url('admin-ajax.php'));
		wp_localize_script( $this->plugin_name, 'wmp_vars', $vars );


	}
	function add_extra_user_fields($user) {

		$categories = get_categories( array('hide_empty' =>false,    'orderby' => 'parent') );
		$get_blog_cat = get_user_meta($user->ID,'user_wmp_blog_category',true);
		//print_r($user);
		$args =array('hide_empty'=>0,'name'=>'blog_category');
		$args['selected'] = $get_blog_cat;
		$dropdown_args = array(
			'hide_empty'       => 0,
			'hide_if_empty'    => false,
			'name'             => 'blog_category',
			'orderby'          => 'name',
			'hierarchical'     => true,
			'show_option_none' => __( 'Select Category' ),
			'selected' => $get_blog_cat,
			'echo' => 0
		);
		$display_blog = get_user_meta($user->ID,'user_display_blog_shortcode',true);
		$get_term_show = get_term_meta($get_blog_cat,'wmp_show_blog_shortcode',true);
		$htmldata = '';
		$htmldata.='<table class="form-table">
			<tr class="user-description-wrap">
			   <th><label for="description">Blog Category</label></th>
			   <td>'.wp_dropdown_categories($dropdown_args).'</td></tr>
			<tr> 
				<th><label for="description">Display Shortcode in Blog</label> </th> 
				<td data-cat="'.$get_term_show.'"> 
					<label for="display_blog_0">  <input type="radio" name="display_blog_shortcode"  value="default"  id="display_blog_0" ';  $htmldata.= ($display_blog=='default'||empty($display_blog)) ? 'checked' : '';  
					$htmldata.= '/> Default </label>
					<label for="display_blog_1">  <input type="radio" name="display_blog_shortcode"  value="yes"  id="display_blog_1" ';  $htmldata.= ($display_blog=='yes') ? 'checked' : '';  
					$htmldata.= '/> Yes </label>
					<label for="display_blog_2">  <input type="radio" name="display_blog_shortcode"  value="no"  id="display_blog_2"'; $htmldata.= ($display_blog=='no') ? 'checked' : ''; 
					$htmldata.='/> No </label>
				</td>
			</tr>
		</table>';
		echo $htmldata;

	}
	function save_extra_user_fields($user_id) {
		$category_id = 0;
		if(isset($_POST['blog_category'])) {
			$category_id = sanitize_text_field($_POST['blog_category']);
			update_user_meta($user_id,'user_wmp_blog_category',$category_id);
		}
		if(isset($_POST['display_blog_shortcode'])) {
			update_user_meta($user_id,'user_display_blog_shortcode',sanitize_text_field($_POST['display_blog_shortcode']));
			update_term_meta($category_id,'wmp_show_blog_shortcode',sanitize_text_field($_POST['display_blog_shortcode']));

		}
		
	}
	function wmp_ajax_import_data() {

		$user_id = get_current_user_id();
		$default_programs = array('Starting Strength','5x5','Max Single','Greyskull LP');
		$default_excercises = array('Squats','Bench Press','Deadlifts','Overhead','Bench','Power Cleans','Barbell Row');

		foreach ($default_programs as $key => $program_name) {
			$args =array('post_title'=>$program_name,'post_type'=>'wmp-program','post_status'=>'publish');
			$post_id = wp_insert_post($args);
		}

		global $wpdb;
		$ex_table = $wpdb->prefix.'wmp_excercise';
		foreach ($default_excercises as $key => $ex_title) {
			$data = array('title'=>$ex_title,'user_id'=>$user_id,'created_at'=>date('Y-m-d H:i:s'));
			$wpdb->insert($ex_table,$data);
		}
		update_option('wmp_default_data_imported','yes');
		wp_send_json(array('status'=>true));
	}
}

