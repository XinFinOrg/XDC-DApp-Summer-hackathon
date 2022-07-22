<?php

/**
 * Display the WooCommerce custom fields.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="options_group listing show_if_subscription" style="display: none;">
	<?php
	// Is Listings Subscription?             		
	woocommerce_wp_checkbox( 
		array( 
			'id'          => 'is_acadp_subscription', 
			'label'       => __( 'Is Listings Subscription?', 'advanced-classifieds-and-directory-pro' ), 
			'description' => __( 'Check this option if this is a listings subscription product', 'advanced-classifieds-and-directory-pro' ),
			'value'       => $is_acadp_subscription
		)
	);
	?>
</div>

<div class="options_group listing show_if_listings_package show_if_subscription">
	<?php
	// Listings Limit
	woocommerce_wp_text_input( 
		array( 
			'id'                => 'acadp_listings_limit', 
			'label'             => __( 'Listings Limit', 'advanced-classifieds-and-directory-pro' ), 
			'placeholder'       => '', 
			'desc_tip'      	=> true,
			'description'       => __( 'Enter the number of listings this plan allows. Leave this field empty to allow unlimited listings.', 'advanced-classifieds-and-directory-pro' ),
			'type'              => 'number', 
			'custom_attributes' => array(
				'step' => 'any',
				'min'  => '0'
			),
			'value'             => $listings_limit 
		)
	);
		
	// Listing Duration
	woocommerce_wp_text_input( 
		array( 
			'id'                => 'acadp_listing_duration', 
			'label'             => __( 'Listing Duration', 'advanced-classifieds-and-directory-pro' ), 
			'placeholder'       => '', 
			'desc_tip'      	=> true,
			'description'       => __( 'Listing Duration (in days)', 'advanced-classifieds-and-directory-pro' ),
			'type'              => 'number', 
			'custom_attributes' => array(
				'step' 	=> 'any',
				'min'	=> '0'
			),
			'value'             => $listing_duration
		)
	);
		
	// Images Limit
	woocommerce_wp_text_input( 
		array( 
			'id'                => 'acadp_images_limit', 
			'label'             => __( 'Images Limit', 'advanced-classifieds-and-directory-pro' ), 
			'placeholder'       => '', 
			'desc_tip'      	=> true,
			'description'       => __( 'Enter the number of images the users can upload per listing.', 'advanced-classifieds-and-directory-pro' ),
			'type'              => 'number', 
			'custom_attributes' => array(
				'step' 	=> 'any',
				'min'	=> '0'
			),
			'value'             => $images_limit
		)
	);
		
	// Featured               		
	woocommerce_wp_checkbox( 
		array( 
			'id'            => 'acadp_featured', 
			'label'         => __( 'Featured', 'advanced-classifieds-and-directory-pro' ), 
			'description'   => __( 'Check this option if you want to make all listings submitted using this plan as featured. Featured listings will always appear on top of regular listings.', 'advanced-classifieds-and-directory-pro' ),
			'value'       	=> $featured
		)
	);
		
	// Select Categories
	if ( ! empty( $multi_categories_settings['enabled'] ) ) {
		$categories_disabled_note = sprintf( 
			__( 'Sorry, you cannot create category based plans when %s enabled.', 'advanced-classifieds-and-directory-pro' ), 
			'<a href="' . esc_url( admin_url( 'admin.php?page=acadp_settings&tab=general&section=acadp_multi_categories_settings' ) ) . '">' . __( 'Multi Categories', 'advanced-classifieds-and-directory-pro' ) . '</a>'			
		);

		printf(
			'<p class="form-field acadp_categories_field"><label for="acadp_categories">%s</label><span class="description">%s</span></p>',
			__( 'Select Categories', 'advanced-classifieds-and-directory-pro' ),
			$categories_disabled_note
		);
	} else {
		acadp_premium_woocommerce_get_categories_multiselect(
			array(
				'id'          => 'acadp_categories',
				'name'        => 'acadp_categories[]',
				'label'       => __( 'Select Categories', 'advanced-classifieds-and-directory-pro' ),
				'desc_tip'    => true,
				'description' => __( 'Select categories that the plan belongs to. Leave this field empty to assign this plan to all the listing categories.', 'advanced-classifieds-and-directory-pro' ),
				'value'       => $categories
			)
		);
	}

	// Disable Repeat Purchase
	woocommerce_wp_checkbox( 
		array( 
			'id'          => 'acadp_disable_repeat_purchase', 
			'label'       => __( 'Disable Repeat Purchase', 'advanced-classifieds-and-directory-pro' ), 
			'description' => __( 'Check this option if this plan can be purchased only once per user.', 'advanced-classifieds-and-directory-pro' ),
			'value'       => $disable_repeat_purchase
		)
	);
    ?>
</div>