<?phpif (!function_exists('is_admin')) {    header('Status: 403 Forbidden');    header('HTTP/1.1 403 Forbidden');    exit();}if (!class_exists("zoho_invoice_Settings")) :class zoho_invoice_Settings {		function __construct() {		}		function admin_init() {		register_setting( 'zoho_invoice_options', 'se_options', array($this, 'sanitize_theme_options') );		add_settings_section('shiba_main', 'Zoho Invoice Settings', 			array($this, 'main_section_text'), 'example_settings_page');		add_settings_field('zoho_api_key', 'Zoho API Key', 			array($this, 'render_zoho_api_key'), 'example_settings_page', 'shiba_main');		add_settings_field('zoho_auth_token', 'Zoho Auth Token', 			array($this, 'render_zoho_auth_token'), 'example_settings_page', 'shiba_main');								add_settings_field('buffer', 'Optional: Markup string', 			array($this, 'render_buffer'), 'example_settings_page', 'shiba_main');				add_settings_field('use_security', 'Optional: Security by Contexture',
			array($this, 'render_use_security'), 'example_settings_page', 'shiba_main');				add_settings_field('use_stripe', 'Optional: Security by Contexture',
		
			array($this, 'render_use_stripe'), 'example_settings_page', 'shiba_main');	}	function main_section_text() {		echo '<p>This plugin enables users to view invoices via shortcodes. Use: [invoice customer="CUSTOMERIDHERE"]</p>';	}		function render_zoho_api_key() { 		$options = get_option('se_options');		?>        <input id="zoho_api_key" style="width:50%;"  type="text" name="se_options[zoho_api_key]" value="<?php echo $options['zoho_api_key']; ?>" />			<?php 		echo '<p>You can obtain your API key <a href="https://zapi.zoho.com/apigen.do" target="_blank">here</a>.</p>';	}		function render_zoho_auth_token() { 		$options = get_option('se_options');		?>        <input id="zoho_auth_token" style="width:50%;"  type="text" name="se_options[zoho_auth_token]" value="<?php echo $options['zoho_auth_token']; ?>" />			<?php 		echo '<p>You can obtain your Auth token <a href="https://accounts.zoho.com/apiauthtoken/create?SCOPE=ZohoInvoice/invoiceapi" target="_blank">here</a>.</p>';	}		function render_buffer() { 		$options = get_option('se_options');		?>        <input id="buffer" style="width:50%;"  type="text" name="se_options[buffer]" value="<?php echo $options['buffer']; ?>" />			<?php 		echo '<p>This option enables visual aid between invoices. HTML and CSS are welcome here. This string executes once to start then again after each listing.';		echo '<blockquote>- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>';		echo 'Invoice Number: INV-XXX<br>';		echo 'Total: $256.88<br>';		echo 'Status: Paid <br>';		echo 'Invoice Date: 1911-11-11<br>';		echo '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br></blockquote></p>';		echo '<blockquote><h4>Available Shortcodes:</h4>';		echo '<b>[listcustomers]</b><br> Shows a complete list of customers and their CustomerID.<br><br>';		echo '<b>[invoice customer="000000000000000000"]</b><br> Lists specified customer\'s invoices. Requires CustomerID.</blockquote>';				}			function render_use_security() {
	
		$options = get_option('se_options');
	
		?>
	
        <input id="use_security" style="width:5%;"  type="text" name="se_options[use_security]" value="<?php echo $options['use_security']; ?>" />			
			<?php 
	
			echo 'Enter a 1 or 0. Determine if you want to associate new users to the their respective CompanyID(s) by email. Requires the Security by Contexture plugin.';
		
			
		}				function render_use_stripe() { 
		
			$options = get_option('se_options');
			?>
			        <input id="use_stripe" style="width:50%;"  type="text" name="se_options[use_stripe]" value="<?php echo $options['use_stripe']; ?>" />	
					<?php 
					echo '<p>This option enables visual aid between invoices. HTML and CSS are welcome here. This string executes once to start then again after each listing.';
				}	function sanitize_theme_options($options) {		$options['zoho_api_key'] = stripcslashes($options['zoho_api_key']);		return $options;	}} // end classendif;?>