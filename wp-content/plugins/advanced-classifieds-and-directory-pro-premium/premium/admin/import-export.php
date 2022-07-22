<?php
 
/**
 * CSV Import/Export.
 *
 * @link    https://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Admin_Import_Export class.
 *
 * @since 1.7.5
 */ 
class ACADP_Premium_Admin_Import_Export {

	/**
	 * Array of attachments created during the import.
	 *	 
	 * @since  1.7.5
	 * @access protected
	 * @var    array
	 */
	protected $attached = array();

	/**
	 * Array of coordinates created during the import.
	 *	 
	 * @since  1.8.0
	 * @access protected
	 * @var    array
	 */
	protected $coordinates = array();

	/**
	 * Import logs.
	 *	 
	 * @since  1.7.5
	 * @access protected
	 * @var    array
	 */
	protected $logs = array(
		'messages' => array(),
		'errors'   => 0,
		'rejected' => 0
	);

	/**
	 * Manage form submissions.
	 *
	 * @since 1.7.5
	 */
	public function admin_init() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['acadp_export_nonce'] ) && wp_verify_nonce( $_POST['acadp_export_nonce'], 'acadp_do_export' ) ) {
			// Export Listings
			if ( __( 'Export Listings', 'advanced-classifieds-and-directory-pro' ) == $_POST['action'] ) {
				$this->export_listings();
			}

			// Download Images
			if ( __( 'Download Images', 'advanced-classifieds-and-directory-pro' ) == $_POST['action'] ) {
				$this->download_images();
			}
		}
	}

	/**
	 * Add "CSV Import / Export" menu.
	 *
	 * @since 1.7.5
	 */
	public function admin_menu() {	
		add_submenu_page(
			'advanced-classifieds-and-directory-pro',
			__( 'Advanced Classifieds and Directory Pro - Import/Export', 'advanced-classifieds-and-directory-pro' ),
			__( 'Import/Export', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'acadp_import_export',
			array( $this, 'display_form' )
		);				
	}	

	/**
	 * Display form.
	 *
	 * @since 1.7.5
	 */
	public function display_form() {
		$tabs = array(
			'import' => __( 'Import', 'advanced-classifieds-and-directory-pro' ),
			'export' => __( 'Export', 'advanced-classifieds-and-directory-pro' )
		);
		
		$active_tab = isset( $_GET['tab'] ) ?  sanitize_text_field( $_GET['tab'] ) : 'import';

		require_once ACADP_PLUGIN_DIR . 'premium/admin/partials/import-export.php';	
	}

	/**
	 * Import Listings.
	 *
	 * @since 1.7.5
	 */
	public function ajax_callback_import() {
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );

		$data = array(
			'error'   => 0,
			'message' => '',
			'html'    => ''
		);

		if ( empty( $_POST['csv_file'] ) ) {
			$data['error']   = 1;
			$data['message'] = __( 'Invalid CSV File!', 'advanced-classifieds-and-directory-pro' );

			echo wp_json_encode( $data );
			wp_die();
		}
		
		$attributes = array(
			'csv_file'          => (int) $_POST['csv_file'],
			'images_file'       => isset( $_POST['images_file'] ) ? (int) $_POST['images_file'] : 0,
			'images_dir'        => '',
			'columns_separator' => isset( $_POST['columns_separator'] ) ? sanitize_text_field( $_POST['columns_separator'] ) : ',',
			'values_separator'  => isset( $_POST['values_separator'] ) ? sanitize_text_field( $_POST['values_separator'] ) : ';',
			'add_new_term'      => isset( $_POST['add_new_term'] ) ? (int) $_POST['add_new_term'] : 0,
			'add_new_user'      => isset( $_POST['add_new_user'] ) ? (int) $_POST['add_new_user'] : 0,
			'do_geocode'        => isset( $_POST['do_geocode'] ) ? (int) $_POST['do_geocode'] : 0,
			'step'              => isset( $_POST['step'] ) ? (int) $_POST['step'] : 1,				
			'collation_fields'  => $this->get_collation_fields(),
			'collated_fields'   => isset( $_POST['collated_fields'] ) ? array_map( 'sanitize_text_field', $_POST['collated_fields'] ) : array(),
			'logs'              => ''
		);		

		$csv_data = $this->parse_csv( $attributes );

		if ( 1 == $attributes['step'] ) {
			$attributes['csv_headers'] = $csv_data['headers'];

			ob_start();
			require_once ACADP_PLUGIN_DIR . 'premium/admin/partials/form-import-step2.php';	
			$data['html'] = ob_get_clean();
		} elseif ( 2 == $attributes['step'] ) {
			if ( ! in_array( 'post_title', $attributes['collated_fields'] ) || ! in_array( 'acadp_categories', $attributes['collated_fields'] ) ) {
				$data['error']   = 1;

				if ( in_array( 'post_title', $attributes['collated_fields'] ) ) {
					$data['message'] = __( "Categories field wasn't collated", 'advanced-classifieds-and-directory-pro' );
				} elseif ( in_array( 'acadp_categories', $attributes['collated_fields'] ) ) {
					$data['message'] = __( "Listing Title field wasn't collated", 'advanced-classifieds-and-directory-pro' );
				} else {
					$data['message'] = __( "Listing Title, Categories fields wasn't collated", 'advanced-classifieds-and-directory-pro' );
				}

				echo wp_json_encode( $data );
				wp_die();
			}

			$attributes['images_dir'] = $this->unzip_file( $attributes );

			$this->logs['messages'][] = sprintf( 
				__( 'Import started, number of available rows in file: %d', 'advanced-classifieds-and-directory-pro' ), 
				count( $csv_data['listings'] ) 
			);
			
			foreach ( $csv_data['listings'] as $index => $data ) {
				$this->import_listing( $data, $attributes, $index + 1 );
			}
						
			$data['message'] = sprintf( 
				__( 'Import finished, number of errors: %d, total rejected lines: %d', 'advanced-classifieds-and-directory-pro' ), 
				$this->logs['errors'],
				$this->logs['rejected']
			);

			$this->logs['messages'][] = $data['message'];
			$data['logs'] = implode( "\n", $this->logs['messages'] );			

			// Delete the csv_file, images_file attachments
			wp_delete_attachment( $attributes['csv_file'], true );

			if ( ! empty( $attributes['images_file'] ) ) {
				wp_delete_attachment( $attributes['images_file'], true );
			}
		}
		
		echo wp_json_encode( $data );
		wp_die();
	}	

	/**
	 * Get Collation Fields.
	 *
	 * @since  1.7.5
	 * @access private
	 * @return array   $fields Collation Fields.
	 */
	private function get_collation_fields() {
		$fields = array(
			'post_id'          => __( 'Listing ID (existing listing)', 'advanced-classifieds-and-directory-pro' ),
			'post_title'       => __( 'Listing Title', 'advanced-classifieds-and-directory-pro' ),
			'post_content'     => __( 'Listing Description', 'advanced-classifieds-and-directory-pro' ),					
			'acadp_categories' => __( 'Categories', 'advanced-classifieds-and-directory-pro' ),			
			'acadp_locations'  => __( 'Locations', 'advanced-classifieds-and-directory-pro' ),
			'address'          => __( 'Address', 'advanced-classifieds-and-directory-pro' ),
			'zipcode'          => __( 'Zipcode', 'advanced-classifieds-and-directory-pro' ),
			'phone'            => __( 'Phone', 'advanced-classifieds-and-directory-pro' ),
			'email'            => __( 'Email', 'advanced-classifieds-and-directory-pro' ),
			'website'          => __( 'Website', 'advanced-classifieds-and-directory-pro' ),
			'latitude'         => __( 'Latitude', 'advanced-classifieds-and-directory-pro' ),
			'longitude'        => __( 'Longitude', 'advanced-classifieds-and-directory-pro' ),
			'hide_map'         => __( 'Hide Map?', 'advanced-classifieds-and-directory-pro' ),
			'images'           => __( 'Images', 'advanced-classifieds-and-directory-pro' ),
			'video'            => __( 'Video', 'advanced-classifieds-and-directory-pro' ),
			'price'            => __( 'Price', 'advanced-classifieds-and-directory-pro' ),			
			'views'            => __( 'Views', 'advanced-classifieds-and-directory-pro' ),
			'featured'         => __( 'Featured', 'advanced-classifieds-and-directory-pro' ),
			'sold'             => __( 'Sold', 'advanced-classifieds-and-directory-pro' ),
			'post_date'        => __( 'Published Date', 'advanced-classifieds-and-directory-pro' ),
			'expiry_date'      => __( 'Expiry Date', 'advanced-classifieds-and-directory-pro' ),
			'never_expires'    => __( 'Never Expires', 'advanced-classifieds-and-directory-pro' ),
			'post_status'      => __( 'Listing Status', 'advanced-classifieds-and-directory-pro' ),
			'post_author'      => __( 'Author', 'advanced-classifieds-and-directory-pro' ),	
		);

		// Custom Fields
		$args = array(
			'post_type'              => 'acadp_fields',
			'post_status'            => 'publish',
			'posts_per_page'         => 500,	
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_key'               => 'order',
			'orderby'                => 'meta_value_num',			
			'order'                  => 'ASC'
		);		
		
		$acadp_query = new WP_Query( $args );

		if ( $acadp_query->have_posts() ) {
			foreach ( $acadp_query->posts as $post ) {
				$fields[ 'custom_field_' . $post->ID ] = $post->post_title;	
			};
		}

		// ...
		return $fields;
	}

	/**
	 * Parse CSV data.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  string  $attributes Input array.
	 * @return array   $data       Parsed CSV data.
	 */
	private function parse_csv( $attributes ) {
		$csv_file = get_attached_file( $attributes['csv_file'] );

		$data = array(
			'headers'  => array(),
			'listings' => array()
		);

		// Attempt to change permissions if not readable
		if ( ! is_readable( $csv_file ) ) {
			chmod( $csv_file, 0744 );
		}

		// Check if file is writable, then open it in 'read only' mode
		if ( is_readable( $csv_file ) && $file = fopen( $csv_file, 'r' ) ) {
			$rows = array();
			
			while ( $row = @fgetcsv( $file, 0, $attributes['columns_separator'] ) ) {
				$rows[] = $row;							
			}

			@fclose( $file );

			if ( count( $rows ) ) {
				$data['headers'] = array_shift( $rows );

				foreach ( $rows as $line => $row ) {
					// To sum this part up, all it really does is go row by
					// row, column by column, saving all the data
					$post = array();

					foreach ( $attributes['collated_fields'] as $i => $field ) {
						if ( ! empty( $field ) ) {
							$value = htmlspecialchars_decode( trim( $row[ $i ] ) ); // htmlspecialchars_decode() needed due to &amp; symbols in import files, ';' symbols can break import
							$post[ $field ] = $value;
						}					
					}

					$data['listings'][] = $post;
				}
			}
		}

		return $data;
	}
	
	/**
	 * Import Listing.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  string  $data       Import listing array.
	 * @param  string  $attributes Import settings.
	 * @param  int     $line_no    CSV file line number.
	 * @return bool    $imported   True if success, false if not.
	 */
	private function import_listing( $data, $attributes, $line_no ) {
		// Pre import
		$this->logs['messages'][] = sprintf(
			__( 'Importing line %d...', 'advanced-classifieds-and-directory-pro' ), 
			$line_no
		);

		$imported = false;

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		$actions = array( 'transition_post_status', 'save_post', 'pre_post_update', 'add_attachment', 'edit_attachment', 'edit_post', 'post_updated', 'wp_insert_post', 'save_post_acadp_listings' );
			
		foreach ( $actions as $action ) {
			remove_all_actions( $action );
		}

		// Import
		$post_title = wp_strip_all_tags( $data['post_title'] );

		$post_array = array(
			'post_type'	   => 'acadp_listings',
			'post_title'   => $post_title,
			'post_name'    => sanitize_title( $data['post_title'] ),
			'post_content' => isset( $data['post_content'] ) ? $data['post_content'] : '',
			'post_status'  => isset( $data['post_status'] ) ? sanitize_text_field( $data['post_status'] ) : 'publish'
		);		

		if ( isset( $data['post_date'] ) ) {
			$post_array['post_date'] = sanitize_text_field( $data['post_date'] );
		}

		$post_author = $this->process_user( $data, $attributes, $line_no );
		if ( $post_author > 0 ) {
			$post_array['post_author'] = $post_author;
		}

		$post_id = isset( $data['post_id'] ) ? (int) $data['post_id'] : 0;

		if ( $post_id > 0 ) {
			// Update the existing post
			if ( $post = get_post( $post_id ) && 'acadp_listings' == $post->post_type ) {
				$post_array['ID'] = $post_id;
				wp_update_post( $post_array );	
			} else {
				$this->logs['messages'][] = sprintf(
					__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Listing with ID "%d" doesn\'t exist', 'advanced-classifieds-and-directory-pro' ), 
					$line_no,
					$post_id
				);
				++$this->logs['error'];				

				$post_id = 0;
			}
		} else {
			// Save a new post
			$post_id = wp_insert_post( $post_array );
		}		

		if ( $post_id ) {
			$imported = true;

			$this->logs['messages'][] = sprintf(
				__( 'Listing title: %s', 'advanced-classifieds-and-directory-pro' ), 
				$post_title
			);
			
			// Categories
			$categories_ids = $this->process_categories( $data, $attributes, $line_no );
			wp_set_object_terms( $post_id, $categories_ids, 'acadp_categories' );

			// Locations
			$locations_ids = $this->process_locations( $data, $attributes, $line_no );
			wp_set_object_terms( $post_id, $locations_ids, 'acadp_locations' );

			if ( $attributes['do_geocode'] ) {
				if ( empty( $data['latitude'] ) && empty( $data['longitude'] ) ) {
					$data['locations_ids'] = $locations_ids;
					$coordinates = $this->get_location_coordinates( $data, $attributes, $line_no );

					$data['latitude']  = $coordinates['latitude'];
					$data['longitude'] = $coordinates['longitude'];
				}
			}

			// Post metas
			$never_expires = ! empty( $data['never_expires'] ) ? (int) $data['never_expires'] : 0;

			$meta = array(
				'address'        => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
				'zipcode'        => isset( $data['zipcode'] ) ? sanitize_text_field( $data['zipcode'] ) : '',
				'phone'          => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
				'email'          => isset( $data['email'] ) ? sanitize_text_field( $data['email'] ) : '',
				'website'        => isset( $data['website'] ) ? esc_url_raw( $data['website'] ) : '',
				'latitude'       => isset( $data['latitude'] ) ? sanitize_text_field( $data['latitude'] ) : '',
				'longitude'      => isset( $data['longitude'] ) ? sanitize_text_field( $data['longitude'] ) : '',
				'hide_map'       => ! empty( $data['hide_map'] ) ? (int) $data['hide_map'] : 0,
				'video'          => ! empty( $data['video'] ) ? esc_url_raw( $data['video'] ) : '',				
				'price'          => ! empty( $data['price'] ) ? acadp_sanitize_amount( $data['price'] ) : acadp_sanitize_amount(0),				
				'views'          => ! empty( $data['views'] ) ? (int) $data['views'] : 0,
				'featured'       => ! empty( $data['featured'] ) ? (int) $data['featured'] : 0,
				'sold'           => ! empty( $data['sold'] ) ? (int) $data['sold'] : 0,
				'expiry_date'    => ! empty( $data['expiry_date'] ) ? sanitize_text_field( $data['expiry_date'] ) : acadp_listing_expiry_date( $post_id, NULL, $never_expires ),
				'listing_status' => 'post_status'
			);

			// Images
			if ( ! empty( $attributes['images_dir'] ) ) {
				$images_ids = $this->process_images( $data, $attributes, $line_no );

				if ( count( $images_ids ) ) {
					$meta['images'] = $images_ids;
					set_post_thumbnail( $post_id, $images_ids[0] );	
				}
			}			

			// Custom Fields
			global $wpdb;

			foreach ( $data as $key => $value ) {
				if ( strpos( $key, 'custom_field_' ) !== false ) {
					$field_id = ltrim( $key, 'custom_field_' );
				} else {
					continue;
				}

				if ( ! $field_id ) {
					continue;
				}

				if ( strpos( $value, $attributes['values_separator'] ) !== false ) {
					$value = array_filter( array_map( 'sanitize_text_field', explode( $attributes['values_separator'], $value ) ) );
					$value = implode( "\n", $value );
				} else {
					$value = sanitize_text_field( $value );
				}

				$meta[ $field_id ] = $value;
			}

			$this->add_post_meta_bulk( $post_id, $meta );
		} else {
			++$this->logs['rejected'];
		}

		// Post Import
		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		return $imported;
	}

	/**
	 * Get user ID.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  array   $data       Listing import data.
	 * @param  array   $attributes Import settings.
	 * @param  int     $line_no    CSV file line number.
	 * @return array   $user_id    User ID.
	 */
	private function process_user( $data, $attributes, $line_no ) {
		$user_id = 0;

		if ( ! empty( $data['post_author'] ) ) {
			$post_author = sanitize_text_field( $data['post_author'] );

			if ( is_numeric( $post_author ) ) {
				$user_id = (int) $post_author;
			} else {
				if ( is_email( $post_author ) ) {
					$email = sanitize_email( $post_author );
					$user  = get_user_by( 'email', $email );

					if ( ! empty( $user ) ) {
						$user_id = $user->ID;
					} else {
						if ( $attributes['add_new_user'] ) {
							$password = wp_generate_password( 12, true );
							$user_id  = wp_create_user( $email, $password, $email );
						}
					}
				} else {
					$user_id = username_exists( $post_author );
				}
			}

			if ( empty( $user_id ) ) {
				$this->logs['messages'][] = sprintf( 
					__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'User "%s" doesn\'t exist, listing assigned to site admin', 'advanced-classifieds-and-directory-pro' ), 
					$line_no, 
					$post_author
				);
				++$this->logs['errors'];
			}
		}

		return $user_id;
	}

	/**
	 * Get categories IDs.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  array   $data           Listing import data.
	 * @param  array   $attributes     Import settings.
	 * @param  int     $line_no        CSV file line number.
	 * @return array   $categories_ids Categories IDs.
	 */
	private function process_categories( $data, $attributes, $line_no ) {
		$acadp_categories = array();
		$categories_ids   = array();

		if ( isset( $data['acadp_categories'] ) ) {
			$acadp_categories = array_filter( array_map( 'trim', explode( $attributes['values_separator'], $data['acadp_categories'] ) ) );
		}		

		if ( empty( $acadp_categories ) ) {
			return $categories_ids;
		}

		foreach ( $acadp_categories as $category_item ) {
			if ( ! is_numeric( $category_item ) ) {
				$categories_chain = array_filter( array_map( 'trim', explode( '>', $category_item ) ) );
				$listing_term_id = 0;

				foreach ( $categories_chain as $key => $category_name ) {
					if ( is_numeric( $category_name ) ) {
						$category_name = intval( $category_name );
					} else {
						$category_name = sanitize_text_field( $category_name );
					}
		
					if ( $term = term_exists( htmlspecialchars( $category_name ), 'acadp_categories', $listing_term_id ) ) { // htmlspecialchars() needed due to &amp; symbols in import files
						$term_id = intval( $term['term_id'] );
						$listing_term_id = $term_id;
					} else {
						if ( $attributes['add_new_term'] ) {
							$newterm = wp_insert_term( $category_name, 'acadp_categories', array( 'parent' => $listing_term_id ) );

							if ( ! is_wp_error( $newterm ) ) {
								$term_id = intval( $newterm['term_id'] );
								$listing_term_id = $term_id;
							} else {
								$this->logs['messages'][] = sprintf( 
									__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Something went wrong with listing category "%s"', 'advanced-classifieds-and-directory-pro' ), 
									$line_no, 
									$category_name
								);
								++$this->logs['errors'];
							}
						} else {
							$this->logs['messages'][] = sprintf( 
								__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Listing category "%s" wasn\'t found, was skipped', 'advanced-classifieds-and-directory-pro' ), 
								$line_no, 
								$category_name
							);
							++$this->logs['errors'];
						}
					}
				}

				if ( $listing_term_id ) {
					$categories_ids[] = $listing_term_id;
				}
			} elseif ( get_term( $category_item, 'acadp_categories' ) ) {
				$categories_ids[] = intval( $category_item );
			}
		}	

		return $categories_ids;
	}

	/**
	 * Get locations IDs.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  array   $data          Listing import data.
	 * @param  array   $attributes    Import settings.
	 * @param  int     $line_no       CSV file line number.
	 * @return array   $locations_ids Locations IDs.
	 */
	private function process_locations( $data, $attributes, $line_no ) {
		$acadp_locations = array();
		$locations_ids   = array();

		if ( isset( $data['acadp_locations'] ) ) {
			$acadp_locations = array_filter( array_map( 'trim', explode( $attributes['values_separator'], $data['acadp_locations'] ) ) );
		}		

		if ( empty( $acadp_locations ) ) {
			return $locations_ids;
		}

		foreach ( $acadp_locations as $location_item ) {
			if ( ! is_numeric( $location_item ) ) {
				$locations_chain = array_filter( array_map( 'trim', explode( '>', $location_item ) ) );
				$listing_term_id = 0;

				foreach ( $locations_chain as $key => $location_name ) {
					if ( is_numeric( $location_name ) ) {
						$location_name = intval( $location_name );
					} else {
						$location_name = sanitize_text_field( $location_name );
					}
		
					if ( $term = term_exists( htmlspecialchars( $location_name ), 'acadp_locations', $listing_term_id ) ) { // htmlspecialchars() needed due to &amp; symbols in import files
						$term_id = intval( $term['term_id'] );
						$listing_term_id = $term_id;
					} else {
						if ( $attributes['add_new_term'] ) {
							$newterm = wp_insert_term( $location_name, 'acadp_locations', array( 'parent' => $listing_term_id ) );

							if ( ! is_wp_error( $newterm ) ) {
								$term_id = intval( $newterm['term_id'] );
								$listing_term_id = $term_id;
							} else {
								$this->logs['messages'][] = sprintf( 
									__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Something went wrong with listing location "%s"', 'advanced-classifieds-and-directory-pro' ), 
									$line_no, 
									$location_name
								);
								++$this->logs['errors'];
							}
						} else {
							$this->logs['messages'][] = sprintf( 
								__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Listing location "%s" wasn\'t found, was skipped', 'advanced-classifieds-and-directory-pro' ), 
								$line_no, 
								$location_name
							);
							++$this->logs['errors'];
						}
					}
				}

				if ( $listing_term_id ) {
					$locations_ids[] = $listing_term_id;
				}
			} elseif ( get_term( $location_item, 'acadp_locations' ) ) {
				$locations_ids[] = intval( $location_item );
			}
		}	

		return $locations_ids;
	}	

	/**
	 * Get location coordinates using Google's Geocoding API.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  array   $data       Listing import data.
	 * @param  array   $attributes Import settings.
	 * @param  int     $line_no    CSV file line number.
	 * @return array               Latitude & Longitude.
	 */
	private function get_location_coordinates( $data, $attributes, $line_no ) {
		$map_settings = get_option( 'acadp_map_settings' );

		if ( 'osm' == $map_settings['service'] ) {
			return $this->get_osm_location_coordinates( $data, $attributes, $line_no );
		}

		$coordinates = array(
			'latitude'  => '',
			'longitude' => ''
		);

		$address = '';

		if ( count( $data['locations_ids'] ) ) {
			$chain = array();
			$parent_id = $data['locations_ids'][0];

			while ( $parent_id != 0 ) {
				if ( $term = get_term( $parent_id, 'acadp_locations' ) ) {
					$chain[]   = $term->name;
					$parent_id = $term->parent;
				} else {
					$parent_id = 0;
				}
			}

			$address = implode( ', ', $chain );
		}

		if ( isset( $data['address'] ) ) {
			$address = $data['address'] . ', ' . $address;
		}

		if ( isset( $data['zipcode'] ) ) {
			$address = $address . ' ' . $data['zipcode'];
		}

		$address = trim( $address );

		$md5_hash = md5( $address );
		if ( isset( $this->coordinates[ $md5_hash ] ) ) { // return from memory if exists
			return $this->coordinates[ $md5_hash ];
		}

		$api_key = ! empty( $map_settings['api_key'] ) ? sanitize_text_field( $map_settings['api_key'] ) : '';
		$api_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&key=' . $api_key;

		$request = wp_remote_get( $api_url );

		if ( is_wp_error( $request ) ) {
			$this->logs['messages'][] = sprintf( 
				__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Unable to find location coordinates (lat, lng)', 'advanced-classifieds-and-directory-pro' ), 
				$line_no
			);
			++$this->logs['errors'];
		} else {
			$body     = wp_remote_retrieve_body( $request );
			$response = json_decode( $body );

			if ( 'OK' == $response->status ) {
				$coordinates = array(
					'latitude'  => $response->results[0]->geometry->location->lat,
					'longitude' => $response->results[0]->geometry->location->lng
				);

				$this->coordinates[ $md5_hash ] = $coordinates;
			} else {
				$this->logs['messages'][] = sprintf( 
					__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Unable to find location coordinates (lat, lng)', 'advanced-classifieds-and-directory-pro' ), 
					$line_no
				);
				++$this->logs['errors'];
			}
		}

		return $coordinates;
	}

	/**
	 * Get location coordinates using OpenStreetMap.
	 *
	 * @since  1.8.0
	 * @access private
	 * @param  array   $data       Listing import data.
	 * @param  array   $attributes Import settings.
	 * @param  int     $line_no    CSV file line number.
	 * @return array               Latitude & Longitude.
	 */
	private function get_osm_location_coordinates( $data, $attributes, $line_no ) {
		$coordinates = array(
			'latitude'  => '',
			'longitude' => ''
		);

		$address = '';
		$query = array();

		if ( count( $data['locations_ids'] ) ) {			
			$parent_id = $data['locations_ids'][0];

			while ( $parent_id != 0 ) {
				if ( $term = get_term( $parent_id, 'acadp_locations' ) ) {
					$query[] = $term->name;
					$parent_id = $term->parent;
				} else {
					$parent_id = 0;
				}
			}
		}

		if ( isset( $data['zipcode'] ) ) {
			$query[] = $data['zipcode'];
		}

		if ( 0 == count( $query ) ) {
			if ( isset( $data['address'] ) ) {
				$query[] = $data['address'];
			}
		}		

		$address = implode( ',', $query );

		$md5_hash = md5( $address );
		if ( isset( $this->coordinates[ $md5_hash ] ) ) { // return from memory if exists
			return $this->coordinates[ $md5_hash ];
		}

		$response = wp_remote_get( 'https://nominatim.openstreetmap.org/search.php?q=' . urlencode( $address ) . '&addressdetails=1&limit=1&format=jsonv2' );

		if ( is_wp_error( $response ) ) {
			$this->logs['messages'][] = sprintf( 
				__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Unable to find location coordinates (lat, lng)', 'advanced-classifieds-and-directory-pro' ), 
				$line_no
			);
			++$this->logs['errors'];
		} else {
			$response = json_decode( $response['body'] );

			if ( count( $response ) > 0 ) {
				$coordinates = array(
					'latitude' => $response[0]->lat,
					'longitude' => $response[0]->lon
				);

				$this->coordinates[ $md5_hash ] = $coordinates;
			} else {
				$this->logs['messages'][] = sprintf( 
					__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Unable to find location coordinates (lat, lng)', 'advanced-classifieds-and-directory-pro' ), 
					$line_no
				);
				++$this->logs['errors'];
			}
		}

		return $coordinates;
	}

	/**
	 * Unzips a specified ZIP file to a location on the filesystem via the WordPress Filesystem Abstraction.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  array   $attributes Import settings.
	 * @return string 			   Path to the extracted ZIP directory.
	 */
	private function unzip_file( $attributes ) {
		if ( ! empty( $attributes['images_file'] ) ) {	
			if ( ! isset( $GLOBALS['wp_filesystem'] ) || ! is_object( $GLOBALS['wp_filesystem'] ) ) {
				WP_Filesystem();
			}

			$images_file   = get_attached_file( $attributes['images_file'] );
			$wp_upload_dir = wp_upload_dir();

			$zip_file_name = basename( $images_file );
			$folder_name   = substr( $zip_file_name, 0, strrpos( $zip_file_name, '.' ) );
			$unzip_dir     = trailingslashit( $wp_upload_dir['path'] ) . $folder_name . '/';

			if ( $unzip_file = unzip_file( $images_file, $unzip_dir ) ) {				
				return $unzip_dir;
			} 
		}

		return '';
	}

	/**
	 * Insert Images.
	 *
	 * @since  1.7.5
	 * @access private
	 * @param  array   $data       Listing import data.
	 * @param  array   $attributes Import settings.
	 * @param  int     $line_no    CSV file line number.
	 * @return array   $images_ids Images IDs.
	 */
	private function process_images( $data, $attributes, $line_no ) {		
		$images     = array();
		$images_ids = array();

		if ( isset( $data['images'] ) ) {
			$images = array_filter( array_map( 'trim', explode( $attributes['values_separator'], $data['images'] ) ) );
		}

		if ( empty( $images ) ) {
			return $images_ids;
		}

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		foreach ( $images as $image ) {
			$file_path = $attributes['images_dir'] . basename( $image );
			
			if ( ! file_exists( $file_path ) ) {
				$this->logs['messages'][] = sprintf( 
					__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Image file "%s" not found inside the ZIP.', 'advanced-classifieds-and-directory-pro' ), 
					$line_no, 
					$image
				);
				++$this->logs['errors'];

				continue;
			}

			// Check if an attachment exists for the image
			if ( isset( $this->attached[ $file_path ] ) ) {
				$images_ids[] = $this->attached[ $file_path ];
			} else {
				// Check the type of file. We'll use this as the 'post_mime_type'
				$filetype = wp_check_filetype( $image, null );			

				// Prepare an array of post data for the attachment
				$attachment = array(
					'guid'           => $file_path, 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $image ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				// Insert the attachment
				if ( $attach_id = wp_insert_attachment( $attachment, $file_path ) ) {
					$images_ids[] = $attach_id;

					// Generate the metadata for the attachment, and update the database record.
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
					wp_update_attachment_metadata( $attach_id, $attach_data );

					// Store the attachment in a variable to avoid recreating the same again
					$this->attached[ $file_path ] = $attach_id;					
				} else {
					$this->logs['messages'][] = sprintf( 
						__( 'Error on line %d: ', 'advanced-classifieds-and-directory-pro' ) . __( 'Image file "%s" could not be inserted.', 'advanced-classifieds-and-directory-pro' ), 
						$line_no, 
						$image
					);
					++$this->logs['errors'];
				}
			}			
		}

		return array_filter( $images_ids );
	}

	/**
	 * Insert multiple post meta at once.
	 * 
	 * @since  1.7.5
	 * @access private
	 * @param  int     $post_id Post ID.
	 * @param  array   $data    Post meta keys and values.
     */
	private function add_post_meta_bulk( $post_id, $data ) {
		global $wpdb;

		$meta_table = _get_meta_table( 'post' );
		$values     = array();
		
		foreach ( $data as $key => $value ) {					
			$values[] = '(' . $post_id . ',"' . $key . '",\'' . maybe_serialize( $value ) . '\')';						
		}
		
		$wpdb->query( "INSERT INTO $meta_table (`post_id`, `meta_key`, `meta_value`) VALUES " . implode( ',', $values ) );
	}

	/**
	 * Export Listings.
	 * 
	 * @since  1.7.5
	 * @access private
     */
	private function export_listings() {
		$offset = (int) $_POST['offset'];

		$limit  = ! empty( $_POST['limit'] ) ? (int) $_POST['limit'] : 1000;
		if ( $limit < $offset ) {
			$limit = $offset + $limit;
		}

		$csv_file_name = sprintf( 'acadp-listings-%s.csv', date( 'Y-m-d-H-i-s' ) );
		$csv_columns   = $this->get_collation_fields();
		$csv_header    = array_values( $csv_columns );
		$csv_output    = array();		

		$args = array(
			'post_type' => 'acadp_listings',
			'orderby' => 'ID',
			'order' => 'ASC',
			'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
			'offset' => $offset,
			'posts_per_page' => $limit,
			'no_found_rows' => true
		);

		$acadp_query = new WP_Query( $args );		

		global $post;

		while ( $acadp_query->have_posts() ) {
			$acadp_query->the_post();
			$post_meta = get_post_meta( get_the_ID() );
			$row = array();

			foreach ( $csv_columns as $field => $column ) {
				switch ( $field ) {
					case 'post_id':
						$row[] = get_the_ID();
						break;
					case 'post_title':
						$row[] = get_the_title();
						break;
					case 'post_content':
						$row[] = get_the_content();
						break;
					case 'acadp_categories':
					case 'acadp_locations':
						$terms = array();
						$terms_ids = wp_get_object_terms( get_the_ID(), $field, array( 'fields' => 'ids' ) );
						
						foreach ( $terms_ids as $term_id ) {
							$term_parents_list = get_term_parents_list( 
								$term_id, 
								$field, 
								array( 
									'separator' => '>', 
									'link'      => false 
								) 
							);

							$terms[] = rtrim( $term_parents_list, '>' );
						}

						$row[] = implode( ';', $terms );
						break;
					case 'address':
					case 'zipcode':
					case 'phone':
					case 'email':
					case 'website':
					case 'latitude':
					case 'longitude':					
					case 'video':										
					case 'expiry_date':
						$row[] = isset( $post_meta[ $field ] ) ? sanitize_text_field( $post_meta[ $field ][0] ) : '';
						break;
					case 'hide_map':
					case 'views':
					case 'featured':
					case 'sold':
					case 'never_expires':
						$row[] = isset( $post_meta[ $field ] ) ? sanitize_text_field( $post_meta[ $field ][0] ) : 0;
						break;
					case 'price':
						$row[] = isset( $post_meta[ $field ] ) ? acadp_format_amount( $post_meta[ $field ][0] ) : '';
						break;
					case 'post_date':
						$row[] = get_the_date( 'Y-m-d H:i:s' );
						break;
					case 'post_status':
						$row[] = $post->post_status;
						break;
					case 'post_author':
						$row[] = get_the_author_meta( 'user_email' );
						break;
					case 'images':
						$images = array();
						$attachment_ids = isset( $post_meta['images'] ) ? unserialize( $post_meta['images'][0] ) : array();
						
						if ( count( $attachment_ids ) ) {						
							foreach ( $attachment_ids as $attachment_id ) {
								if ( $image = get_attached_file( $attachment_id ) ) {
									$images[] = basename( $image );
								}						
							}						
						}

						$row[] = implode( ';', $images );
						break;
					default:
						if ( strpos( $field, 'custom_field_' ) !== false ) { // Custom Fields
							$field = ltrim( $field, 'custom_field_' );

							if ( isset( $post_meta[ $field ] ) ) {
								$row[] = $this->get_custom_field_value( $post_meta[ $field ][0], $field );
							} else {
								$row[] = '';
							}
						} else {
							$row[] = '';
						}						
				}
			}

			
			$csv_output[] = $row;
		}		

		// Remove empty (or) unwanted columns		
		if ( count( $csv_output ) ) {
			// $unwanted = array( 'post_id', 'post_date', 'expiry_date' );
			$unwanted = array();

			foreach ( $csv_header as $column_index => $column ) {
				$is_empty_column = 1;

				foreach ( $csv_output as $row ) {
					if ( '' != $row[ $column_index ] ) {
						$is_empty_column = 0;
						break;
					}
				}

				if ( $is_empty_column || in_array( $column_index, $unwanted ) ) {
					foreach ( $csv_output as $row_index => $row ) {
						unset( $csv_output[$row_index][ $column_index ] );
					}

					unset( $csv_header[ $column_index ] );
				}
			}
		}		

		// Insert CSV header
		array_unshift( $csv_output, $csv_header );

		// Output the CSV file
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Cache-Control: private", false );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Disposition: attachment; filename=\"" . $csv_file_name . "\";" );
		header( "Content-Transfer-Encoding: binary" );

		$output_buffer = fopen( "php://output", 'w' );
		foreach ( $csv_output as $val ) {
			fputcsv( $output_buffer, $val );
		}
		fclose( $output_buffer );

		exit();
	}

	/**
	 * Download Images.
	 * 
	 * @since  1.7.5
	 * @access private
     */
	private function download_images() {
		$offset = (int) $_POST['offset'];

		$limit  = ! empty( $_POST['limit'] ) ? (int) $_POST['limit'] : 1000;
		if ( $limit < $offset ) {
			$limit = $offset + $limit;
		}

		$images = array();

		$args = array(
			'post_type' => 'acadp_listings',
			'orderby' => 'ID',
			'order' => 'ASC',
			'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
			'offset' => $offset,
			'posts_per_page' => $limit,
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results' => false
		);

		$acadp_query = new WP_Query( $args );

		if ( $acadp_query->have_posts() ) {
			foreach ( $acadp_query->posts as $post_id ) {
				$attachment_ids = get_post_meta( $post_id, 'images', true );
					
				if ( count( $attachment_ids ) ) {						
					foreach ( $attachment_ids as $attachment_id ) {
						if ( $image = get_attached_file( $attachment_id ) ) {
							$images[] = $image;
						}						
					}						
				}
			}
		}

		$images = array_unique( $images );

		if ( $images ) {
			$zip_file_name = sprintf( 'acadp-images-%s.zip', date( 'Y-m-d-H-i-s' ) );
			$zip_file = trailingslashit( get_temp_dir() ) . $zip_file_name;
			
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			$zip = new PclZip( $zip_file );
			$path = $zip->create( implode( ',', $images ), PCLZIP_OPT_REMOVE_ALL_PATH );
			if ( ! $path ) {
				die( 'Error : ' . $zip->errorInfo( true ) );
			}

			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: private", false );
			header( "Content-Type: application/octet-stream" );
			header( "Content-Disposition: attachment; filename=\"" . $zip_file_name . "\";" );
			header( "Content-Length: " . filesize( $zip_file ) );
			flush();
			readfile( $zip_file );

			if ( ! isset( $GLOBALS['wp_filesystem'] ) || ! is_object( $GLOBALS['wp_filesystem'] ) ) {
				WP_Filesystem();
			}
			
			$wp_file = new WP_Filesystem_Direct( false );
			$wp_file->delete( $zip_file );
		}

		exit();
	}

	/**
	 * Get Custom Field Value.
	 * 
	 * @since  1.7.5
	 * @access private	 
	 * @param  string  $value    Custom field value stored in the listing form.
	 * @param  int     $field_id Custom field ID.
	 * @param  string  $output   Custom field value to display.
     */
	private function get_custom_field_value( $value, $field_id ) {
		$field_meta = get_post_meta( $field_id );

		if ( 'checkbox' == $field_meta['type'][0] ) {
			$values = explode( "\n", $value );
			$values = array_map( 'trim', $values );

			$output = implode( ';', $values );
		} else {
			$output = sanitize_text_field( $value );
		}

		return $output;
	}

}
