<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://uniquehosting.net/
 * @since      1.0.0
 *
 * @package    WarmupReps
 * @subpackage WarmupReps/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WarmupReps
 * @subpackage WarmupReps/public
 * @author     Chris Newell @ Unique Technologies <chris@uniquetech.net>
 */
class Warmup_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = time(); //$version;
		$this->init_hooks();

	}
	function init_hooks() {

		add_shortcode('show_all_workout',array($this,'render_show_workout_func'));
		add_action('wp_ajax_fetch_all_workouts',array($this,'render_fetch_all_workouts'));
		add_action('wp_ajax_nopriv_fetch_all_workouts',array($this,'render_fetch_all_workouts'));
		//add_action('template_redirect',array($this,'render_template_settings'));
		//add_filter('the_content',array($this,'render_wmp_content'),99999,1);
		add_action('wp_ajax_sava_data_to_blog',[$this,'render_sava_data_to_blog']);
		add_action('wp_ajax_get_warmup_template_html',[$this,'render_get_warmup_template_html']);
		add_action('wp_ajax_nopriv_get_warmup_template_html',[$this,'render_get_warmup_template_html']);
	}
	function render_template_settings() {
	
	 	$page = get_option('wmp_dropdown_page');
		//echo get_option('page_for_posts');
		$is_selected_page = ($page==get_option('page_for_posts')) ? is_home() : is_page($page);
		if(!empty($page)&&$is_selected_page) {
			
		}
	}
	function render_wmp_content($content='') {
		$workout_content ='';
		$get_show_workout = get_option('wmp_show_on_all_pages');
		if($get_show_workout&&is_page()){
			$workout_content = do_shortcode('[show_all_workout]');
		}
		$page = get_option('wmp_dropdown_page');
		if(!empty($page)&&is_page($page)) {
			$workout_content = do_shortcode('[show_all_workout]');
		}

		return $content.$workout_content;
	}
	function render_show_workout_func($atts) {

		$args = shortcode_atts( array(
		'status' => '',
		'post_id' => '',
		), $atts );

		wp_enqueue_script( $this->plugin_name.'-main', plugin_dir_url( __FILE__ ) . 'js/warmup-main.js', array( 'jquery' ), $this->version, false );
		$vars= array('unit_system'=>get_option('wmp_measurement_units'),'bar_type'=>get_option('wmp_bar_type'),'post_id'=>'');
		wp_localize_script( $this->plugin_name.'-main', 'wmp_settings', $vars );

		ob_start();
		include plugin_dir_path( __FILE__ ).'partials/warmup-workout-html.php';

		return ob_get_clean();


	}
	function render_fetch_all_workouts(){
		$wo_data = array();
		$post_id = isset($_REQUEST['post_id']) ? sanitize_text_field($_REQUEST['post_id']) : '';
		$status =false;
		$get_all_programs =$this->get_all_program_details($post_id);
		if($get_all_programs) {
			foreach ($get_all_programs as $key => $pid) {
				$exercises = $this->get_exercise_by_pid($pid,$post_id);
				if(empty($exercises)) continue; 
				$temp = array();
				$temp['title'] = get_the_title($pid);
				$temp['exercises'] = $this->get_all_workout_data($exercises);
				$wo_data[] = $temp;
			}
			$status =true;
		}
		wp_send_json(array('status'=>$status,'data'=>$wo_data));
		//print_r($wo_data);
	}
	function get_all_workout_data($exercises) {
		$ex_data = array();
		if($exercises) {
			foreach ($exercises as $key => $row) {
				$ex_temp =array();
				$ex_temp['name'] = $row->ex_title;
				$ex_temp['max'] = $row->max_weight;
				$ex_temp['workouts'] =json_decode($row->content);
				$ex_data[] = $ex_temp;
			}
		}
		return $ex_data;
	}
	function get_exercise_by_pid($pid=0,$post_id=0) {
		global $wpdb;
		$ex_table = $wpdb->prefix.'wmp_excercise';
		$wo_table =$wpdb->prefix.'wmp_workout';
		$where = !empty($post_id) ? ' and post_id ='.$post_id : '';
		$query =$wpdb->prepare("SELECT wwc.*,we.title as ex_title from $wo_table wwc  inner join $ex_table we on wwc.exercise_id=we.ID  where wwc.program_id = %s $where",$pid);
		$get_all_rows = $wpdb->get_results($query);
		return !empty($get_all_rows) ? $get_all_rows : false;
	}
	function get_all_program_details($post_id=0) {
		global $wpdb;
		$table_name = $wpdb->prefix.'wmp_workout';
		$where = !empty($post_id) ? ' and post_id ='.$post_id : '';
		$query = $wpdb->prepare("SELECT program_id from $table_name where 1= %s $where ",1);
		$get_results = $wpdb->get_results($query,OBJECT_K);
		return !empty($get_results) ? array_keys($get_results) : false;
	}
	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/warmup-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/warmup-public.js', array( 'jquery' ), $this->version, false );


		wp_enqueue_script('wmp-cookie', plugin_dir_url( __FILE__ ) . 'js/jquery.cookie.js', array( 'jquery' ), $this->version, false );

		$user = wp_get_current_user();
		$allowed_roles = array('editor', 'administrator', 'author');
		$current_roles = array_intersect($allowed_roles, $user->roles ) ;
		$is_user_authorized = !empty($current_roles) ? true : false;
		$vars= array('ajax_url'=>admin_url('admin-ajax.php'),'is_user_authorized'=>$is_user_authorized);
		wp_localize_script( $this->plugin_name, 'wmp_vars', $vars );

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_style( 'jquery-ui-slider' );
		$wp_scripts = wp_scripts();
 
		wp_enqueue_style( 'jquery-ui-theme-smoothness', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css' );
		$is_shortcode_enable = $this->is_warmup_shortcode_enable();
		if($is_shortcode_enable) {
			wp_enqueue_script( $this->plugin_name.'-main', plugin_dir_url( __FILE__ ) . 'js/warmup-main.js', array( 'jquery' ), $this->version, false );
			$vars= array('unit_system'=>get_option('wmp_measurement_units'),'bar_type'=>get_option('wmp_bar_type'),'selector'=>get_option('wmp_wrapper_class'));
			wp_localize_script( $this->plugin_name.'-main', 'wmp_settings', $vars );
		}
	}
	function render_sava_data_to_blog() {
		//print_r($_POST);
		$user = wp_get_current_user();
		$get_blog_cat = get_user_meta($user->ID,'user_wmp_blog_category',true);
		$status = false;
		$link ='';
		$categories = '';
		$msg ='<div class="wmp-notice danger">Please try after some time.</div>';
		if(empty($_POST['title'])) {
			wp_send_json(array('status'=>false));
		}
		$content = preg_replace("/\r|\n/", "", $_POST['content']);
		$args = array();
		$args['post_title'] =sanitize_text_field($_POST['title']);
		$args['post_content'] = stripslashes($content);
		if(in_array('administrator', $user->roles)) {
			$args['post_status'] = 'publish';
		}
		if(!empty($get_blog_cat)) {
			$categories = (array) $get_blog_cat;
		}
		if(!empty($_POST['weight'])) {
			$args['post_content'].='<div> <span class="wmp-specs">Weight : </span>'.sanitize_text_field($_POST['weight']).'</div>';
		}
		if(!empty($_POST['bar_type'])) {
			$bar_type= str_replace('bar_type_', '', sanitize_text_field($_POST['bar_type']));
			$args['post_content'].='<div> <span class="wmp-specs">Bar Type : </span>'.$bar_type.'</div>';
		}

		$args['post_category'] = $categories;
		//print_r($args);
		$id = wp_insert_post($args);
		if(!empty($id)) {
			update_post_meta($id,'warmp_post_status','Yes');
			$status = true;
			$link = get_the_permalink($id);
			$msg = '<div class="wmp-notice succuss"> <a href="'.$link.'" target="_blank">Post Link</a> </div>';
		}
		wp_send_json(compact('status','link','msg'));
	}
	function render_get_warmup_template_html() {
		ob_start();
		include plugin_dir_path( __FILE__ ).'partials/warmup-workout-html.php';
		$html_data = ob_get_clean();
		wp_send_json(array('status'=>true,'htmldata'=>$html_data));
	}
	function is_warmup_shortcode_enable() {
		$display_shortcode = get_option('wmp_display_blog');
		if(is_home() && $display_shortcode == 'yes') return true;
		if(is_category()) {
			$category = get_queried_object();
			$status = get_term_meta($category->term_id,'wmp_show_blog_shortcode',true);
			if(($status == 'default' || empty($status)) && $display_shortcode == 'yes') return true; 
			return ($status == 'yes') ? true : false ;
		}
		return false;
	}
}
