<?php
/*
Plugin Name: Zoho Invoice
Plugin URI: http://findingapogee.com/zoho-invoice/
Description: Zoho Invoice can be used to view invoices and client information from inside posts and pages via shortcodes. Example: [invoice customer="CUSTOMERIDHERE"]
Version: 2.6
Author: justMiles
Author URI: http://findingapogee.com
*/


/*  Copyright 2012  Finding Apogee

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
	
*/
// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); 


define( 'zoho_invoice_DIR', WP_PLUGIN_DIR . '/zoho-invoice' );
define( 'zoho_invoice_URL', WP_PLUGIN_URL . '/zoho-invoice' );

function check_zoho($user_id){
	global $wpdb,$ctxpsdb;
	$newuser = get_userdata($user_id);
	$newuser_email = $newuser->user_email;

	// Get Zoho API Keys
	$options = get_option('se_options');
	$authtoken = $options['zoho_auth_token'];
	$apikey = $options['zoho_api_key'];

	// Search customers by note (must put email address in notes on invoice.zoho.com)
	$customersapi = 'http://invoice.zoho.com/api/view/search/customers/';
	$customersxml = simplexml_load_file($customersapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey.'&searchtext='.$newuser_email);
	$zoho_id = $customersxml->Customers->Customer;

	for ($i = 0; $i < sizeof($zoho_id); $i++) {
		$CustomerID=$zoho_id[$i]->CustomerID;
		$group_id = $wpdb->get_var($wpdb->prepare('SELECT `ID` FROM `'.$ctxpsdb->groups.'` WHERE `group_description` = %s',$CustomerID));
		$groupjoin = CTXPS_Queries::add_membership_with_expiration($user_id,$group_id);
	}

}

	
//New registrar
$options = get_option('se_options');
if ($options['use_security'] == "1") {
	add_action('user_register','check_zoho');
}

if (!class_exists("zoho_invoice")) :

class zoho_invoice {
	var $settings;
	var $addpage;
	
	function __construct() {	
		if (!class_exists("zoho_invoice_Settings"))
			require('invoice-ex-settings.php');
		$this->settings = new zoho_invoice_Settings();	

		add_action('admin_init', array(&$this,'admin_init') );
		add_action('init', array(&$this,'init') );
		add_action('admin_menu', array(&$this,'admin_menu') );
		
		register_activation_hook( __FILE__, array(&$this,'activate') );
		register_deactivation_hook( __FILE__, array(&$this,'deactivate') );
		add_shortcode('invoice', array($this, 'customerInvoice'));
		add_shortcode('listcustomers', array($this, 'listCustomers'));
	}
	

	
	function listCustomers() {
		$options = get_option('se_options');
    	extract(shortcode_atts(array(
    		"customer" => ''
    	), $atts));  
		$CustomerID = $customer;
		$authtoken = $options['zoho_auth_token'];
		$apikey = $options['zoho_api_key'];
		$buffer = $options['buffer'];
		
		// Customers
		$customersapi = 'https://invoice.zoho.com/api/customers';
		$customersxml = simplexml_load_file($customersapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey);
		$customers = $customersxml->Customers->Customer;
		
		$return .= $buffer;
		for ($i = 0; $i < sizeof($customers); $i++) {
		   $CustomerName=$customers[$i]->Name;
		   $CustomerID=$customers[$i]->CustomerID;
		
		    $return .= 'CustomerName: ';
		    $return .= $CustomerName;
		    $return .= '<br>';
		    $return .= 'CustomerID: ';
		    $return .= $CustomerID;
		    $return .= '<br>';
		    $return .= $buffer;
		}
		return $return;
	}
	
	function customerInvoice($atts, $content = null) {
		$options = get_option('se_options');
    	extract(shortcode_atts(array(
    		"customer" => ''
    	), $atts));  
		$CustomerID = $customer;
		$authtoken = $options['zoho_auth_token'];
		$apikey = $options['zoho_api_key'];
		$buffer = $options['buffer'];
		$invoiceapi = 'https://invoice.zoho.com/api/invoices/customer/'.$CustomerID;
		$invoicexml = simplexml_load_file($invoiceapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey);
		if ($invoicexml) {
		} else {
			return '<b>An error has occured while retrieving your invoices. Please try again later.</b>';
		}
		
		$invoice = $invoicexml->Invoices->Invoice;

		$TotalSpent = 0;
		$return .= $buffer;
		for ($i = 0; $i < sizeof($invoice); $i++) {
		
			// Get main invoices
		   $InvoiceNumber=$invoice[$i]->InvoiceNumber;
		   $InvoiceID=$invoice[$i]->InvoiceID;
		   $Status=$invoice[$i]->Status;
		   $Total= floatval($invoice[$i]->Total);
		   $InvoiceDate=$invoice[$i]->InvoiceDate;
		   
		 // Collect data from inside invoices ItemName
		 
			
		   // Show invoices
      // Draft Invoice
			if ($Status == "1") {
				$return .= '<span style="font-weight:bold">Draft Invoice: ';
				$return .= '<a href="https://invoice.zoho.com/api/invoices/pdf'.$pdfapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey.'&InvoiceID='.$InvoiceID.'"target="_blank">'.$InvoiceNumber.'</a></span>';
				$return .= '<br>Total: $'.$Total.'<br> Status: <span>';
				$return .= 'You have not been billed for this invoice.';
      } // Open Invoice
      else if ($Status == "2") {
				$return .= '<span style="font-weight:bold">Invoice  Number: ';
        $return .= '<a href="https://invoice.zoho.com/api/invoices/pdf'.$pdfapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey.'&InvoiceID='.$InvoiceID.'"target="_blank">'.$InvoiceNumber.'</a></span>';
        $return .= '<br>Total: $'.$Total.'<br> Status: <span style="color:red">Open</span> <br>Invoice Date: '.$InvoiceDate;
      } // 
      else if ($Status == "3") {
				$return .= '<span style="font-weight:bold">Invoice Number: ';
        $return .= '<a href="https://invoice.zoho.com/api/invoices/pdf'.$pdfapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey.'&InvoiceID='.$InvoiceID.'"target="_blank">'.$InvoiceNumber.'</a></span>';
				$return .= '<br>Total: $'.$Total.'<br> Status: <span style="color:green">Paid</span> <br>Invoice Date: '.$InvoiceDate;
				$TotalSpent = $Total + $TotalSpent;
			} 
      else if ($Status == "4") {
				$return .= '<span style="font-weight:bold">Invoice Number: ';
				$return .= '<a href="https://invoice.zoho.com/api/invoices/pdf'.$pdfapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey.'&InvoiceID='.$InvoiceID.'"target="_blank">'.$InvoiceNumber.'</a></span>';
				$return .= '<br>Total: $'.$Total.'<br> Status: <span style="color:red" font-weight="bold">Overdue</span> <br>Invoice Date: '.$InvoiceDate;
			} 
      else if ($Status == "5") {
				$return .= '<span style="font-weight:bold">Invoice Number: ';
				$return .= '<a href="https://invoice.zoho.com/api/invoices/pdf'.$pdfapi.'?authtoken='.$authtoken.'&scope=invoiceapi&apikey='.$apikey.'&InvoiceID='.$InvoiceID.'"target="_blank">'.$InvoiceNumber.'</a></span>';
				$return .= '<br>Total: $'.$Total.'<br> Status: Void <br>Invoice Date: '.$InvoiceDate;
			}
			$return .= '<br>'.$buffer;
			$return = $return;
			
		}
		return $return;
	}
	
	
	function activate($networkwide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_activate();
				}
				switch_to_blog($old_blog);
				return;
			}	
		} 
		$this->_activate();		
	}

	function deactivate($networkwide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_deactivate();
				}
				switch_to_blog($old_blog);
				return;
			}	
		} 
		$this->_deactivate();		
	}	
	
	function _activate() {}
	
	function _deactivate() {}
	
	function admin_init() {
		$this->settings->admin_init();
	}

	function init() {
		load_plugin_textdomain( 'zoho_invoice', zoho_invoice_DIR . '/lang', basename( dirname( __FILE__ ) ) . '/lang' );
	}

	function admin_menu() {
	
		// Add a new submenu
		$this->addpage = add_options_page(	
			__('Zoho Invoice', 'zoho_invoice'), __('Zoho Invoice', 'zoho_invoice'), 
			'administrator', 'zoho_invoice', 
			array($this,'add_example_page') );
		add_action("admin_head-$this->addpage", array($this,'admin_head'));
		add_action("load-$this->addpage", array($this, 'on_load'));
		add_action("admin_print_styles-$this->addpage", array($this,'admin_styles'));
		add_action("admin_print_scripts-$this->addpage", array($this,'admin_scripts'));
	}

	function admin_head() {
	}
	
	
	function admin_styles() {
	}
	
	function admin_scripts() {
	}
	
	function on_load() {	
	}
	
	
	function add_example_page() {
		include('invoice-ex-page.php');
	
	}

	function print_example($str, $print_info=TRUE) {
		if (!$print_info) return;
		__($str . "<br/><br/>\n", 'zoho_invoice' );
	}

	function javascript_redirect($location) {
		// redirect after header here can't use wp_redirect($location);
		?>
		  <script type="text/javascript">
		  <!--
		  window.location= <?php echo "'" . $location . "'"; ?>;
		  //-->
		  </script>
		<?php
		exit;
	}

} // end class
endif;

global $zoho_invoice;
if (class_exists("zoho_invoice") && !$zoho_invoice) {
    $zoho_invoice = new zoho_invoice();	
}	
?>