<?php

/**
 * General Helper Functions.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Base64 encode a string.
 *
 * @since  1.6.0
 * @param  string $string String to be encoded.
 * @return string         Encoded string.
 */
function acadp_base64_encode( $string ) {
	return str_replace( array( '+', '/', '=' ), array(  '-', '_', '.' ), base64_encode( $string ) );
}

/**
 * Base64 decode a string.
 *
 * @since  1.6.0
 * @param  string $string String to be decoded.
 * @return string         Decoded string.
 */
function acadp_base64_decode( $string ) { 
	return base64_decode( str_replace( array( '-', '_', '.' ), array( '+', '/', '=' ), $string ) );
}

/**
 * Insert required custom pages and return their IDs as array.
 * 
 * @since  1.5.6
 * @return array Array of created page IDs.
 */
function acadp_insert_custom_pages() {
	// Vars
	$page_settings = get_option( 'acadp_page_settings', array() );
	$page_definitions = acadp_get_custom_pages_list();
	
	// ...
	$pages = array();
	
	foreach ( $page_definitions as $slug => $page ) {
		$id = 0;
		
		if ( array_key_exists( $slug, $page_settings ) ) {
			$id = (int) $page_settings[ $slug ];
		}

		if ( ! $id ) {
			if ( 'search' == $slug ) {
				$page['content'] = '[acadp_search_form]<p>&nbsp;</p>[acadp_search]';
			}

			$id = wp_insert_post(
				array(
					'post_title'     => $page['title'],
					'post_content'   => $page['content'],
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);
		}				
			
		$pages[ $slug ] = $id;			
	}

	return $pages;
}

/**
 * Get a list of custom pages.
 * 
 * @since  1.7.3
 * @return array $pages Array of pages.
 */
function acadp_get_custom_pages_list() {
	$pages = array(
		'listings' => array( 
			'title'   => __( 'Listings', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_listings]'
		),	
		'locations' => array( 
			'title'   => __( 'Listing Locations', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_locations]'
		),
		'location' => array( 
			'title'   => __( 'Listing Location', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_location]' 
		),
		'categories' => array( 
			'title'   => __( 'Listing Categories', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_categories]' 
		),
		'category' => array( 
			'title'   => __( 'Listing Category', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_category]' 
		),
		'search' => array( 
			'title'   => __( 'Search Listings', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_search]' 
		),
		'user_listings' => array( 
			'title'   => __( 'User Listings', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_user_listings]' 
		),
		'user_dashboard' => array( 
			'title'   => __( 'User Dashboard', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_user_dashboard]' 
		),
		'listing_form' => array( 
			'title'   => __( 'Listing Form', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_listing_form]' 
		),
		'manage_listings' => array( 
			'title'   => __( 'Manage Listings', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_manage_listings]' 
		),
		'favourite_listings' => array( 
			'title'   => __( 'Favourite Listings', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_favourite_listings]' 
		),
		'checkout' => array( 
			'title'   => __( 'Checkout', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_checkout]'
		),
		'payment_receipt' => array( 
			'title'   => __( 'Payment Receipt', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_payment_receipt]' 
		),
		'payment_failure' => array( 
			'title'   => __( 'Transaction Failed', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_payment_errors]'.__( 'Your transaction failed, please try again or contact site support.', 'advanced-classifieds-and-directory-pro' ).'[/acadp_payment_errors]' 
		),
		'payment_history' => array( 
			'title'   => __( 'Payment History', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_payment_history]' 
		),
		'login_form' => array( 
			'title'   => __( 'Login', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_login]' 
		),		
		'register_form' => array(
			'title'   => __( 'Register', 'advanced-classifieds-and-directory-pro' ),
			'content' => '[acadp_register]'
		),
		'user_account' => array( 
			'title'   => __( 'Account', 'advanced-classifieds-and-directory-pro' ), 
			'content' => '[acadp_user_account]' 
		),
		'forgot_password' => array(
			'title'   => __( 'Forgot Password', 'advanced-classifieds-and-directory-pro' ),
			'content' => '[acadp_forgot_password]'
		),
		'password_reset' => array(
			'title'   => __( 'Password Reset', 'advanced-classifieds-and-directory-pro' ),
			'content' => '[acadp_password_reset]'
		)
	);

	return $pages;
}

/** 
 * Get current address bar URL.
 *
 * @since  1.0.0
 * @return string Current Page URL.
 */
function acadp_get_current_url() {
    $current_url  = ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://";
    $current_url .= $_SERVER["SERVER_NAME"];
    if ( "80" != $_SERVER["SERVER_PORT"] && "443" != $_SERVER["SERVER_PORT"] ) {
        $current_url .= ":" . $_SERVER["SERVER_PORT"];
    }
    $current_url .= $_SERVER["REQUEST_URI"];
	
    return $current_url;	
}

/**
 * Enable or disable the plugin's custom registration feature.
 *
 * @since  1.5.6
 * @return bool  Return true if enabled, false if not.
 */
function acadp_registration_enabled() {
	$registration_settings = get_option( 'acadp_registration_settings', array() );
	
	if ( ! empty( $registration_settings['engine'] ) && 'acadp' == $registration_settings['engine'] ) {
		return true;
	}
	
	return false;
}

/**
 * Provides a simple login form.
 *
 * @since  1.0.0
 * @return string Login form.
 */
function acadp_login_form() {
	$registration_settings = get_option( 'acadp_registration_settings', array() );
	
	if ( ! empty( $registration_settings['engine'] ) && 'acadp' == $registration_settings['engine'] ) {	
		$redirect = esc_url_raw( get_permalink() );
		$form = do_shortcode( "[acadp_login redirect=$redirect]" );	
	} else {	
		// Login Form
		$custom_login = $registration_settings['custom_login'];
		
		if ( empty( $custom_login ) ) {
			// Fallback to default login form
			$form = wp_login_form();
		} else {
			if ( ! filter_var( $custom_login, FILTER_VALIDATE_URL ) === FALSE ) {
				// If URL redirect here
				echo '<script type="text/javascript">window.location.href="' . $custom_login . '";</script>';
				exit(); 
			} else {
				// If shortcode found
				$form = do_shortcode( $custom_login );
			}
		}
		
		// Forgot Password
		$lostpassword_url = empty( $registration_settings['custom_forgot_password'] ) ? wp_lostpassword_url( esc_url_raw( get_permalink() ) ) : $registration_settings['custom_forgot_password'];
		$form .= sprintf( '<p><a href="%s">%s</a></p>', $lostpassword_url, __( 'Forgot your password?', 'advanced-classifieds-and-directory-pro' ) );
		
		// Registration		
		if ( get_option( 'users_can_register' ) ) {
			$registration_url = empty( $registration_settings['custom_register'] ) ? wp_registration_url() : $registration_settings['custom_register'];
			$form .= sprintf( '<p><a href="%s">%s</a></p>', $registration_url, __( 'Create an account', 'advanced-classifieds-and-directory-pro' ) );
		}		
	}
	
	return $form;	
}

/**
 * Whether the current user has a specific capability.
 *
 * @since  1.0.0
 * @param  string $capability Capability name.
 * @param  int    $post_id    Optional. ID of the specific object to check against if
 *							  `$capability` is a "meta" cap.
 * @return bool               True if the current user has the capability, false if not.
 */
function acadp_current_user_can( $capability, $post_id = 0 ) {
	$user_id = get_current_user_id();
	
	// If editing, deleting, or reading a listing, get the post and post type object.
	if ( 'edit_acadp_listing' == $capability || 'delete_acadp_listing' == $capability || 'read_acadp_listing' == $capability ) {
		$post = get_post( $post_id );
		$post_type = get_post_type_object( $post->post_type );

		// If editing a listing, assign the required capability.
		if ( 'edit_acadp_listing' == $capability ) {
			if( $user_id == $post->post_author ) {
				$capability = 'edit_acadp_listings';
			} else {
				$capability = 'edit_others_acadp_listings';
			}
		}
		
		// If deleting a listing, assign the required capability.
		elseif ( 'delete_acadp_listing' == $capability ) {
			if ( $user_id == $post->post_author ) {
				$capability = 'delete_acadp_listings';
			} else {
				$capability = 'delete_others_acadp_listings';
			}
		}
		
		// If reading a private listing, assign the required capability.
		elseif ( 'read_listing' == $capability ) {
			if( 'private' != $post->post_status ) {
				$capability = 'read';
			} elseif ( $user_id == $post->post_author ) {
				$capability = 'read';
			} else {
				$capability = 'read_private_acadp_listings';
			}
		}		
	}
		
	return current_user_can( $capability );	
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @since  1.0.0
 * @param  string $key       The key to insert after.
 * @param  array  $array     An array to insert in to.
 * @param  array  $new_array An array to insert.
 * @return                   The new array if the key exists, FALSE otherwise.
 */
function acadp_array_insert_after( $key, $array, $new_array ) {
	if ( array_key_exists( $key, $array ) ) {
    	$new = array();
    	foreach ( $array as $k => $value ) {
      		$new[ $k ] = $value;
      		if ( $k === $key ) {
				foreach ( $new_array as $new_key => $new_value ) {
        			$new[ $new_key ] = $new_value;
				}
      		}
    	}
    	return $new;
  	}
		
  	return $array;  
}

/**
 * Calculate listing expiry date.
 *
 * @since  1.0.0
 * @param  int    $post_id       Post ID.
 * @param  string $start_date    Date from which the expiry date must be calculated.
 * @param  bool   $never_expires True if the listing is set to be permanent, false if not.
 * @return string $date          Expiry date.
 */
function acadp_listing_expiry_date( $post_id, $start_date = NULL, $never_expires = false ) {
	// Get number of days to add
	$general_settings = get_option( 'acadp_general_settings' );
	$days = apply_filters( 'acadp_listing_duration', (int) $general_settings['listing_duration'], $post_id );
	
	if ( $never_expires || $days <= 0 ) {		
		update_post_meta( $post_id, 'never_expires', 1 );
		$days = 999;
	} else {
		delete_post_meta( $post_id, 'never_expires' );
	}

	if ( $start_date == NULL ) {
		// Current time
		$start_date = current_time( 'mysql' );
	}
	
	// Calculate new date
	$date = new DateTime( $start_date );
	$date->add( new DateInterval( "P{$days}D" ) );
	
	// return
	return $date->format( 'Y-m-d H:i:s' );	
}

/**
 * Parse MySQL date format.
 *
 * @since  1.0.0
 * @param  string $date MySQL date string.
 * @return array  $date Array of date values.
 */
function acadp_parse_mysql_date_format( $date ) {
	$date = preg_split( '([^0-9])', $date );
	
	return array(
		'year'  => $date[0],
		'month' => $date[1],
		'day'   => $date[2],
		'hour'  => $date[3],
		'min'   => $date[4],
		'sec'   => $date[5]
	);				
}

/**
 * Convert to MySQL date format (Y-m-d H:i:s).
 *
 * @since  1.0.0
 * @param  array  $date Array of date values.
 * @return string $date Formatted date string.
 */
function acadp_mysql_date_format( $date ) {
	$defaults = array(
		'year'  => 0,
		'month' => 0,
		'day'   => 0,
		'hour'  => 0,
		'min'   => 0,
		'sec'   => 0
	);	
	$date = array_merge( $defaults, $date );

	$year = str_pad( $date['year'], 4, '0', STR_PAD_RIGHT );								
	$month = max( 1, min( 12, $date['month'] ) );							
	$day = max( 1, min( 31, $date['day'] ) );				
	$hour = max( 1, min( 24, $date['hour'] ) );				
	$min = max( 0, min( 59, $date['min'] ) );	
	$sec = max( 0, min( 59, $date['sec'] ) );
	
	return sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $min, $sec );				
}

/**
 * Get payment statuses.
 *
 * @since  1.0.0
 * @return array $statuses A list of available payment status.
 */
function acadp_get_payment_statuses() {
	$statuses = array(
		'created'   => __( "Created", 'advanced-classifieds-and-directory-pro' ),
		'pending'   => __( "Pending", 'advanced-classifieds-and-directory-pro' ),
		'completed' => __( "Completed", 'advanced-classifieds-and-directory-pro' ),
		'failed'    => __( "Failed", 'advanced-classifieds-and-directory-pro' ),
		'cancelled' => __( "Cancelled", 'advanced-classifieds-and-directory-pro' ),
		'refunded'  => __( "Refunded", 'advanced-classifieds-and-directory-pro' )
	);
			
	return apply_filters( 'acadp_payment_statuses', $statuses );	
}

/**
 * Retrieve the payment status in localized format.
 *
 * @since  1.5.4
 * @param  string $status Payment status.
 * @return string $status Localized payment status.
 */
function acadp_get_payment_status_i18n( $status ) {
	$statuses = acadp_get_payment_statuses();			
	return $statuses[ $status ];	
}

/**
 * Get bulk actions.
 *
 * @since  1.0.0
 * @return array $actions A list of payment history page bulk actions.
 */
function acadp_get_payment_bulk_actions() {
	$actions = array(
		'set_to_created'   => __( "Set to Created", 'advanced-classifieds-and-directory-pro' ),
		'set_to_pending'   => __( "Set to Pending", 'advanced-classifieds-and-directory-pro' ),
		'set_to_completed' => __( "Set to Completed", 'advanced-classifieds-and-directory-pro' ),
		'set_to_failed'    => __( "Set to Failed", 'advanced-classifieds-and-directory-pro' ),		
		'set_to_cancelled' => __( "Set to Cancelled", 'advanced-classifieds-and-directory-pro' ),
		'set_to_refunded'  => __( "Set to Refunded", 'advanced-classifieds-and-directory-pro' )
	);
			
	return apply_filters( 'acadp_payment_bulk_actions', $actions );	
}

/**
 * Sanitize Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since  1.0.0
 * @param  string $amount            Price amount to format.
 * @param  array  $currency_settings Currency Settings.
 * @return string $amount            Newly sanitized amount.
 */
function acadp_sanitize_amount( $amount, $currency_settings = array() ) {
	$is_negative = false;
	
	if ( empty( $currency_settings ) ) {
		$currency_settings = get_option( 'acadp_currency_settings' );
	}
	
	$currency = ! empty( $currency_settings[ 'currency' ] ) ? $currency_settings[ 'currency' ] : 'USD';
	$thousands_sep = ! empty( $currency_settings[ 'thousands_separator' ] ) ? $currency_settings[ 'thousands_separator' ] : ',';
	$decimal_sep = ! empty( $currency_settings[ 'decimal_separator' ] ) ? $currency_settings[ 'decimal_separator' ] : '.';

	// Sanitize the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if( ( $thousands_sep == '.' || $thousands_sep == ' ' ) && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif ( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	if ( $amount < 0 ) {
		$is_negative = true;
	}

	$amount = preg_replace( '/[^0-9\.]/', '', $amount );
	$decimals = acadp_currency_decimal_count( 2, $currency );
	$amount = number_format( (double) $amount, $decimals, '.', '' );

	if ( $is_negative ) {
		$amount *= -1;
	}

	return apply_filters( 'acadp_sanitize_amount', $amount );	
}

/**
 * Sanitize Paymount Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since  1.5.4
 * @param  string $amount Price amount to format.
 * @return string         Newly sanitized amount.
 */
function acadp_sanitize_payment_amount( $amount ) {
	return acadp_sanitize_amount( $amount, acadp_get_payment_currency_settings() );
}

/**
 * Returns a nicely formatted amount.
 *
 * @since  1.0.0
 * @param  string $amount            Price amount to format
 * @param  string $decimals          Whether or not to use decimals. Useful when set 
 *								     to false for non-currency numbers.
 * @param  array  $currency_settings Currency Settings.
 * @return string $amount            Newly formatted amount or Price Not Available
 */
function acadp_format_amount( $amount, $decimals = true, $currency_settings = array() ) {
	if ( empty( $currency_settings ) ) {
		$currency_settings = get_option( 'acadp_currency_settings' );
	}
	
	$currency = ! empty( $currency_settings[ 'currency' ] ) ? $currency_settings[ 'currency' ] : 'USD';
	$thousands_sep = ! empty( $currency_settings[ 'thousands_separator' ] ) ? $currency_settings[ 'thousands_separator' ] : ',';
	$decimal_sep = ! empty( $currency_settings[ 'decimal_separator' ] ) ? $currency_settings[ 'decimal_separator' ] : '.';

	// Format the amount
	if ( $decimal_sep == ',' && false !== ( $sep_found = strpos( $amount, $decimal_sep ) ) ) {
		$whole = substr( $amount, 0, $sep_found );
		$part = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip , from the amount (if set as the thousands separator)
	if ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ',', '', $amount );
	}

	// Strip ' ' from the amount (if set as the thousands separator)
	if ( $thousands_sep == ' ' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ' ', '', $amount );
	}

	if ( empty( $amount ) ) {
		$amount = 0;
	}

	//Mel: 23/11/21. To enable 3 decimal places or more
/* 	if ( $decimals ) {
		$decimals  = acadp_currency_decimal_count( 2, $currency );
	} else {
		$decimals = 0;
	} */
	
	//Mel: 24/11/21. To check if the price is cash (like $49) or crypto (like 0.00000001 with 8 decimal places for BTC)
	if ( substr($amount, 0, 1) == '0' ) {
		$formatted = number_format( $amount, 8, $decimal_sep, $thousands_sep );
	} else {
		$formatted = number_format( $amount, 0, $decimal_sep, $thousands_sep );
	}
	
	//Mel: 23/11/21. To enable 3 decimal places like 0.001
	//$formatted = number_format( $amount, 3, $decimal_sep, $thousands_sep );
	//$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	return apply_filters( 'acadp_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );	
}

/**
 * Returns a nicely formatted amount.
 *
 * @since  1.5.4
 * @param  string $amount   Price amount to format
 * @param  string $decimals Whether or not to use decimals. Useful when set to false for non-currency numbers.
 * @return string           Newly formatted amount or Price Not Available
 */
function acadp_format_payment_amount( $amount, $decimals = true ) {
	return acadp_format_amount( $amount, $decimals, acadp_get_payment_currency_settings() );	
}

/**
 * Set the number of decimal places per currency
 *
 * @since  1.0.0
 * @param  int    $decimals Number of decimal places.
 * @param  string $currency Payment currency.
 * @return int    $decimals
*/
function acadp_currency_decimal_count( $decimals = 2, $currency = 'USD' ) {
	switch ( $currency ) {
		case 'RIAL' :
		case 'JPY' :
		case 'TWD' :
		case 'HUF' :
			$decimals = 0;
			break;
	}

	return apply_filters( 'acadp_currency_decimal_count', $decimals, $currency );	
}

/**
 * Get the directory's set currency
 *
 * @since  1.0.0
 * @return string The currency code.
 */
function acadp_get_currency() {
	$currency_settings = get_option( 'acadp_currency_settings' );
	$currency = ! empty( $currency_settings[ 'currency' ] ) ? $currency_settings[ 'currency' ] : 'USD';
	
	return strtoupper( $currency );	
}

/**
 * Get the directory's set payment currency
 *
 * @since  1.5.4
 * @return string The currency code.
 */
function acadp_get_payment_currency() {
	$currency_settings = acadp_get_payment_currency_settings();
	$currency = ! empty( $currency_settings[ 'currency' ] ) ? $currency_settings[ 'currency' ] : 'USD';
	
	return strtoupper( $currency );	
}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since  1.0.0
 * @param  string $currency The currency string.
 * @return string           The symbol to use for the currency.
 */
function acadp_currency_symbol( $currency = '' ) {
	switch ( $currency ) {
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "NZD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		default :
			$symbol = $currency;
			break;
	}

	return apply_filters( 'acadp_currency_symbol', $symbol, $currency );	
}

/**
 * Formats the currency display.
 *
 * @since  1.0.0
 * @param  string $price             Paid Amount.
 * @param  array  $currency_settings Currency Settings.
 * @return string $formatted         Formatted amount with currency.
 */
function acadp_currency_filter( $price = '', $currency_settings = array() ) {
	if ( empty( $currency_settings ) ) {
		$currency_settings = get_option( 'acadp_currency_settings' );
	}
	
	$currency = ! empty( $currency_settings[ 'currency' ] ) ? $currency_settings[ 'currency' ] : 'USD';
	$position = $currency_settings['position'];

	$negative = $price < 0;

	if ( $negative ) {
		$price = substr( $price, 1 ); // Remove proceeding "-" -
	}

	$symbol = acadp_currency_symbol( $currency );

	if ( $position == 'before' ) {	
		switch ( $currency ) {
			case "GBP" :
			case "BRL" :
			case "EUR" :
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "NZD" :
			case "SGD" :
			case "JPY" :
				$formatted = $symbol . $price;
				break;
			default :
				$formatted = $currency . ' ' . $price;
				break;
		}
		
		$formatted = apply_filters( 'acadp_' . strtolower( $currency ) . '_currency_filter_before', $formatted, $currency, $price );		
	} else {	
		switch ( $currency ) {
			case "GBP" :
			case "BRL" :
			case "EUR" :
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
			case "JPY" :
				$formatted = $price . $symbol;
				break;
			default :
				$formatted = $price . ' ' . $currency;
				break;
		}
		
		$formatted = apply_filters( 'acadp_' . strtolower( $currency ) . '_currency_filter_after', $formatted, $currency, $price );		
	}

	if ( $negative ) {
		// Prepend the mins sign before the currency sign
		$formatted = '-' . $formatted;
	}

	return $formatted;	
}

/**
 * Formats the payment currency display.
 *
 * @since  1.5.4
 * @param  string $price Paid Amount.
 * @return string        Formatted amount with currency.
 */
function acadp_payment_currency_filter( $price = '' ) {
	return acadp_currency_filter( $price, acadp_get_payment_currency_settings() );
}

/**
 * Get the directory's payment currency settings.
 *
 * @since  1.5.4
 * @return array $currency_settings Array. Currency Settings.
 */
function acadp_get_payment_currency_settings() {	
	$gateway_settings = get_option( 'acadp_gateway_settings' );
	
	if ( ! empty( $gateway_settings[ 'currency' ] ) ) {	
		$currency_settings = array(
			'currency'            => $gateway_settings[ 'currency' ],
			'thousands_separator' => ! empty( $gateway_settings[ 'thousands_separator' ] ) ? $gateway_settings[ 'thousands_separator' ] : ',',
			'decimal_separator'   => ! empty( $gateway_settings[ 'decimal_separator' ] ) ? $gateway_settings[ 'decimal_separator' ] : '.',
			'position'            => $gateway_settings[ 'position' ]
		);		
	} else {	
		$currency_settings = get_option( 'acadp_currency_settings' );		
	}
	
	return $currency_settings;	
}

/**
 * Get the list of listings view options.
 *
 * @since  1.5.2
 * @return array $view_options List of view Options.
 */
function acadp_get_listings_view_options() {
	$general_settings = get_option( 'acadp_general_settings' );
	$listings_settings = get_option( 'acadp_listings_settings' );
	
	$options   = ! empty( $listings_settings['view_options'] ) ? $listings_settings['view_options'] : array();
	$options[] = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : $listings_settings['default_view'];
	$options   = array_unique( $options );
	
	if ( empty( $general_settings['has_map'] ) && array_key_exists( 'map', $options ) ) {
		unset( $options['map'] );
	}
	
	$views = array();
	
	foreach ( $options as $option ) {	
		switch ( $option ) {
			case 'list' :
				$views[ $option ] = __( 'List', 'advanced-classifieds-and-directory-pro' );
				break;
			case 'grid' :
				$views[ $option ] = __( 'Grid', 'advanced-classifieds-and-directory-pro' );
				break;
			case 'map' :
				$views[ $option ] = __( 'Map', 'advanced-classifieds-and-directory-pro' );
				break;
		}		
	}
	
	return $views;
}

/**
 * Get the view(layout) name the listings should be displayed.
 *
 * @since  1.0.0
 * @param  string $view Default View.
 * @return string $view Grid or List.
 */
function acadp_get_listings_current_view_name( $view ) {
	$general_settings = get_option( 'acadp_general_settings' );
	
	if ( isset( $_GET['view'] ) ) {
		$view = sanitize_text_field( $_GET['view'] );
	}
	
	$allowed_views = array( 'list', 'grid', 'map' );
	if ( ! in_array( $view, $allowed_views ) ) {
		$listings_settings = get_option( 'acadp_listings_settings' );
		$view = $listings_settings['default_view'];
	}
	
	if ( empty( $general_settings['has_map'] ) && 'map' == $view ) {
		$view = 'list';
	}
	
	return $view;				
}

/**
 * Get the highest priority ACADP template file that exists.
 *
 * @since  1.0.0
 * @param  string $name   The name of the specialized template.
 * @param  string $widget Name of the Widget(only if applicable).
 * @return string         The ACADP template file.
 */
function acadp_get_template( $name, $widget = '' ) {	
	$template_file = '';
	
	if ( '' !== $widget ) {	
		$templates = array(
			"acadp/widgets/$widget/$name",
			"acadp_templates/widgets/$widget/$name" // deprecated in 1.5.4
		);
		
		if ( ! $template_file = locate_template( $templates ) ) {		
			$template_file = ACADP_PLUGIN_DIR . "widgets/$widget/views/$name";
		}	
	} else {	
		$templates = array(
			"acadp/$name",
			"acadp_templates/$name" // deprecated in 1.5.4
		);
		
		if ( ! $template_file = locate_template( $templates ) ) {		
			$template_file = ACADP_PLUGIN_DIR . "public/partials/$name";
		}		
	}
	
	return apply_filters( 'acadp_get_template', $template_file, $name, $widget );
}

/**
 * List ACADP categories.
 *
 * @since  1.0.0
 * @param  array  $settings Settings args.
 * @return string           HTML code that contain categories list.
 */
function acadp_list_categories( $settings ) {	
	if ( $settings['depth'] <= 0 ) {
		return;
	}
		
	$args = array(
		'orderby'      => $settings['orderby'], 
    	'order'        => $settings['order'],
    	'hide_empty'   => ! empty( $settings['hide_empty'] ) ? 1 : 0, 
		'parent'       => $settings['term_id'],
		'hierarchical' => false
  	);
		
	$terms = get_terms( 'acadp_categories', $args );
	
	$html = '';
				
	if ( count( $terms ) > 0 ) {			
		--$settings['depth'];
			
		$html .= '<ul class="list-unstyled">';
							
		foreach ( $terms as $term ) {
			$settings['term_id'] = $term->term_id;
			
			$count = 0;
			if ( ! empty( $settings['hide_empty'] ) || ! empty( $settings['show_count'] ) ) {
				$count = acadp_get_listings_count_by_category( $term->term_id, $settings['pad_counts'] );
				
				if ( ! empty( $settings['hide_empty'] ) && 0 == $count ) continue;
			}
			
			$html .= '<li>'; 
			$html .= '<a href="' . acadp_get_category_page_link( $term ) . '" title="' . sprintf( __( "View all posts in %s", 'advanced-classifieds-and-directory-pro' ), $term->name ) . '" ' . '>';
			$html .= $term->name;
			if ( ! empty( $settings['show_count'] ) ) {
				$html .= ' (' . $count . ')';
			}
			$html .= '</a>';
			$html .= acadp_list_categories( $settings );
			$html .= '</li>';	
		}	
			
		$html .= '</ul>';					
	}		
			
	return $html;
}

/**
 * Get total listings count.
 *
 * @since  1.0.0
 * @param  int   $term_id    Custom Taxonomy term ID.
 * @param  bool  $pad_counts Pad the quantity of children in the count.
 * @return int               Listings count.
 */
function acadp_get_listings_count_by_category( $term_id, $pad_counts = true ) {	
	$args = array(
		'post_type' => 'acadp_listings',
		'posts_per_page' => -1,   		
		'post_status' => 'publish',
		'fields' =>'ids',
		'no_found_rows' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
   		'tax_query' => array(
			array(
				'taxonomy' => 'acadp_categories',
				'field' => 'term_id',
				'terms'=> $term_id,
				'include_children' => $pad_counts
			)
		)    		
	);

	$acadp_query = new WP_Query( $args );

	if ( $acadp_query->have_posts() ) {
		return count( $acadp_query->posts );
	}

	return 0;
}

/**
 * List ACADP locations.
 *
 * @since  1.0.0
 * @param  array  $settings Settings args.
 * @return string           HTML code that contain locations list.
 */
function acadp_list_locations( $settings ) {	
	if ( $settings['depth'] <= 0 ) {
		return;
	}
		
	$args = array(
		'orderby'      => $settings['orderby'], 
    	'order'        => $settings['order'],
    	'hide_empty'   => ! empty( $settings['hide_empty'] ) ? 1 : 0, 
		'parent'       => $settings['term_id'],
		'hierarchical' => false
  	);
		
	$terms = get_terms( 'acadp_locations', $args );
	
	$html = '';
				
	if ( count( $terms ) > 0 ) {			
		--$settings['depth'];
			
		$html .= '<ul class="list-unstyled">';
							
		foreach ( $terms as $term ) {
			$settings['term_id'] = $term->term_id;
			
			$html .= '<li>'; 
			$html .= '<a href="' . esc_url( acadp_get_location_page_link( $term ) ) . '" title="' . sprintf( __( "View all posts in %s", 'advanced-classifieds-and-directory-pro' ), $term->name ) . '" ' . '>';
			$html .= $term->name;
			if( ! empty( $settings['show_count'] ) ) {
				$html .= ' (' . acadp_get_listings_count_by_location( $term->term_id, $settings['pad_counts'] ) . ')';
			}
			$html .= '</a>';
			$html .= acadp_list_locations( $settings );
			$html .= '</li>';	
		}	
			
		$html .= '</ul>';					
	}		
			
	return $html;
}

/**
 * Get total listings count.
 *
 * @since  1.0.0
 * @param  int   $term_id    Custom Taxonomy term ID.
 * @param  bool  $pad_counts Pad the quantity of children in the count.
 * @return int               Listings count.
 */
function acadp_get_listings_count_by_location( $term_id, $pad_counts = true ) {	
	$args = array(
		'post_type' => 'acadp_listings',
		'posts_per_page' => -1,   		
		'post_status' => 'publish',
		'fields' =>'ids',
		'no_found_rows' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
   		'tax_query' => array(
			array(
				'taxonomy' => 'acadp_locations',
				'field' => 'term_id',
				'terms' => $term_id,
				'include_children' => $pad_counts
			)
		)    		
	);

	$acadp_query = new WP_Query( $args );

	if ( $acadp_query->have_posts() ) {
		return count( $acadp_query->posts );
	}

	return 0;
}

/**
 * Insert/Update listing views count.
 *
 * @since 1.0.0
 * @param int   $post_id Post ID.
 */
function acadp_update_listing_views_count( $post_id ) {
    $user_ip = $_SERVER['REMOTE_ADDR']; // retrieve the current IP address of the visitor
    $key     = $user_ip . '_acadp_' . $post_id; // combine post ID & IP to form unique key
    $value   = array( $user_ip, $post_id ); // store post ID & IP as separate values (see note)
    $visited = get_transient( $key ); // get transient and store in variable

    // check to see if the Post ID/IP ($key) address is currently stored as a transient
    if ( false === $visited ) {
        // store the unique key, Post ID & IP address for 12 hours if it does not exist
        set_transient( $key, $value, 60*60*12 );

        // now run post views function
        $count_key = 'views';
        $count = get_post_meta( $post_id, $count_key, true );
        if ( '' == $count ) {
            $count = 0;
            delete_post_meta( $post_id, $count_key );
            add_post_meta( $post_id, $count_key, '0' );
        } else {
            $count++;
            update_post_meta( $post_id, $count_key, $count );
        }
    }
}

/**
 * Get orderby list.
 *
 * @since  1.0.0
 * @return array $options A list of the orderby options.
 */
function acadp_get_listings_orderby_options() {
	$general_settings = get_option( 'acadp_general_settings' );
	
	$options = array(
		'title-asc'  => __( "A to Z ( title )", 'advanced-classifieds-and-directory-pro' ),
		'title-desc' => __( "Z to A ( title )", 'advanced-classifieds-and-directory-pro' ),
		'date-desc'  => __( "Recently added ( latest )", 'advanced-classifieds-and-directory-pro' ),
		'date-asc'   => __( "Date added ( oldest )", 'advanced-classifieds-and-directory-pro' ),
		'views-desc' => __( "Most viewed", 'advanced-classifieds-and-directory-pro' ),
		'views-asc'  => __( "Less viewed", 'advanced-classifieds-and-directory-pro' )			
	);
	
	if ( ! empty( $general_settings['has_price'] ) ) {
		$options['price-asc']  = __( "Price ( low to high )", 'advanced-classifieds-and-directory-pro' );
		$options['price-desc'] = __( "Price ( high to low )", 'advanced-classifieds-and-directory-pro' );						
	}
	
	return apply_filters( 'acadp_get_listings_orderby_options', $options );	
}

/**
 * Get orderby list.
 *
 * @since      1.0.0
 * @deprecated 1.5.6
 * @return     array $options A list of the orderby options.
 */
function acadp_get_orderby_options() {
	return acadp_get_listings_orderby_options();	
}

/**
 * Get the current listings order.
 *
 * @since  1.5.5
 * @param  string $default_order Default Order.
 * @return string $order         Listings Order.
 */
function acadp_get_listings_current_order( $default_order = '' ) {
	$order = $default_order;
	
	if ( isset( $_GET['sort'] ) ) {
		$order = sanitize_text_field( $_GET['sort'] );
	} elseif ( isset( $_GET['order'] ) ) {
		$order = sanitize_text_field( $_GET['order'] );
	}

	return apply_filters( 'acadp_get_listings_current_order', $order );				
}

/**
 * Get total listings count of the current user.
 *
 * @since  1.0.0
 * @return int   Total listings count.
 */
function acadp_get_user_total_listings() {
	global $wpdb;

	$where = get_posts_by_author_sql( 'acadp_listings', true, get_current_user_id(), false );
	$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts $where" );

  	return $count;	
}

/**
 * Get active listings count of the current user.
 *
 * @since  1.0.0
 * @return int   Active listings count.
 */
function acadp_get_user_total_active_listings() {
	global $wpdb;

	$where = get_posts_by_author_sql( 'acadp_listings', true, get_current_user_id(), true );
	$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts $where" );

  	return $count;	
}

/**
 * Parse the video URL and determine it's valid embeddable URL for usage.
 *
 * @since  1.0.0
 * @param  string $url YouTube / Vimeo URL.
 * @return array       An array of video metadata if found.
 */
function acadp_parse_videos( $url ) {	
	$embeddable_url = '';
	
	// Check for YouTube
	$is_youtube = preg_match( '/youtu\.be/i', $url ) || preg_match( '/youtube\.com\/watch/i', $url );
	
	if ( $is_youtube ) {
    	$pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
    	preg_match( $pattern, $url, $matches );
    	if ( count( $matches ) && strlen( $matches[7] ) == 11 ) {
      		$embeddable_url = 'https://www.youtube.com/embed/' . $matches[7];
    	}
  	}
	
	// Check for Vimeo
	$is_vimeo = preg_match( '/vimeo\.com/i', $url );
	
	if ( $is_vimeo ) {
    	$pattern = '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/';
    	preg_match( $pattern, $url, $matches );
    	if ( count( $matches ) ) {
      		$embeddable_url = 'https://player.vimeo.com/video/' . $matches[2];
    	}
  	}
	
	// Return
	return $embeddable_url;
}

/**
 * Get current page number.
 *
 * @since  1.0.0
 * @return int   $paged The current page number.
 */
function acadp_get_page_number() {
	global $paged;
	
	if ( get_query_var( 'paged' ) ) {
    	$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
    	$paged = get_query_var( 'page' );
	} else {
		$paged = 1;
	}
    	
	return absint( $paged );		
}

/**
  * Removes an item or list from the query string.
  *
  * @since  1.0.0
  * @param  string|array $key   Query key or keys to remove.
  * @param  bool|string  $query Optional. When false uses the $_SERVER value. Default false.
  * @return string              New URL query string.
  */
function acadp_remove_query_arg( $key, $query = false ) {
	if ( is_array( $key ) ) { // removing multiple keys
		foreach ( $key as $k ) {
			$query = str_replace( '#038;', '&', $query );
			$query = add_query_arg( $k, false, $query );
		}
		
		return $query;
	}
		
	return add_query_arg( $key, false, $query );	
}

/**
  * Verify the captcha answer.
  *
  * @since  1.0.0
  * @param  string $form ACADP Form Name.
  * @return bool         True if not a bot, false if bot.
  */
function acadp_is_human( $form ) {
	$recaptcha_settings = get_option( 'acadp_recaptcha_settings' );
	
	$has_captcha = false;
	if ( isset( $recaptcha_settings['forms'] ) && '' !== $recaptcha_settings['site_key'] && '' !== $recaptcha_settings['secret_key'] ) {
		if ( in_array( $form, $recaptcha_settings['forms'] ) ) {
			$has_captcha = true;
		}
	}
	
	if ( $has_captcha ) {	
		$response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( $_POST['g-recaptcha-response'] ) : '';
		
		if ( '' !== $response ) {			
			// make a GET request to the Google reCAPTCHA Server
			$request = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_settings['secret_key'] . '&response=' . $response . '&remoteip=' . $_SERVER["REMOTE_ADDR"] );
			
			// get the request response body
			$response_body = wp_remote_retrieve_body( $request );
			
			$result = json_decode( $response_body, true );
			
			// return true or false, based on users input
			return ( true == $result['success'] ) ? true : false;	
		} else {		
			return false;			
		}	
	}
	
	return true;	
}

/**
 * Get payment gateways.
 *
 * @since  1.0.0
 * @return array $gateways A list of the available gateways.
 */
function acadp_get_payment_gateways() {
	$gateways = apply_filters( 'acadp_payment_gateways', array( 'offline' => __( 'Offline Payment', 'advanced-classifieds-and-directory-pro' ) ) );	
	return $gateways;	
}

/**
 * Update Order details. Send emails to site and listing owners
 * when order completed.
 *
 * @since 1.0.0
 * @param array $order Order details.
 */
function acadp_order_completed( $order ) {	
	// update order details
	update_post_meta( $order['id'], 'payment_status', 'completed' );
	update_post_meta( $order['id'], 'transaction_id', $order['transaction_id'] );

	//Mel: 27/01/22
	update_post_meta( $order['id'], 'block_number', $order['block_number'] );
	update_post_meta( $order['id'], 'gas_used', $order['gas_used'] );
	update_post_meta( $order['id'], 'user_wallet', $order['user_wallet'] );
	update_post_meta( $order['id'], 'token_uri', $order['token_uri'] );
	update_post_meta( $order['id'], 'file_hash', $order['file_hash'] );
	update_post_meta( $order['id'], 'contract_address', $order['contract_address'] );
	
	// If the order has featured
	$featured = get_post_meta( $order['id'], 'featured', true );
	
	if ( ! empty( $featured ) ) {
		$post_id = get_post_meta( $order['id'], 'listing_id', true );
		update_post_meta( $post_id, 'featured', 1 );

		//Mel: 27/01/22. Add blockchain tx hash so that it can be viewed on listing page
		update_post_meta( $post_id, 'tx_hash', $order['transaction_id'] );
	}
	
	// Hook for developers
	do_action( 'acadp_order_completed', $order['id'] );
		
	// send emails
	acadp_email_listing_owner_order_completed( $order['id'] );
	acadp_email_admin_payment_received( $order['id'] );		
}

/**
 * Rotate images to the correct orientation.
 *
 * @since  1.5.4
 * @param  array $file $_FILES array
 * @return array	   $_FILES array in the correct orientation
 */
function acadp_exif_rotate( $file ) {
	if ( ! function_exists( 'exif_read_data' ) ) {
		return $file;
	}
	
	$exif = @exif_read_data( $file['tmp_name'] );
	$exif_orient = isset( $exif['Orientation'] ) ? $exif['Orientation'] : 0;
	$rotate_image = 0;

	if ( 6 == $exif_orient ) {
		$rotate_image = 90;
	} elseif ( 3 == $exif_orient ) {
		$rotate_image = 180;
	} elseif ( 8 == $exif_orient ) {
		$rotate_image = 270;
	}

	if ( $rotate_image ) {	
		if ( class_exists( 'Imagick' ) ) {		
			$imagick = new Imagick();
			$imagick_pixel = new ImagickPixel();
			$imagick->readImage( $file['tmp_name'] );
			$imagick->rotateImage( $imagick_pixel, $rotate_image );
			$imagick->setImageOrientation( 1 );
			$imagick->writeImage( $file['tmp_name'] );
			$imagick->clear();
			$imagick->destroy();		
		} else {		
			$rotate_image = -$rotate_image;
			
			switch ( $file['type'] ) {
				case 'image/jpeg' :
					if ( function_exists( 'imagecreatefromjpeg' ) ) {
						$source = imagecreatefromjpeg( $file['tmp_name'] );
						$rotate = imagerotate( $source, $rotate_image, 0 );
						imagejpeg( $rotate, $file['tmp_name'] );
					}
					break;
				case 'image/png' :
					if ( function_exists( 'imagecreatefrompng' ) ) {
						$source = imagecreatefrompng( $file['tmp_name'] );
						$rotate = imagerotate( $source, $rotate_image, 0 );
						imagepng( $rotate, $file['tmp_name'] );
					}
					break;
				case 'image/gif' :
					if ( function_exists( 'imagecreatefromgif' ) ) {
						$source = imagecreatefromgif( $file['tmp_name'] );
						$rotate = imagerotate( $source, $rotate_image, 0 );
						imagegif( $rotate, $file['tmp_name'] );
					}
					break;
			}
		}	
	}
	
	return $file;
}

/**
 * Retrieve the listing status in localized format.
 *
 * @since  1.5.4
 * @param  string $status Listing status.
 * @return string $status Localized listing status.
 */
function acadp_get_listing_status_i18n( $status ) {
	$post_status = get_post_status_object( $status );			
	return $post_status->label;	
}

/**
 * Check if listing specific widgets are enabled.
 *
 * @since  1.5.5
 * @return bool  $found 0 or 1.
 */
function acadp_has_active_listing_widgets() {
	// Vars
	$sidebars_widgets = get_option( 'sidebars_widgets' );

	$listing_widgets = array(
		ACADP_PLUGIN_NAME.'-widget-listing-video',
		ACADP_PLUGIN_NAME.'-widget-listing-address',
		ACADP_PLUGIN_NAME.'-widget-listing-contact'
	);

	$found = 0;
	
	// Loop through active widgets list
	foreach ( $sidebars_widgets as $sidebar => $widgets ) {
		// Check if the sidebar is active
		if ( is_active_sidebar( $sidebar ) ) {
			// Loop through widgets registered inside this sidebar
			foreach ( $widgets as $widget ) {
				// Loop through our listing specific widgets list
				foreach ( $listing_widgets as $listing_widget ) {
					// Check if the current widget belongs to one of our listing specific widgets
					if ( FALSE !== strpos( $widget, $listing_widget ) ) {
						$found = 1;
						break;
					}
				}
			}
		}
	}
	
	return $found;
}

/**
 * Get custom field types.
 *
 * @since  1.5.8
 * @return array Array of custom field types.
 */
function acadp_get_custom_field_types() {
	$types = array(
		'text'     => __( 'Text', 'advanced-classifieds-and-directory-pro' ),
		'textarea' => __( 'Text Area', 'advanced-classifieds-and-directory-pro' ),
		'select'   => __( 'Select', 'advanced-classifieds-and-directory-pro' ),
		'checkbox' => __( 'Checkbox', 'advanced-classifieds-and-directory-pro' ),
		'radio'    => __( 'Radio Button', 'advanced-classifieds-and-directory-pro' ),
		'url'      => __( 'URL', 'advanced-classifieds-and-directory-pro' )
	);
		
	// Return
	return apply_filters( 'acadp_custom_field_types', $types );
}

/**
 * Get custom fields.
 *
 * @since  1.5.8
 * @param  int|array $terms     Category ID(s).
 * @return array     $field_ids Array of custom field ids.
 */
function acadp_get_custom_field_ids( $terms = array() ) {
	$field_ids = array();

	// Get global fields
	$args = array(
		'post_type' => 'acadp_fields',
		'post_status' => 'publish',
		'posts_per_page' => 500,	
		'fields' => 'ids',
		'no_found_rows' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'meta_query' => array(
			array(
				'key' => 'associate',
				'value' => 'form'
			),
		)
	);

	$acadp_query = new WP_Query( $args );

	if ( $acadp_query->have_posts() ) {
		$field_ids = $acadp_query->posts;
	}		
	
	// Get category fields	
	if ( ! empty( $terms ) ) {	
		$tax_queries = array(
			array(
				'taxonomy' => 'acadp_categories',
				'field' => 'term_id',
				'terms' => is_array( $terms ) ? (int) $terms[0] : (int) $terms,
				'include_children' => false,
			),
		);

		$args = array(
			'post_type' => 'acadp_fields',
			'post_status' => 'publish',
			'posts_per_page' => 500,	
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query' => apply_filters( 'acadp_custom_fields_tax_queries', $tax_queries, $terms )
		);		
		
		$acadp_query = new WP_Query( $args );

		if ( $acadp_query->have_posts() ) {
			$field_ids = array_merge( $field_ids, $acadp_query->posts );
			$field_ids = array_unique( $field_ids );
		}	
	}	
	
	return $field_ids;
}

/**
 * Get custom fields for the listings archive pages.
 *
 * @since  1.8.0
 * @return array $fields Array of custom fields.
 */
function acadp_get_custom_fields_listings_archive() {
	$args = array(
		'post_type' => 'acadp_fields',
		'post_status' => 'publish',
		'posts_per_page' => 100,
		'meta_query' => array(
			array(
				'key' => 'listings_archive',
				'value'	=> 1,
				'compare' => '=',
			)
		),
		'meta_key' => 'order',
		'orderby' => 'meta_value_num',				
		'order' => 'ASC',	
		'no_found_rows' => true,
		'update_post_term_cache' => false,
		'suppress_filters' => false				
	);
	
	$fields = get_posts( $args );

	return $fields;
}

/**
 * Get user slug from URL.
 *
 * @since  1.6.0
 * @return string $user_slug User slug.
 */
function acadp_get_user_slug() {
	$user_slug = get_query_var( 'acadp_user' );
	
	if ( ! empty( $user_slug ) ) {
		$user_slug = acadp_base64_decode( $user_slug );
	}
		
	// Return
	return $user_slug;
}

/**
 * Check if Yoast SEO plugin is active and ACADP can use that.
 *
 * @since  1.6.1
 * @return bool  $can_use_yoast "true" if can use Yoast, "false" if not.
 */
function acadp_can_use_yoast() {
	$can_use_yoast = false;

	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
	if ( in_array( 'wordpress-seo/wp-seo.php', $active_plugins ) || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins ) ) {
		$can_use_yoast = true;
	}

	return $can_use_yoast;
}

/**
 * Get custom field display text.
 *
 * @since  1.6.1
 * @param  string  $value User input.
 * @param  WP_POST $field Custom field object.
 * @return string         String to display.
 */
function acadp_get_custom_field_display_text( $value, $field ) {
	$display_text = '';

	$field_meta = get_post_meta( $field->ID );

	if ( 'select' == $field_meta['type'][0] || 'radio' == $field_meta['type'][0] ) {
		$choices = explode( "\n", $field_meta['choices'][0] );

		foreach ( $choices as $choice ) {
			if ( false !== strpos( $choice, ':' ) ) {
				$_choice = explode( ':', $choice );
				$_choice = array_map( 'trim', $_choice );

				$_value  = $_choice[0];
				$_label  = $_choice[1];
			} else {
				$_value  = trim( $choice );
				$_label  = $_value;
			}

			if ( trim( $value ) == $_value ) {
				$display_text = $_label;
				break;
			}
		}
	} elseif ( 'checkbox' == $field_meta['type'][0] ) {
		$choices = explode( "\n", $field_meta['choices'][0] );

		$values  = explode( "\n", $value );
		$values  = array_map( 'trim', $values );
		$_values = array();

		foreach ( $choices as $choice ) {
			if ( false !== strpos( $choice, ':' ) ) {
				$_choice = explode( ':', $choice );
				$_choice = array_map( 'trim', $_choice );

				$_value  = $_choice[0];
				$_label  = $_choice[1];
			} else {
				$_value  = trim( $choice );
				$_label  = $_value;
			}

			if ( in_array( $_value, $values ) ) {
				$_values[] = $_label;
			}
		}

		$display_text = implode( ', ', $_values );
	} elseif ( 'url' == $field_meta['type'][0] && ! filter_var( $value, FILTER_VALIDATE_URL ) === FALSE ) {
		$nofollow = ! empty( $field_meta['nofollow'][0] ) ? ' rel="nofollow"' : '';
		$display_text = sprintf( '<a href="%1$s" target="%2$s"%3$s>%1$s</a>', $value, $field_meta['target'][0], $nofollow );
	} else {
		$display_text = $value;
	}

	return $display_text;
}

/**
 * Get shortcode fields.
 *
 * @since  1.7.3
 * @return array $fields Array of shortcode fields.
 */
function acadp_get_shortcode_fields() {
	$general_settings    = get_option( 'acadp_general_settings' );		
	$listings_settings   = get_option( 'acadp_listings_settings' ); 
	$locations_settings  = get_option( 'acadp_locations_settings' ); 
	$categories_settings = get_option( 'acadp_categories_settings' ); 

	$has_location = empty( $general_settings['has_location'] ) ? 0 : 1;
	$has_price    = empty( $general_settings['has_price'] )    ? 0 : 1;

	$fields = array(
		'listings' => array(
			'title'    => __( 'Listings', 'advanced-classifieds-and-directory-pro' ),
			'sections' => array(				
				'general' => array(
					'title'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'view',
							'label'       => __( 'Select layout', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'list' => __( 'List', 'advanced-classifieds-and-directory-pro' ),
								'grid' => __( 'Grid', 'advanced-classifieds-and-directory-pro' ),
								'map'  => __( 'Map', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['default_view']
						),					
						array(
							'name'        => 'location',
							'label'       => __( 'Select location', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'locations',
							'options'     => array(),
							'value'       => max( 0, $general_settings['base_location'] )
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Select category', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'categories',
							'options'     => array(),
							'value'       => 0
						),
						array(
							'name'        => 'filterby',
							'label'       => __( 'Filter by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								''         => __( 'None', 'advanced-classifieds-and-directory-pro' ),
								'featured' => __( 'Featured', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => ''
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'title' => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
								'date'  => __( 'Date posted', 'advanced-classifieds-and-directory-pro' ),
								'price' => __( 'Price', 'advanced-classifieds-and-directory-pro' ),
								'views' => __( 'Views count', 'advanced-classifieds-and-directory-pro' ),
								'rand'  => __( 'Random sort', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
								'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['order']
						),
						array(
							'name'        => 'columns',
							'label'       => __( 'Number of columns', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => $listings_settings['columns']
						),
						array(
							'name'        => 'listings_per_page',
							'label'       => __( 'Listings per page', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 0,
							'max'         => 500,
							'step'        => 1,
							'value'       => ! empty( $listings_settings['listings_per_page'] ) ? $listings_settings['listings_per_page'] : -1
						),
						array(
							'name'        => 'featured',
							'label'       => __( 'Show featured', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( 'Show or hide featured listings at the top of normal listings. This setting has no value when "Filter by" option is set to "Featured".', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'header',
							'label'       => __( 'Show header', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( 'Header = Videos count, Views switcher, Sort by dropdown', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'pagination',
							'label'       => __( 'Show pagination', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						)
					)
				)        
			)
		),
		'locations' => array(
			'title'    => __( 'Locations', 'advanced-classifieds-and-directory-pro' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'parent',
							'label'       => __( 'Select parent', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'locations',
							'options'     => array(),
							'value'       => max( 0, $general_settings['base_location'] )
						),
						array(
							'name'        => 'columns',
							'label'       => __( 'Number of columns', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => $locations_settings['columns']
						),
						array(
							'name'        => 'depth',
							'label'       => __( 'Depth', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( 'Enter the maximum number of location sub-levels to show.', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'number',
							'min'         => 1,
							'max'         => 5,
							'step'        => 1,
							'value'       => $locations_settings['depth']
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'id'    => __( 'ID', 'advanced-classifieds-and-directory-pro' ),
								'count' => __( 'Count', 'advanced-classifieds-and-directory-pro' ),
								'name'  => __( 'Name', 'advanced-classifieds-and-directory-pro' ),
								'slug'  => __( 'Slug', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $locations_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
								'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $locations_settings['order']
						),
						array(
							'name'        => 'show_count',
							'label'       => __( 'Show listings count?', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => empty( $locations_settings['show_count'] ) ? 0 : 1
						),
						array(
							'name'        => 'hide_empty',
							'label'       => __( 'Hide empty locations?', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => empty( $locations_settings['hide_empty'] ) ? 0 : 1
						),
					)
				)
			)
		),
		'categories' => array(
			'title'    => __( 'Categories', 'advanced-classifieds-and-directory-pro' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'parent',
							'label'       => __( 'Select parent', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'categories',
							'options'     => array(),
							'value'       => 0
						),
						array(
							'name'        => 'view',
							'label'       => __( 'Select layout', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'image_grid' => __( 'Thumbnail grid', 'advanced-classifieds-and-directory-pro' ),
								'text_list'  => __( 'Text-only menu items', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $categories_settings['view']
						),
						array(
							'name'        => 'columns',
							'label'       => __( 'Number of columns', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => $categories_settings['columns']
						),
						array(
							'name'        => 'depth',
							'label'       => __( 'Depth', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( 'Enter the maximum number of category sub-levels to show in the "Text-only Menu Items" view.', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'number',
							'min'         => 1,
							'max'         => 5,
							'step'        => 1,
							'value'       => $categories_settings['depth']
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'id'    => __( 'ID', 'advanced-classifieds-and-directory-pro' ),
								'count' => __( 'Count', 'advanced-classifieds-and-directory-pro' ),
								'name'  => __( 'Name', 'advanced-classifieds-and-directory-pro' ),
								'slug'  => __( 'Slug', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $categories_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
								'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $categories_settings['order']
						),
						array(
							'name'        => 'show_count',
							'label'       => __( 'Show categories count?', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => empty( $categories_settings['show_count'] ) ? 0 : 1
						),
						array(
							'name'        => 'hide_empty',
							'label'       => __( 'Hide empty categories?', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => empty( $categories_settings['hide_empty'] ) ? 0 : 1
						),
					)
				)
			)
		),
		'search_form' => array(
			'title'    => __( 'Search Form', 'advanced-classifieds-and-directory-pro' ),
			'sections' => array(				
				'general' => array(
					'title'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'style',
							'label'       => __( 'Select layout', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'vertical' => __( 'Vertical', 'advanced-classifieds-and-directory-pro' ),
								'inline'   => __( 'Inline', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => 'inline'
						),
						array(
							'name'        => 'location',
							'label'       => __( 'Search by Location', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $has_location
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Search by Category', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'custom_fields',
							'label'       => __( 'Search by Custom Fields', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'price',
							'label'       => __( 'Search by Price', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $has_price
						) 
					)
				)
			)        
		)		
	);

	$fields = apply_filters( 'acadp_shortcode_fields', $fields );

	// If Location (or) Price disabled
	if ( ! $has_location ) {
		unset( $fields['locations'] );
	}

	foreach ( $fields as $shortcode => $params ) {
		foreach ( $params['sections'] as $name => $section ) {
			foreach ( $section['fields'] as $index => $field ) {
				// Remove Location
				if ( ! $has_location && 'location' == $field['name']  ) {
					unset( $fields[ $shortcode ]['sections'][ $name ]['fields'][$index] );
				}

				// Remove Price
				if ( ! $has_price && 'price' == $field['name']  ) {
					unset( $fields[ $shortcode ]['sections'][ $name ]['fields'][$index] );
				}
			}
		}
	}

	return $fields;
}

/**
 * Sanitize the array inputs.
 *
 * @since  1.7.3
 * @param  array $value Input array.
 * @return array        Sanitized array.
 */
function acadp_sanitize_array( $value ) {
	return ! empty( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
}

/**
 * Sanitize the integer inputs, accepts empty values.
 *
 * @since  1.7.3
 * @param  string|int $value Input value.
 * @return string|int        Sanitized value.
 */
function acadp_sanitize_int( $value ) {
	$value = intval( $value );
	return ( 0 == $value ) ? '' : $value;	
}

/**
 * Sanitize the thousands separator field.
 *
 * @since  1.7.3
 * @param  array $value Input value.
 * @return array        Sanitized value.
 */
function acadp_sanitize_thousands_separator( $value ) {
	return ( ' ' !== $value ) ? sanitize_text_field( $value ) : ' ';
}

/**
 * Get the location coordinates from the location term id.
 *
 * @since  1.8.0
 * @param  string $term_id     Location ID.
 * @return array  $coordinates The location coordinates.
 */
function acadp_get_location_coordinates( $term_id ) {
	$coordinates = array(
		'latitude'  => 0,
		'longitude' => 0
	);

	if ( $term_id > 0 ) {
		if ( false === ( $transient_data = get_transient( 'acadp_location_coordinates' ) ) ) {
			$transient_data = array();
		} else {			
			foreach ( $transient_data as $index => $data ) {
				if ( $index == $term_id ) {					
					return $data;
				}
			}

			delete_transient( 'acadp_location_coordinates' );
		}

		$term = get_term( $term_id );
		$response = wp_remote_get( 'https://nominatim.openstreetmap.org/search.php?q=' . urlencode( $term->name ) . '&addressdetails=1&limit=1&format=jsonv2' );

		if ( ! is_wp_error( $response ) ) {
			$response = json_decode( $response['body'] );

			if ( count( $response ) > 0 ) {
				$coordinates = array(
					'latitude'  => $response[0]->lat,
					'longitude' => $response[0]->lon
				);

				$transient_data[ $term_id ] = $coordinates;
				set_transient( 'acadp_location_coordinates', $transient_data, 30 * DAY_IN_SECONDS );
			}
		}
	}

	return $coordinates;
}

/**
 * Display plugin status messages.
 *
 * @since 1.8.0
 */
function acadp_status_messages() {
	if ( ! isset( $_GET['status'] ) ) return;

	$page_settings = get_option( 'acadp_page_settings' );
	
	$checkout_page_id = (int) $page_settings['checkout'];
	$status = sanitize_text_field( $_GET['status']  );

	if ( $checkout_page_id > 0 && is_page( $checkout_page_id ) ) {
		if ( 'publish' != $status && 'renewed' != $status ) return;
	}

	switch ( $status ) {
		case 'draft':
			printf(
				'<div class="alert alert-info" role="alert">%s</div>',
				esc_html__( 'Listing saved successfully.', 'advanced-classifieds-and-directory-pro' )
			);
			break;
		case 'permission_denied':
			printf(
				'<div class="alert alert-danger" role="alert">%s</div>',
				esc_html__( "Sorry, you don't have permission to do this action.", 'advanced-classifieds-and-directory-pro' )
			);
			break;
		case 'publish':
			printf(
				'<div class="alert alert-info" role="alert">%s</div>',
				esc_html__( 'Listing published successfully.', 'advanced-classifieds-and-directory-pro' )
			);
			break;
		case 'updated':
			printf(
				'<div class="alert alert-info" role="alert">%s</div>',
				esc_html__( 'Listing updated successfully.', 'advanced-classifieds-and-directory-pro' )
			);
			break;
		case 'pending':
			printf(
				'<div class="alert alert-info" role="alert">%s</div>',
				esc_html__( "Listing submitted successfully and it's pending review. This review process could take up to 48 hours. Please be patient.", 'advanced-classifieds-and-directory-pro' )
			);
			break;
		case 'renewed':
			printf(
				'<div class="alert alert-info" role="alert">%s</div>',
				esc_html__( 'Listing renewed successfully.', 'advanced-classifieds-and-directory-pro' )
			);
			break;
		case 'deleted':
			printf(
				'<div class="alert alert-info" role="alert">%s</div>',
				esc_html__( 'Listing deleted successfully.', 'advanced-classifieds-and-directory-pro' )
			);
			break;
	}
}
