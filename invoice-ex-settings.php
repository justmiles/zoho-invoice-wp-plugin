<?php
			array($this, 'render_use_security'), 'example_settings_page', 'shiba_main');
		
			array($this, 'render_use_stripe'), 'example_settings_page', 'shiba_main');
	
		$options = get_option('se_options');
	
		?>
	
        <input id="use_security" style="width:5%;"  type="text" name="se_options[use_security]" value="<?php echo $options['use_security']; ?>" />	
			<?php 
	
			echo 'Enter a 1 or 0. Determine if you want to associate new users to the their respective CompanyID(s) by email. Requires the Security by Contexture plugin.';
		
			
		}
		
			$options = get_option('se_options');
			?>
			        <input id="use_stripe" style="width:50%;"  type="text" name="se_options[use_stripe]" value="<?php echo $options['use_stripe']; ?>" />	
					<?php 
					echo '<p>This option enables visual aid between invoices. HTML and CSS are welcome here. This string executes once to start then again after each listing.';
				}