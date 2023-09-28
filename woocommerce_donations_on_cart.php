<?php
/*
Plugin Name: WooCommerce Donations Plugin
Plugin URI: http://albdesignweb.com/demos/
Description: This plugin adds a donation field on the cart page.
Version: 1.95
Author: Albdesign
WC tested up to: 4.0.1
Author URI: http://albdesignweb.com/
Text Domain: albdesign-wc-donations
*/


define( 'ALBDESIGN_WC_DONATIONS_URL', plugin_dir_url( __FILE__ ));

//load translatable files
add_action('plugins_loaded', 'albdesign_wc_donations_language');
function albdesign_wc_donations_language() {
	load_plugin_textdomain( 'albdesign-wc-donations', false, dirname( plugin_basename( __FILE__ ) ) . '/language/' );
}


//Load JS/CSS for tabs on admin area 
add_action( 'admin_enqueue_scripts', 'albdesign_wc_donation_register_scripts_styles_admin' );
function albdesign_wc_donation_register_scripts_styles_admin(){
	
	
	$screen       = get_current_screen();
	$screen_id    = $screen ? $screen->id : '';	
	
	
	//bail out if not on our plugin screen
	if($screen_id != 'woocommerce_page_donnation-settings-page' ){
		return ;
	}
	
	wp_enqueue_style( 'albdesign-wc-donations-tabs-css-admin',ALBDESIGN_WC_DONATIONS_URL.'assets/tabs/css/tabs.css' );
	

	wp_enqueue_style( 'albdesign-wc-donations-select2-css-admin','//cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css' );

	wp_register_script( 'albdesign-wc-donations-select2-js-admin','//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js',array( 'jquery' ),'1.0.0',true);
	wp_enqueue_script('albdesign-wc-donations-select2-js-admin');
	
	wp_register_script( 'albdesign-wc-donations-tabs-js-admin',ALBDESIGN_WC_DONATIONS_URL.'assets/tabs/js/cbpFWTabs.js',array( 'jquery' ),'1.0.0',true);
	wp_enqueue_script( 'albdesign-wc-donations-tabs-js-admin' );
	wp_localize_script( 'albdesign-wc-donations-tabs-js-admin', 'albdesignwcdonations',
		array( 
			'remove_campaign'   		=> __('Remove campaign','albdesign-wc-donations'),
			'enter_campaign_name'   	=> __('Enter campaign name','albdesign-wc-donations'),
			'enter_predefined_value'   	=> __('Enter predefined value','albdesign-wc-donations'),
			'remove_predefined_value'	=> __('Remove predefined value','albdesign-wc-donations'),
			'order_text'				=> __('Order','albdesign-wc-donations'),
			'no_orders_found'			=> __('No orders found','albdesign-wc-donations'),
			
		)
	);	
	
}

//Load frontend CSS files 
add_action( 'wp_enqueue_scripts', 'albdesign_wc_donation_register_scripts_styles_frontend' );
function albdesign_wc_donation_register_scripts_styles_frontend() {
    wp_enqueue_style( 'albdesign-wc-donation-frontend', ALBDESIGN_WC_DONATIONS_URL . 'assets/css/frontend.css');
}





// add the subpages to Woocommerce on admin area 
add_action('admin_menu', 'register_woocommerce_donation_submenu');

function register_woocommerce_donation_submenu() {
	add_submenu_page( 'woocommerce', 'Donations', 'Donations', 'manage_options', 'donnation-settings-page', 'woocommerce_donation_submenu_callback' ); 
	
}

function sanitize_array( $input ) {
	// Initialize the new array that will hold the sanitize values
	$new_input = array();
	// Loop through the input and sanitize each of the values
	foreach ( $input as $key => $val ) {
		
		$new_input[ $key ] = sanitize_text_field( esc_html($val ));
	}
	return $new_input;
}

function woocommerce_donation_submenu_callback() {


	//Create new product STARTS
	if(isset($_POST['woocommerce_donations_add_new_product_form'])){

			if(sanitize_text_field($_POST['woocommerce_donations_new_product_title'])!=""){
				$new_product_title = sanitize_text_field($_POST['woocommerce_donations_new_product_title']);
			}
		
						$add_new_donation_product_array = array(

								  'post_title'     => $new_product_title ,
								  'post_status'    => 'publish' , 
								  'post_type'      => 'product'  

								);  
						$id_of_new_donation_product = wp_insert_post($add_new_donation_product_array);

						
						
						//update_post_meta($id_of_new_donation_product , '_visibility','hidden');		
						update_post_meta($id_of_new_donation_product , '_sku','checkout-donation-product');		
						update_post_meta($id_of_new_donation_product , '_tax_class','zero-rate');		
						update_post_meta($id_of_new_donation_product , '_tax_status','none');		
						update_post_meta($id_of_new_donation_product , '_sold_individually','yes');		
						update_post_meta($id_of_new_donation_product , '_virtual','yes');		

	}
	//Create new product ENDS
	
	
	//Save how the donation will be displayed START 
	
	if(isset($_POST['woocommerce_donations_display_donation_field_as_form'])){
		
		update_option( 'albdesign_woocommerce_donations_show_donation_field_as', sanitize_text_field($_POST['woocommerce_donations_display_donation_field_as']));
		
		if(sanitize_text_field($_POST['woocommerce_donations_display_donation_field_as']) === 'dropdown' ){
			
			
			
			//save the predefined values 
			if(isset($_POST['albdesign_woocommerce_donations_predefined_donation_value'])){
				update_option( 'albdesign_woocommerce_donations_predefined_values', sanitize_array($_POST['albdesign_woocommerce_donations_predefined_donation_value'] ));
			}else{
				update_option( 'albdesign_woocommerce_donations_predefined_values', array() );
			}
		}else{
			update_option( 'albdesign_woocommerce_donations_predefined_values', array() );
		}
		
		
		//currency symbol 
		update_option('albdesign_woocommerce_donations_show_currency_field',sanitize_text_field($_POST['woocommerce_donations_show_currency_symbol']));
		
	}
	
	$get_saved_show_currency_symbol = get_option('albdesign_woocommerce_donations_show_currency_field');
	$get_saved_display_donation_as = get_option ( 'albdesign_woocommerce_donations_show_donation_field_as' );
	$get_saved_predefined_donation_value_options = get_option ( 'albdesign_woocommerce_donations_predefined_values' );
	
	
	//Save how the donation will be displayed ENDS
	

	if(isset($_POST['woocommerce_donations_select_product_form'])){

		if ( !isset($_POST['woocommerce_donations_select_product_nonce_field']) || !wp_verify_nonce($_POST['woocommerce_donations_select_product_nonce_field'],'woocommerce_donations_select_product_nonce') ){
			
		   _e('Sorry, your nonce did not verify.','albdesign-wc-donations');
		   
		   exit;
		}
		else
		{
			//PROCESS FORM DATA 
			
			//selected an existing product
			if(sanitize_text_field($_POST['woocommerce_donations_select_product_id'])!=""){
				
				//save selected product ID
				$donation_product_new_option_value = sanitize_text_field($_POST['woocommerce_donations_select_product_id']) ;

				if ( get_option( 'woocommerce_donations_product_id' ) !== false ) {
					update_option( 'woocommerce_donations_product_id', $donation_product_new_option_value );
				} else {
					// there is still no options on the database
					add_option( 'woocommerce_donations_product_id', $donation_product_new_option_value, null, 'no' );
				}

			}
		}
		
		
	}


	
	//save string translations 
	if(isset($_POST['woocommerce_donations_save_string_translation'])){
		if($_POST['woocommerce_donations_translations']['use_custom_translation']){
			update_option('woocommerce_donations_translations',sanitize_array($_POST['woocommerce_donations_translations']));	
		}
	}
	$get_saved_translation = get_option('woocommerce_donations_translations');

	
	//save campaigns settings 
	if(isset($_POST['woocommerce_donations_campaigns_form'])){
		
		$array_for_campaigns = array();
		
		if(isset($_POST['albdesign_woocommerce_donations_campaigns']['enable_campaign_support'])){
			$array_for_campaigns['enable_campaign_support'] = sanitize_text_field($_POST['albdesign_woocommerce_donations_campaigns']['enable_campaign_support']);
		}
		
		if(isset($_POST['albdesign_woocommerce_donations_campaigns']['campaign_list'])){
			$array_for_campaigns['campaign_list'] = sanitize_array($_POST['albdesign_woocommerce_donations_campaigns']['campaign_list']);
		}
		
		update_option('albdesign_woocommerce_donations_campaigns',$array_for_campaigns);

	}
	$get_saved_campaign_options = get_option ( 'albdesign_woocommerce_donations_campaigns' );

	

	?>

		<div class="albdesign_tabs container">

			<section>
			
				<div class="tabs tabs-style-flip">
					<h3><?php _e('Donation options page','albdesign-wc-donations'); ?></h3>
					<nav>
						<ul>
							
							<li><a href="#section-settings" ><span><?php _e('Settings','albdesign-wc-donations');?></span></a></li>
							<li><a href="#section-campaign" ><span><?php _e('Campaign','albdesign-wc-donations');?></span></a></li>
							<li><a href="#section-reports" ><span><?php _e('Reports','albdesign-wc-donations');?></span></a></li>
							
						</ul>
					</nav>
					<div class="content-wrap">

						<section id="section-settings">	
							<form  action="" method="post">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row" class="titledesc"><label for="woocommerce_donations_select_product_id"><?php _e('Donation Product','albdesign-wc-donations'); ?></label></th>
											<td class="forminp">
												<?php _e('Select existing product','albdesign-wc-donations'); ?>
												
												
													<select class="albdesign-wc-donation-product-search " name="woocommerce_donations_select_product_id" id="woocommerce_donations_select_product_id" data-albdesign-wc-donation-product-nonce="<?php echo wp_create_nonce( 'search-products' );?>">
													
													<?php if(get_option('woocommerce_donations_product_id',true)){ ?>
														<option value="<?php echo get_option('woocommerce_donations_product_id',true); ?>"> <?php echo get_the_title(get_option('woocommerce_donations_product_id',true)) ;?> </option>
													<?php }	?>
													
												
													</select>
							
												<p class="description"><?php _e('A non taxable,not shippable product needs to exists in WooCommerce before using donations','albdesign-wc-donations'); ?></p>
											</td>
										</tr>			
									</tbody>
								</table>		
								<p class="submit">
									<input type="hidden" name="woocommerce_donations_select_product_form">
										<?php wp_nonce_field('woocommerce_donations_select_product_nonce','woocommerce_donations_select_product_nonce_field'); ?>
									<input name="save" class="button-primary" type="submit" value="<?php _e('Save changes','albdesign-wc-donations'); ?>">        			        
								</p>
							</form>	

							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row" class="titledesc"><label for="woocommerce_donations_add_new_product_form"><?php _e('New donation product','albdesign-wc-donations'); ?></label></th>
										<td class="forminp">
											<form  action="" method="post">
												<?php _e('New product title','albdesign-wc-donations'); ?> <input name="woocommerce_donations_new_product_title" class="text" type="text" >
												<input name="woocommerce_donations_add_new_product_form" class="button button-primary" type="submit" value="<?php _e('Create Product','albdesign-wc-donations'); ?>">
												<p class="description"><?php _e('A non taxable,not shippable product will be created and you can select it on the Donation Product  above afterward. <br> Keep in mind that the new product title will be visible on the cart, the checkout page and invoice so name it something like "DONATIONS" .','albdesign-wc-donations'); ?></p>
											</form>
										</td>
									</tr>			
								</tbody>
							</table>		

							
							<h3><?php _e('Donation field','albdesign-wc-donations'); ?></h3>
							<form  action="" method="post">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row" class="titledesc"><label for="woocommerce_donations_display_donation_field_as"><?php _e('Display as','albdesign-wc-donations'); ?></label></th>
											<td class="forminp">
												
												<select name="woocommerce_donations_display_donation_field_as" id="woocommerce_donations_display_donation_field_as" class="text" style="width:80%">
													<option value="text" <?php selected($get_saved_display_donation_as , 'text') ;?>><?php _e('Let the customer choose how much to donate','albdesign-wc-donations'); ?></option>
													<option value="dropdown"  <?php selected($get_saved_display_donation_as , 'dropdown') ;?>><?php _e('Dropdown with predefined values','albdesign-wc-donations'); ?></option>
												</select>

												<p class="description"><?php _e('Select how the donation field will be displayed. Free value input form or as dropdown of predefined values','albdesign-wc-donations'); ?></p>
												
												<div id="woocommerce_donations_display_donation_field_predefined_values_container" style="<?php if(get_option('albdesign_woocommerce_donations_show_donation_field_as',true)!='dropdown'){ echo 'display:none'; } ?>">
													
													<div class="input_predefined_donation_values_fields_wrap">
														
														<?php 
															//list existing predefined donation values  if any 
															if(is_array($get_saved_predefined_donation_value_options)){
																foreach($get_saved_predefined_donation_value_options as $single_predefined_value){ ?>
																	<div>
																		<input type="text" name="albdesign_woocommerce_donations_predefined_donation_value[]" placeholder="<?php _e('Enter predefined value','albdesign-wc-donations'); ?>" value="<?php echo $single_predefined_value;?>"> <a href="#" class="albdesign_woocommerce_donations_campaigns_remove_field_button"><?php _e('Remove value','albdesign-wc-donations'); ?></a>
																	</div>
																<?php 
																}
															}
														?>
														
													</div>			
													
													<br><br>
													
													<button class="albdesign_woocommerce_donations_campaigns_add_predefined_field_button"><?php _e('Add new value','albdesign-wc-donations'); ?></button>
												</div>												
												
											</td>
										</tr>		

										<tr valign="top">
											<th scope="row" class="titledesc"><label for="woocommerce_donations_show_currency_symbol"><?php _e('Show currency symbol','albdesign-wc-donations'); ?></label></th>
											<td class="forminp">
												
												<select name="woocommerce_donations_show_currency_symbol" id="woocommerce_donations_show_currency_symbol" class="text" style="width:80%">
													<option value="no" <?php selected($get_saved_show_currency_symbol , 'no') ;?>><?php _e('No','albdesign-wc-donations'); ?></option>
													<option value="before"  <?php selected($get_saved_show_currency_symbol , 'before') ;?>><?php _e('Yes, before the field','albdesign-wc-donations'); ?></option>
													<option value="after"  <?php selected($get_saved_show_currency_symbol , 'after') ;?>><?php _e('Yes, after the field','albdesign-wc-donations'); ?></option>
												</select>

												<p class="description"><?php _e('Should the currency symbol be visible. If yes , select where','albdesign-wc-donations'); ?></p>
										
											</td>
										</tr>	

									</tbody>
								</table>		
								<p class="submit">
									<input type="hidden" name="woocommerce_donations_display_donation_field_as_form">
									<input name="save" class="button-primary" type="submit" value="<?php _e('Save changes','albdesign-wc-donations'); ?>">        			        
								</p>
							</form>	
							
							<h3><?php _e('String translations','albdesign-wc-donations'); ?></h3>

							<form  action="" method="post">
								<table class="form-table">
									
										<tbody>
										
											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_use_custom_translation"><?php _e('Use custom text','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<select name="woocommerce_donations_translations[use_custom_translation]" class="text" type="text" style="width:80%">
														<option value="no" <?php selected($get_saved_translation['use_custom_translation'],'no');?>><?php _e('No, use default strings','albdesign-wc-donations'); ?> </option>
														<option value="yes" <?php selected($get_saved_translation['use_custom_translation'],'yes');?>><?php _e('Yes ,I want to use the texts below instead of default text of plugin','albdesign-wc-donations'); ?></option>
													</select>
													<p class="description"><?php _e('Select "Yes" if you want to use the strings below instead of default plugin strings . Supports HTML','albdesign-wc-donations'); ?> </p>
												</td>
											</tr>				
										
											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_single_product_text"><?php _e('Single product','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[single_product_text]" class="text" type="text" style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('single_product_text');?>">
													<p class="description"><?php _e('Text "Enter the amount you wish to donate" located on single product page . Supports HTML','albdesign-wc-donations'); ?></p>
												</td>
											</tr>			
										
											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_single_product_text"><?php _e('Single product confirmation','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[donation_added_single_product_text]" class="text" type="text" style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('donation_added_single_product_text');?>">
													<p class="description"><?php _e('Text "Donation added" located on single product page . Shown once the customer adds the donation . Supports HTML ','albdesign-wc-donations'); ?></p>
												</td>
											</tr>				

											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_cart_header_text"><?php _e('Cart Text','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[cart_header_text]" class="text" type="text"  style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('cart_header_text');?>">
													<p class="description"><?php _e('Text "Add a donation to your order" located on cart before "Add Donation" . Supports HTML','albdesign-wc-donations'); ?></p>
												</td>
											</tr>
											
											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_cart_button_text"><?php _e('Cart Button','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[cart_button_text]" class="text" type="text"  style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('cart_button_text');?>">
													<p class="description"><?php _e('Text for button "Add Donation" located on cart.','albdesign-wc-donations'); ?></p>
												</td>
											</tr>			

											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_checkout_title_text"><?php _e('Checkout Title Text','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[checkout_title_text]" class="text" type="text"  style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('checkout_title_text');?>">
													<p class="description"><?php _e('Add a donation to your order" header text located on checkout . Shown on checkout when user has not added a donation . Supports HTML','albdesign-wc-donations'); ?></p>
												</td>
											</tr>				
											
											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_checkout_text"><?php _e('Checkout  Text','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[checkout_text]" class="text" type="text"  style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('checkout_text');?>">
													<p class="description"><?php _e('If you wish to add a donation you can do so on the " text located on checkout . Shown on checkout when user has not added a donation . Supports HTML ','albdesign-wc-donations'); ?></p>
												</td>
											</tr>				
											
											<tr valign="top">
												<th scope="row" class="titledesc"><label for="woocommerce_donations_checkout_text"><?php _e('Select Campaign Text','albdesign-wc-donations'); ?></label></th>
												<td class="forminp">
													<input name="woocommerce_donations_translations[select_campaign_text]" class="text" type="text"  style="width:80%" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings_admin('select_campaign_text');?>">
													<p class="description"><?php _e('Text shown on the top of the dropdown of campaigns','albdesign-wc-donations'); ?></p>
												</td>
											</tr>											
	
										</tbody>
									
								</table>		
								
								<p class="submit">
									<input name="woocommerce_donations_save_string_translation" class="button-primary" type="submit" value="<?php _e('Save translations','albdesign-wc-donations'); ?>">        			        
								</p>
							
							</form>							
						</section> <!-- general tab -->

						<section id="section-campaign">	
							<form  action="" method="post">
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row" class="titledesc"><label for="albdesign_woocommerce_donations_campaigns"><?php _e('Enable Campaigns','albdesign-wc-donations'); ?></label></th>
											<td class="forminp">
												
												<select name="albdesign_woocommerce_donations_campaigns[enable_campaign_support]" id="woocommerce_donations_enable_campaign_support" class="select email_type">
													<option value="no" <?php selected($get_saved_campaign_options['enable_campaign_support'],'no');?>><?php _e('No','albdesign-wc-donations'); ?></option>
													<option value="yes" <?php selected($get_saved_campaign_options['enable_campaign_support'],'yes');?>><?php _e('Yes','albdesign-wc-donations'); ?></option>
												</select>

												<p class="description"><?php _e('If enabled the customer can select for which campaign-cause the donation needs to go','albdesign-wc-donations'); ?></p>

											</td>
										</tr>			
										
										<tr valign="top">
											<th scope="row" class="titledesc"><label for="albdesign_woocommerce_donations_campaigns"><?php _e('Campaigns','albdesign-wc-donations'); ?></label></th>
											<td class="forminp">
											
												<div class="input_fields_wrap">
												
													<?php 
														//list existing campaigns if any 
														if(isset($get_saved_campaign_options['campaign_list'])){
															foreach($get_saved_campaign_options['campaign_list'] as $single_campaign){ ?>
																<div>
																	<input type="text" name="albdesign_woocommerce_donations_campaigns[campaign_list][]" placeholder="<?php _e('Enter campaign name','albdesign-wc-donations'); ?>" value="<?php echo $single_campaign;?>"> <a href="#" class="albdesign_woocommerce_donations_campaigns_remove_field_button"><?php _e('Remove campaign','albdesign-wc-donations'); ?></a>
																</div>
															<?php 
															}
															
														}
													?>
													
												</div>			
												
												<br><br>
												
												<button class="albdesign_woocommerce_donations_campaigns_add_field_button"><?php _e('Add new campaign','albdesign-wc-donations'); ?></button>
												
												<p class="description"><?php _e('Add or remove campaigns','albdesign-wc-donations'); ?></p>

											</td>
										</tr>
										
									</tbody>
								</table>		
								<p class="submit">
									<input type="hidden" name="woocommerce_donations_campaigns_form">
										
									<input name="save" class="button-primary" type="submit" value="<?php _e('Save changes','albdesign-wc-donations'); ?>">        			        
								</p>
							</form>								
						</section> <!-- campaign tab --> 	
						
						<section id="section-reports">	
							
							<form action="" method="post">
								<select name="albdesign_wc_donation_show_donations_for_campaign" id="albdesign_wc_donation_show_donations_for_campaign">
									<option value="show_all"><?php _e('All donations','albdesign-wc-donations');?></option>
									<?php 
										//list existing campaigns if any 
										if(isset($get_saved_campaign_options['campaign_list'])){
											foreach($get_saved_campaign_options['campaign_list'] as $single_campaign){ ?>
												<option value="<?php echo $single_campaign;?>"><?php echo $single_campaign;?></option>
											<?php 
											}
										}
									?>									
								</select>
								
								<input type="submit" value="<?php _e('Search','albdesign-wc-donations');?>" class="button-primary" name="albdesign_wc_donations_search_reports" id="albdesign_wc_donations_search_reports">
								
							</form>
							
							<div class="albdesign-wc-donation-reports-results">
								<table class="wp-list-table widefat fixed posts">
									<thead>
										<tr>
											<th scope="col"  class="check-column manage-column column-title sortable desc " style="padding-top:0px;width: 3em;">
												<a class="table_header_text_link"><span><strong><?php _e('Order','albdesign-wc-donations');?></strong></span></a>
											</th>
											<th scope="col"  class="check-column manage-column column-title sortable desc " style="padding-top:0px;width: 3em;">
												<a class="table_header_text_link"><span><strong><?php _e('Donation','albdesign-wc-donations');?> </strong></span></a>
											</th>
											<th scope="col"  class="check-column manage-column column-title sortable desc " style="padding-top:0px;width: 3em;">
												<a class="table_header_text_link"><span><strong><?php _e('Campaign','albdesign-wc-donations');?></strong></span></a>
											</th>	
										</tr>
									<thead>
									<tbody id="the-list">	
									</tbody>
								</table>
							</div>
							
						</section> <!-- reports tab -->							

					</div>
				</div>
			</section>
			
		</div>	

<?php

} //woocommerce_donation_submenu_callback




//current product ID 
if ( get_option('woocommerce_donations_product_id' ) !== false ) {

	//defines the ID of the product to be used as donation
	define('DONATE_PRODUCT_ID', get_option( 'woocommerce_donations_product_id' )); 
}


if ( ! function_exists( 'ok_donation_exists' ) ){
	function ok_donation_exists(){
	 
		global $woocommerce;
	 
		if( sizeof($woocommerce->cart->get_cart()) > 0){
	 
			foreach($woocommerce->cart->get_cart() as $cart_item_key => $values){
	 
				$_product = $values['data'];
	 
				if( albdesign_wc_donation_get_product_or_order_id($_product) == DONATE_PRODUCT_ID )
					return true;
			}
		}
		return false;
	}
}



// Avada and themes that uses avada as parent Fix
if(strtolower(wp_get_theme()->Template) == 'avada' || strtolower(wp_get_theme()->Name) === 'avada'){
	add_action('woocommerce_after_cart_contents','ok_woocommerce_after_cart_table' , 1 );
	
	
	//add the extra classses to the SUBMIT button on CART for avada
	add_filter('albdesign_wc_donation_submit_button','albdesign_wc_donation_submit_button_avada_classes');
	function albdesign_wc_donation_submit_button_avada_classes($existing_classes){
		return $existing_classes . ' fusion-button fusion-button-default fusion-button-small button default small';
	}
	
}else{	
	//All other themes 
	add_action('woocommerce_cart_contents','ok_woocommerce_after_cart_table');
}

//Add the theme name to the TR container on CART page
add_filter('albdesign_wc_donation_cart_tr_container','albdesign_wc_donation_cart_tr_container_class');
function albdesign_wc_donation_cart_tr_container_class($existing_class){
	return strtolower(wp_get_theme()->Template) . ' donation-block' ;
}



if ( ! function_exists( 'ok_woocommerce_after_cart_table' ) ){
	
	function ok_woocommerce_after_cart_table(){
	 
		global $woocommerce;
		$donate = isset($woocommerce->session->ok_donation) ? floatval($woocommerce->session->ok_donation) : 0;
	 
		if(!ok_donation_exists()){
			unset($woocommerce->session->ok_donation);
		}
	  
		if(!ok_donation_exists()){
			?>
			<tr class="<?php echo apply_filters('albdesign_wc_donation_cart_tr_container','donation-block');?>">
				<td colspan="6">
					<div class="donation">
						
						<p class="message"><strong><?php 
						if(albdw_wcdonation_woocommerce_donations_get_saved_strings('cart_header_text')){
							echo albdw_wcdonation_woocommerce_donations_get_saved_strings('cart_header_text');
						}else{
							_e('Add a donation to your order','albdesign-wc-donations'); 
						}
						
						?></strong></p>
						
						<form action="" method="post">
							<div class="input text">
							
								<?php do_action('albdesign_wc_donations_before_textbox_on_cart'); ?>
								
								<?php 
									//display as INPUT or SELECT 
									if(get_option('albdesign_woocommerce_donations_show_donation_field_as',true) == 'dropdown'  && is_array( get_option('albdesign_woocommerce_donations_predefined_values') )){ 
									?>
									<select  name="ok-donation" class="input-text">
									<?php foreach(get_option('albdesign_woocommerce_donations_predefined_values') as $single_predefined_value){ ?>
										<option value="<?php echo $single_predefined_value;?>"><?php echo $single_predefined_value;?></option>
									<?php } //end foreach  ?>
									</select>
									
									<?php } else { ?>
								
									<input type="text" name="ok-donation" class="<?php echo apply_filters('albdesign_wc_donation_free_input_text_field','input-text');?>" value="<?php echo $donate;?>"/>
								
								<?php } ?>
								
								<?php do_action('albdesign_wc_donations_after_textbox_on_cart'); ?>
							
								<?php 
									$get_saved_campaign_options = get_option ( 'albdesign_woocommerce_donations_campaigns' );
									if(isset($get_saved_campaign_options['enable_campaign_support'])){
										if($get_saved_campaign_options['enable_campaign_support'] == 'yes'){
										?>
										<select name="albdesign-wc-donation-campaign">
											<?php if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'select_campaign_text' ) ) { ?>
												<option value=""><?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings( 'select_campaign_text' );?></option>
											<?php }else{ ?>
												<option value=""><?php _e('Select campaign','albdesign-wc-donations');?></option>
											<?php } ?>			
											<?php foreach($get_saved_campaign_options['campaign_list'] as $single_campaign){ ?>
												<option value="<?php echo $single_campaign;?>"> <?php echo $single_campaign;?> </option>
											<?php } ?>
										</select>
										
										<?php }
									
									}
								?>
								
								<?php if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'cart_button_text' ) ) { ?>
									<input type="submit" name="donate-btn" class="<?php echo apply_filters('albdesign_wc_donation_submit_button','button');?>" value="<?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings('cart_button_text');?>"/>
								<?php }else{ ?>
									<input type="submit" name="donate-btn" class="<?php echo apply_filters('albdesign_wc_donation_submit_button','button');?>" value="<?php _e('Add Donation','albdesign-wc-donations');?>"/>
								<?php } ?>								
							</div>

						</form>
					</div>
				</td>
			</tr>
			<?php
		}
	}
}



//Associate the campaign name to the order when order is completed 
add_action('woocommerce_thankyou','albdesign_wc_donation_add_campaign_meta_to_order');
function albdesign_wc_donation_add_campaign_meta_to_order($order_id){
	if ( !$order_id ){
		return;
	}
	
	global $woocommerce;
	
	$order = new WC_Order( $order_id );

	$product_list = '';
	$order_item = $order->get_items();

	foreach( $order_item as $product ) {
		if(defined('DONATE_PRODUCT_ID')){
			if ($product['product_id'] == DONATE_PRODUCT_ID ){
				
				if($woocommerce->session->albdesign_wc_donation_campaign){
					
					update_post_meta($order_id , '_albdesign_wc_donation_campaign_name',$woocommerce->session->albdesign_wc_donation_campaign);
				}
			}			
			
		}
	}
	
	//unset the campaign 
	unset($woocommerce->session->albdesign_wc_donation_campaign); 
}

//Show the campaign name on the order details page on admin area 
add_action('woocommerce_after_order_itemmeta','albdesign_wc_donations_show_campaign_on_order_item_meta',10,3);
function albdesign_wc_donations_show_campaign_on_order_item_meta( $item_id, $item, $_product){
	
	global $post;

	if(defined('DONATE_PRODUCT_ID') && $_product){
		
		if (  albdesign_wc_donation_get_product_or_order_id($_product) == DONATE_PRODUCT_ID ){
		
			if(get_post_meta( $post->ID , '_albdesign_wc_donation_campaign_name')){
				
				printf( __( 'Campaign %s', 'albdesign-wc-donations' ), get_post_meta( $post->ID , '_albdesign_wc_donation_campaign_name',true) ); 
				
			}
		}	
	}
	
}





add_action('template_redirect','ok_process_donation');

if ( ! function_exists( 'ok_process_donation' ) ){
	
	function ok_process_donation(){
	 
		global $woocommerce;
	 
		$donation = isset($_POST['ok-donation']) && !empty($_POST['ok-donation']) ? floatval($_POST['ok-donation']) : false;
		$campaign = isset($_POST['albdesign-wc-donation-campaign']) && !empty($_POST['albdesign-wc-donation-campaign']) ? $_POST['albdesign-wc-donation-campaign'] : false;

		if($donation && isset($_POST['donate-btn'])){

			// add item to basket
			$found = false;
	 
			// add to session
			if($donation > 0){
				
				
				
				$woocommerce->session->ok_donation = $donation;
				
				
				//check if product already in cart
				
				if( sizeof($woocommerce->cart->get_cart()) > 0){
	 
					
					
					foreach($woocommerce->cart->get_cart() as $cart_item_key=>$values){
	 
						$_product = $values['data'];
	 
						if(  albdesign_wc_donation_get_product_or_order_id($_product)  == DONATE_PRODUCT_ID){

							$found = true;

							
							//associate campaign with the donation  
							if($campaign){
								$woocommerce->session->albdesign_wc_donation_campaign = $campaign;
								
							}		
							
						}
						
					}
		
					// if product not found, add it
					if(!$found){

						$woocommerce->cart->add_to_cart(DONATE_PRODUCT_ID);

						//associate campaign with the donation  
						if($campaign){
							$woocommerce->session->albdesign_wc_donation_campaign = $campaign;
						}			
						
					}
				}else{
					// if no products in cart, add it
					$woocommerce->cart->add_to_cart(DONATE_PRODUCT_ID);
					
					//associate campaign with the donation  
					if($campaign){
						$woocommerce->session->albdesign_wc_donation_campaign = $campaign;
					}					
					
				}

			}
		}else{
			
			//if we dont have a donation then there is no point to have a campaign so remove that from session 
			if( !isset($woocommerce->session->ok_donation)) {
				
				if(isset($woocommerce->session->albdesign_wc_donation_campaign)){
					
					unset($woocommerce->session->albdesign_wc_donation_campaign);
					
				}
			}
		}
	}

}



/**
 * Add filter depending on the WC version  
 */
if(albdesign_wc_donation_woocommerce_version_check('3.0.5')){
	add_filter('woocommerce_product_get_price', 'ok_get_price',10,2);
}else{
	add_filter('woocommerce_get_price', 'ok_get_price',10,2);
}

if ( ! function_exists( 'ok_get_price' ) ){
	function ok_get_price($price, $product){
	 
		global $woocommerce;
		
		
		
		if( albdesign_wc_donation_get_product_or_order_id($product) == DONATE_PRODUCT_ID){
			
			if(isset($_POST['ok-donation'])){
				return isset($woocommerce->session->ok_donation) ? floatval($woocommerce->session->ok_donation) : 0;
			}
			
			if(isset($_POST['albdesign_wc_donation_from_single_page'])){
				
				return (sanitize_text_field($_POST['albdesign_wc_donation_from_single_page'])>0) ? floatval(sanitize_text_field($_POST['albdesign_wc_donation_from_single_page'])) : 0 ;
				
			}
			
			return isset($woocommerce->session->ok_donation) ? floatval($woocommerce->session->ok_donation) : 0;
			
		}
		
		return $price;
	}
}

//Change free text 
add_filter('woocommerce_free_price_html','albdesign_change_free_text' , 12,2);
add_filter('woocommerce_get_price_html','albdesign_change_free_text' , 12,2);

if ( ! function_exists( 'albdesign_change_free_text' ) ){
	
	function albdesign_change_free_text($price,$product_object){
		
		global $woocommerce;
		
		if( !is_admin() && !( defined( 'WC_API_REQUEST' ) && WC_API_REQUEST == true)){
			
			if($product_object->get_id()){
				
				if(defined('DONATE_PRODUCT_ID')){
				
					if ($product_object->get_id() == DONATE_PRODUCT_ID ){
						
						if(isset($woocommerce->session->ok_donation )){
							if($woocommerce->session->ok_donation ){
								
								return __('Donation added','albdesign-wc-donations');
							}
						}
					
						
						if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'single_product_text' ) ) { 
							return '<span class="enter_donation_amount_single_page">'. albdw_wcdonation_woocommerce_donations_get_saved_strings( 'single_product_text' )  .'</span>' ;
						}

						return '<span class="enter_donation_amount_single_page">'. _e('Enter the amount you wish to donate','albdesign-wc-donations') .'</span>' ;
						
					}
				
				}
			}
		}
		
		return $price;
	}

}

//Add the input box on single product page 
add_action('woocommerce_before_add_to_cart_button','albdesign_add_input_on_single_product_page');

if ( ! function_exists( 'albdesign_add_input_on_single_product_page' ) ){
	function albdesign_add_input_on_single_product_page(){
		
		global $woocommerce,$post;
		
		$current_donation_value = 0;
		
		if(defined('DONATE_PRODUCT_ID')){
		
			if($post->ID == DONATE_PRODUCT_ID){

				if(!ok_donation_exists()){ 
					unset($woocommerce->session->ok_donation); 
				}
				
				if( ! isset($woocommerce->session->ok_donation)){ ?>
					<p>
					
						<?php do_action('albdesign_wc_donations_before_textbox_on_single_product_page'); ?>
						<?php 
							//display as INPUT or SELECT 
							if(get_option('albdesign_woocommerce_donations_show_donation_field_as',true) == 'dropdown'  && is_array( get_option('albdesign_woocommerce_donations_predefined_values') )){ 
							?>
							<select  name="albdesign_wc_donation_from_single_page" class="input-text">
							<?php foreach(get_option('albdesign_woocommerce_donations_predefined_values') as $single_predefined_value){ ?>
								<option value="<?php echo $single_predefined_value;?>"><?php echo $single_predefined_value;?></option>
							<?php } //end foreach  ?>
							</select>
							
							<?php } else { ?>
						
							<input name="albdesign_wc_donation_from_single_page" value="<?php echo $current_donation_value;?>">
						
						<?php } ?>					
					
						<?php do_action('albdesign_wc_donations_after_textbox_on_single_product_page'); ?>

						<?php 
							$get_saved_campaign_options = get_option ( 'albdesign_woocommerce_donations_campaigns' );
							if(isset($get_saved_campaign_options['enable_campaign_support'])){
								if($get_saved_campaign_options['enable_campaign_support'] == 'yes'){ ?>
								<select name="albdesign-wc-donation-campaign">
								
									<?php if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'select_campaign_text' ) ) { ?>
										<option value=""><?php echo albdw_wcdonation_woocommerce_donations_get_saved_strings( 'select_campaign_text' );?></option>
									<?php }else{ ?>
										<option value=""><?php _e('Select campaign','albdesign-wc-donations');?></option>
									<?php } ?>			

									<?php foreach($get_saved_campaign_options['campaign_list'] as $single_campaign){ ?>
										<option value="<?php echo $single_campaign;?>"> <?php echo $single_campaign;?> </option>
									<?php } ?>
								</select>
								<?php }
							
							}
						?>

					</p>
				<?php
				}else{
					?>
					 <p class="albdesign_wc_donation_from_single_page_added">
						
						<?php 
						
							if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'single_product_text' ) ) {
						
								echo albdw_wcdonation_woocommerce_donations_get_saved_strings( 'donation_added_single_product_text' );
							
							}else {
								
								printf( __( 'Donation added . Check it on the  <a href="%s">cart page</a>', 'albdesign-wc-donations' ), $woocommerce->cart->get_cart_url()); 
								
							} 
						?>
						
					</p>
					<?php
				}
			} 
		
		} // if defined
	}
}


/*
* Change "add to cart" on single page
*/

add_filter( 'woocommerce_product_single_add_to_cart_text', 'albdesign_custom_cart_button_text_single_page' );  
 
if ( ! function_exists( 'albdesign_custom_cart_button_text_single_page' ) ){
	
	function albdesign_custom_cart_button_text_single_page($text) {
	 
		global $post,$woocommerce;

		if(defined('DONATE_PRODUCT_ID')){
		
			if($post->ID == DONATE_PRODUCT_ID) {
				if(isset($woocommerce->session->ok_donation )){
					$text =  _e('Donation added','albdesign-wc-donations') ;
				}
			}
		
		}
	 
		return $text;
	}

}

/*
* Hide  the "ADD TO CART" on single page if donations already added
*/
add_action('wp_head','albdesign_wc_donation_hide_add_to_cart_on_single_product');

if ( ! function_exists( 'albdesign_wc_donation_hide_add_to_cart_on_single_product' ) ){
	
	function albdesign_wc_donation_hide_add_to_cart_on_single_product(){
		
		global $woocommerce;
		
		if(defined('DONATE_PRODUCT_ID')){
		
			if(isset($woocommerce->session->ok_donation )){
				echo '<style>
						.woocommerce div.product.post-'.DONATE_PRODUCT_ID.' form.cart .button {
							display:none;
						}
					 </style>';
			}

		}
		
	}
}


add_filter('woocommerce_add_cart_item', 'albdesign_wc_donation_add_cart_item_data', 14, 2);

if ( ! function_exists( 'albdesign_wc_donation_add_cart_item_data' ) ){
	
	function albdesign_wc_donation_add_cart_item_data($cart_item) {
		global $woocommerce;

		

		if(defined('DONATE_PRODUCT_ID')){
		
	
		
			if($cart_item['product_id'] == DONATE_PRODUCT_ID){

				//if the user is adding from single product page 
				if(isset($_POST['albdesign_wc_donation_from_single_page'])){
					
					$woocommerce->session->ok_donation =  floatval(sanitize_text_field($_POST['albdesign_wc_donation_from_single_page']));
					
					//check if we have a campaign 
					if(isset($_POST['albdesign-wc-donation-campaign'])){
						$woocommerce->session->albdesign_wc_donation_campaign =  sanitize_text_field($_POST['albdesign-wc-donation-campaign']);
					}else{
						unset($woocommerce->session->albdesign_wc_donation_campaign);
					}
					
				}
			}
		
		}
		
		return $cart_item;
	}

}



//Append the campaign to the table of items on cart page 
add_filter('woocommerce_cart_item_name','albdesign_wc_donation_change_cart_item_name',10,3);
if ( ! function_exists( 'albdesign_wc_donation_change_cart_item_name' ) ){
	function albdesign_wc_donation_change_cart_item_name($link, $cart_item, $cart_item_key ){
		
		global $woocommerce;
		
		if($cart_item['product_id'] === get_option( 'woocommerce_donations_product_id' ,true)){
			if(isset($woocommerce->session->albdesign_wc_donation_campaign)){
				return sprintf( __( '%s <br> Campaign %s', 'albdesign-wc-donations' ), $link , $woocommerce->session->albdesign_wc_donation_campaign );
			}
		}
		
		return $link ;
	}
}


//Show or hide currency symbol on product pages 
add_action('albdesign_wc_donations_before_textbox_on_single_product_page','albdesign_wc_donation_show_currency_symbol_before');
add_action('albdesign_wc_donations_before_textbox_on_cart','albdesign_wc_donation_show_currency_symbol_before');
if ( ! function_exists( 'albdesign_wc_donation_show_currency_symbol_before' ) ){
	function albdesign_wc_donation_show_currency_symbol_before(){
		
		$get_saved_show_currency_symbol = get_option('albdesign_woocommerce_donations_show_currency_field',true);
		
		if( $get_saved_show_currency_symbol === 'before'  ) {
			echo get_woocommerce_currency_symbol();
		}
		
	}
}


add_action('albdesign_wc_donations_after_textbox_on_single_product_page','albdesign_wc_donation_show_currency_symbol_after');
add_action('albdesign_wc_donations_after_textbox_on_cart','albdesign_wc_donation_show_currency_symbol_after');
if ( ! function_exists( 'albdesign_wc_donation_show_currency_symbol_after' ) ){
	function albdesign_wc_donation_show_currency_symbol_after(){
		
		$get_saved_show_currency_symbol = get_option('albdesign_woocommerce_donations_show_currency_field',true);
		
		if( $get_saved_show_currency_symbol === 'after'  ) {
			echo get_woocommerce_currency_symbol();
		}
		
	}
}

add_action('woocommerce_review_order_before_payment','albdesign_donations_add_link_on_checkout');

if ( ! function_exists( 'albdesign_donations_add_link_on_checkout' ) ){
	
	function albdesign_donations_add_link_on_checkout(){ 

		global $woocommerce;

		$products_ids_in_cart=false;
		
		//check if donation is already in cart 
		foreach($woocommerce->cart->get_cart() as $cart_item_key => $values ) {
			
			$_product = $values['data'];
		
			$products_ids_in_cart[albdesign_wc_donation_get_product_or_order_id($_product)]= albdesign_wc_donation_get_product_or_order_id($_product);

		}

		//if no donation found on cart ... show a link on checkout page
		if( is_array( $products_ids_in_cart ) ) {
			
			if( !in_array(DONATE_PRODUCT_ID,$products_ids_in_cart )){
				?>
					<div style="margin: 0 -1px 24px 0;">
					<h3><?php 
						if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'checkout_title_text' ) ) { 
							echo albdw_wcdonation_woocommerce_donations_get_saved_strings( 'checkout_title_text' );
						}else {
							_e('Add a donation to your order','albdesign-wc-donations');
						} 
					?></h3> 
					
					
					<?php 
						if( albdw_wcdonation_woocommerce_donations_get_saved_strings( 'checkout_text' ) ) { 
							echo albdw_wcdonation_woocommerce_donations_get_saved_strings( 'checkout_text' );
						}else {
							 printf( __( 'If you wish to add a donation you can do so on the <a href="%s">cart page</a>', 'albdesign-wc-donations' ), wc_get_cart_url() ); 
						} 
					?>
					</div>
				<?php 
				
			} //end if "no donation found on cart"
		
		} //end if is array $products_ids_in_cart
		
	}
	
}


/*
* Add the donation campaign to the email sent to customer 
*/
add_action('woocommerce_order_item_meta_end','albdesign_wc_donation_add_campaign_details_to_email',999,3 );
if ( ! function_exists( 'albdesign_wc_donation_add_campaign_details_to_email' ) ){
	function albdesign_wc_donation_add_campaign_details_to_email($item_id, $item, $order){

		if(get_option( 'woocommerce_donations_product_id' )){
		
			if ($item['product_id'] == get_option( 'woocommerce_donations_product_id' ) ){
				
				if( get_post_meta( albdesign_wc_donation_get_product_or_order_id($order) , '_albdesign_wc_donation_campaign_name', true ) ) {
					
					printf( __( 'Campaign %s', 'albdesign-wc-donations' ), get_post_meta( albdesign_wc_donation_get_product_or_order_id($order) , '_albdesign_wc_donation_campaign_name',true) ); 
					
				}
			}	
		}

	}
}



/*
* Generate reports 
*/
add_action('wp_ajax_albdesign_wc_donations_search_reports','albdesign_wc_donations_search_reports_ajax_func');
if ( ! function_exists( 'albdesign_wc_donations_search_reports_ajax_func' ) ){
	
	function albdesign_wc_donations_search_reports_ajax_func(){
		
		//get all orders that has donations ... will filter them later or not 
		global $wpdb;
		$produto_id = get_option( 'woocommerce_donations_product_id' ); 
		$consulta = "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_itemmeta woim LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi ON woim.order_item_id = oi.order_item_id WHERE meta_key = '_product_id' AND meta_value = %d GROUP BY order_id;";
		$order_ids = $wpdb->get_col( $wpdb->prepare( $consulta, $produto_id ) );		

		$return_array = array('count' => 0 , 'results' => array() );
		
		//show all orders that has the donation product 
		if(sanitize_text_field($_POST['search_for'])==='show_all'){


			if( $order_ids ) {
				$args = array(
							'post_type' =>'shop_order',
							'post__in' => $order_ids,
							'post_status' =>  array_keys( wc_get_order_statuses() ),
							'posts_per_page' => -1,
							'order' => 'DESC',

						);
				$orders = get_posts( $args );
				
				foreach($orders as $single_order){
					$order = new WC_Order( $single_order->ID );
					foreach ($order->get_items() as $key => $lineItem) {
						
						if($lineItem['product_id']== get_option( 'woocommerce_donations_product_id' ) ){
							$single_order_to_return['ID'] = $single_order->ID;
							$single_order_to_return['view_full_order_link'] =  '<a href="'.get_edit_post_link($single_order->ID).'">'. $single_order->ID .'</a>' ; 
							$single_order_to_return['donation_value'] = $lineItem['line_total'];
							$single_order_to_return['campaign'] = get_post_meta($single_order->ID , '_albdesign_wc_donation_campaign_name', true);
							$final_orders[] = $single_order_to_return;
						}
						
					}
					
				}
				
				$return_array['count'] = count($final_orders);
				$return_array['results'] = $final_orders;
				
			}

		}else {
			
			//show orders that has specific donation for X campaign 
			
			if( $order_ids ) {
				
				$final_orders = array();
				
				$args = array(
							'post_type' =>'shop_order',
							'post__in' => $order_ids,
							'post_status' =>  array_keys( wc_get_order_statuses() ),
							'meta_key'    => '_albdesign_wc_donation_campaign_name',
							'meta_value'  => esc_attr($_POST['search_for']),							
							'posts_per_page' => -1,
							'order' => 'DESC',

						);
				$orders = get_posts( $args );

				foreach($orders as $single_order){
					$order = new WC_Order( $single_order->ID );
					foreach ($order->get_items() as $key => $lineItem) {
						
						if($lineItem['product_id']== get_option( 'woocommerce_donations_product_id' ) ){
							$single_order_to_return['ID'] = $single_order->ID;
							$single_order_to_return['view_full_order_link'] =  '<a href="'.get_edit_post_link($single_order->ID).'">'. $single_order->ID .'</a>' ; 
							$single_order_to_return['donation_value'] = $lineItem['line_total'];
							$single_order_to_return['campaign'] = get_post_meta($single_order->ID , '_albdesign_wc_donation_campaign_name', true);
							$final_orders[] = $single_order_to_return;
						}
						
					}
					
				}
				
				$return_array['count'] = count($final_orders);
				$return_array['results'] = $final_orders;			
				
			}			

		}
		
		die (json_encode($return_array));
	}

	

	
	
}



/**
 *  Compare WC versions 
 */
function albdesign_wc_donation_woocommerce_version_check( $version = '3.0' ) {
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}
	}
	return false;
}


/**
 *  Get product ID depending on WC version . 
 *  Added since after WC 3.0 we cant use $product->id but we need to use $product->get_id()
 */

function albdesign_wc_donation_get_product_or_order_id( $product_or_order ) {
	
	if(albdesign_wc_donation_woocommerce_version_check('3.0')){
		return $product_or_order->get_id();
	}else{
		return $product_or_order->id;
	}
	
} 
 
 

/*
* Get translated texts for backend plugin options
*/
function albdw_wcdonation_woocommerce_donations_get_saved_strings_admin($key){
	$saved_strings_array = get_option('woocommerce_donations_translations');
	if(isset($saved_strings_array[$key])){
		return stripcslashes(esc_html($saved_strings_array[$key]));
	}
	
	return false;
}

/*
* Get translated texts for frontend
*/
function albdw_wcdonation_woocommerce_donations_get_saved_strings($key){
	
	$saved_strings_frontend_array = get_option('woocommerce_donations_translations');
	
	if($saved_strings_frontend_array['use_custom_translation']==='yes'){
		
		if($saved_strings_frontend_array[$key]){
			return stripcslashes(htmlspecialchars_decode($saved_strings_frontend_array[$key]));
			
		}
	
	}
	
	return false;
}




function albdw_wcdonation_plugin_uninstall() {

  delete_option( 'woocommerce_donations_product_id' );
  delete_option( 'woocommerce_donations_translations' );
  delete_option( 'albdesign_woocommerce_donations_show_currency_field' );
  delete_option( 'albdesign_woocommerce_donations_show_donation_field_as' );
  delete_option( 'albdesign_woocommerce_donations_predefined_values' );
  delete_option( 'albdesign_woocommerce_donations_campaigns' );
 
}
register_uninstall_hook( __FILE__, 'albdw_wcdonation_plugin_uninstall' );