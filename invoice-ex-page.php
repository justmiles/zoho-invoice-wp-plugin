<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}


if ( ! current_user_can('update_plugins') )
	wp_die(__('You are not allowed to update plugins on this blog.'));

$messages[1] = __('Zoho Invoice settings updated.', 'zoho_invoice');

if ( isset($_GET['message']) && (int) $_GET['message'] ) {
	$message = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

$title = __('Zoho Invoice', 'zoho_invoice');
?>
<div class="wrap">   
    <?php screen_icon(); ?>
    <h2><?php echo esc_html( $title ); ?></h2>

	<?php
		if ( !empty($message) ) : 
		?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php 
		endif; 
	?>

    <form method="post" action="options.php">
		<?php 
			settings_fields('zoho_invoice_options'); 
			do_settings_sections('example_settings_page'); 
		?>

		<p>
  		<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
        
		</p>
    </form>

</div>