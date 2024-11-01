<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	$default = array(
        'ID' => 0,
        'title'=>'',
        'status'=>'',
    );
    global $wpdb;
    $table_name = $wpdb->prefix . 'wmp_excercise';
	if(!empty($_POST)){
		if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
          //  $item = shortcode_atts($default, $_REQUEST);
            $item['title'] = isset($_REQUEST['title']) ? sanitize_text_field($_REQUEST['title']) : '';
            $item['status'] = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '';
            $item['ID'] = isset($_REQUEST['excercise']) ? sanitize_text_field($_REQUEST['excercise']) : $item['ID'];
            $item['created_at'] = date('Y-m-d H:i:s');
            if(!empty($item['ID']))
            {
            	$result = $wpdb->update($table_name, $item, array('ID' => $item['ID']));
                if ($result) {
                    $message = __('Item was successfully updated', 'custom_table_example');
                } else {
                    $notice = __('There was an error while updating item', 'custom_table_example');
                }
            }
            else {
                $item['user_id'] = get_current_user_id();
            	$result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Excercise was successfully saved', 'custom_table_example');
                } else {
                   $notice = __('There was an error while saving item', 'custom_table_example');
                }
            }
		}
	}
	$item = $default;
    if (isset($_REQUEST['excercise'])) {
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", sanitize_text_field($_REQUEST['excercise'])), ARRAY_A);
        if (!$item) {
            $item = $default;
            $notice = __('Item not found', 'custom_table_example');
        }
    }
   // print_r($item);
    

	$wmp_ex = new Wmp_Excercise_List_Table();
	$wmp_ex->prepare_items();
	//$message = '';
	//echo $wmp_ex->current_action();
    if ('delete' === $wmp_ex->current_action()) {
        $message =  sprintf(__('Items deleted: %d', 'custom_table_example'), count($_REQUEST['id']));
    }
    $page_sub_title = ('edit'===$wmp_ex->current_action()) ? 'Edit' : 'Add';
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

	<div class="postbox" id="poststuff">
		<div class="postbox-header"> <h2 class="hndle ui-sortable-handle"><?php echo $page_sub_title; ?> Excercise</h2>  </div>
		<div class="inside">
     <form id="form" method="POST" class="form_provider">
			<label> <?php echo $page_sub_title; ?> Excercise</label>
			 <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
			 <input type="hidden" name="ID"  value="<?php echo esc_attr($item['id']);?>">
			<input type="text" name="title" id="title" value="<?php echo esc_attr($item['title']);?>">
			<button type="submit" class="button button-primary button-large">Save</button>
		</form>
		</div>
	</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="excecise-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
		<?php 
			if(isset($_REQUEST['post_type'])){
				echo '<input type="hidden" name="post_type" class="post_type_page" value="'.esc_attr($_REQUEST['post_type']).'">';
			}
		?>
		<!-- Now we can render the completed list table -->
		<?php $wmp_ex->display() ?>
	</form>

</div>
