<?php

//Mel: Redirect to homepage after logged out
add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout(){
  wp_safe_redirect( home_url() );
  exit;
}

//Mel: To add menu item under user profile to view user's uploaded files
 add_action( 'admin_bar_menu', 'adjust_admin_menu_bar_items', 999);
 function adjust_admin_menu_bar_items ($wp_admin_bar) {
   $user = wp_get_current_user();

   //First, we gotta remove the log out link
   $wp_admin_bar->remove_node( 'logout' );

   //Next, insert this new menu item
   $wp_admin_bar->add_node([
     'id'        => 'link-id',
     'title' => 'My Files',
     'href' => get_site_url(null, 'manage-submissions'),
     'parent' => 'user-actions'
   ]);

   //Then, we reinsert log out link so that it appears at the bottom of the menu
   $wp_admin_bar->add_node([
     'id'        => 'logout',
     'title' => 'Log Out',
     'href' => wp_logout_url(),
     'parent' => 'user-actions'
   ]);
 }

//Mel: 29/01/22. To publish file after token is minted
add_action( 'acadp_order_completed', 'acadp_custom_order_completed' );
function acadp_custom_order_completed( $order_id ) {      
	$post_id = (int) get_post_meta( $order_id, 'listing_id', true );
   
	if ( $post_id > 0 ) {
		// Update post
		$post_array = array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		);

		wp_update_post( $post_array );
	}
}

//Mel: 29/01/22. Redirect user to home after logging in
function redirect_after_login($redirect_to, $request) {
	$redirect_url = get_bloginfo( 'url' ) . '/';

	return $redirect_url;
}
add_filter("login_redirect", "redirect_after_login", 10, 3);

//Mel: 29/01/22. Remove edit profile link from admin bar and side menu and kill profile page if not an admin
if( !current_user_can('activate_plugins') ) {
	function mytheme_admin_bar_render() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('edit-profile', 'user-actions');
	}
	add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );

	function stop_access_profile() {
		if(IS_PROFILE_PAGE === true) {
			wp_die( 'Please contact your administrator to have your profile information changed.' );
		}
		remove_menu_page( 'profile.php' );
		remove_submenu_page( 'users.php', 'profile.php' );
	}
	add_action( 'admin_init', 'stop_access_profile' );
}

//Mel: 29/01/22. Remove greeting
add_filter( 'admin_bar_menu', 'replace_wordpress_howdy', 25 );
function replace_wordpress_howdy( $wp_admin_bar ) {
	$my_account = $wp_admin_bar->get_node('my-account');
	$newtext = str_replace( 'Howdy,', '', $my_account->title );
	$wp_admin_bar->add_node( array(
	'id' => 'my-account',
	'title' => $newtext,
	));
}
 
/** Mel: 24/11/21
 * This function modifies the main WordPress query to include an array of 
 * post types instead of the default 'post' post type.
 *
 * @param object $query The main WordPress query.
 */
function tg_include_custom_post_types_in_search_results( $query ) {
    if ( $query->is_main_query() && $query->is_search() ) {
        $query->set( 'post_type', array( 'post', 'acadp_listings' ) );
    }
}
add_action( 'pre_get_posts', 'tg_include_custom_post_types_in_search_results' );

add_action( 'wp_enqueue_scripts', 'mywiki_theme_setup' );
function mywiki_theme_setup(){
  
 wp_enqueue_style( 'google-fonts-lato', '//fonts.googleapis.com/css?family=Lato', array(), false,null );
 wp_enqueue_style( 'google-fonts-cabin', '//fonts.googleapis.com/css?family=Cabin', array(), false,null );

  wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.css', array(), false,null );

  wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.css', array(), false, 'all' );
  wp_enqueue_style( 'mywiki-style', get_stylesheet_uri());
  wp_enqueue_script( 'bootstrap',  get_template_directory_uri() . '/js/bootstrap.js', array('jquery'), '3.0.1'); 
  wp_enqueue_script( 'mywiki-general',  get_template_directory_uri() . '/js/general.js');
  wp_localize_script( 'mywiki-general', 'my_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
  if ( is_singular() ): wp_enqueue_script( 'comment-reply' ); endif;
}

/* mywiki theme starts */
if ( ! function_exists( 'mywiki_setup' ) ) :
  function mywiki_setup() {
    	/* content width */
    	global $content_width;
    	if ( ! isset( $content_width ) ) {
    		$content_width = 900;
    	}
    	/*
    	 * Make mywiki theme available for translation.
    	 *
    	 */
    	load_theme_textdomain( 'mywiki', get_template_directory() . '/languages' );

      register_nav_menus(
        array(
          'primary' => __( 'The Main Menu', 'mywiki' ),  // main nav in header
          'footer-links' => __( 'Footer Links', 'mywiki' ) // secondary nav in footer
        )
      );
    	// This theme styles the visual editor to resemble the theme style.
    	add_editor_style( 'css/editor-style.css' );
    	// Add RSS feed links to <head> for posts and comments.
    	add_theme_support( 'automatic-feed-links' );
      add_theme_support( 'title-tag' );
      add_theme_support( 'custom-logo', array(
                'height'      => 160,
                'width'       => 45,
                'flex-height' => true,
                'flex-width'  => true,
                'priority'    => 11,
                'header-text' => array( 'site-title', 'site-description' ), 
            ) );
    	/*
    	 * Enable support for Post Formats.
    	 */
    	// This theme allows users to set a custom background.
    	add_theme_support( 'custom-background', apply_filters( 'mywiki_custom_background_args', array(
    		'default-color' => '048eb0',
    	) ) );
    	// Add support for featured content.
    	add_theme_support( 'featured-content', array(
    		'featured_content_filter' => 'mywiki_get_featured_posts',
    		'max_posts' => 6,
    	) );
    	// This theme uses its own gallery styles.
    	add_filter( 'use_default_gallery_style', '__return_false' );


      add_theme_support( 'post-thumbnails' );
      set_post_thumbnail_size( 150, 150 ); // default Post Thumbnail dimensions
     
      
      add_image_size( 'category-thumb', 300, 9999 ); //300 pixels wide (and unlimited height)
      add_image_size( 'homepage-thumb', 220, 180, true ); //(cropped)
      
      add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form' ) );
  }
endif; // mywiki_setup
add_action( 'after_setup_theme', 'mywiki_setup' );

add_filter('get_custom_logo','mywiki_change_logo_class');
function mywiki_change_logo_class($html)
{
  //$html = str_replace('class="custom-logo"', 'class="img-responsive logo-fixed"', $html);
  $html = str_replace('width=', 'original-width=', $html);
  $html = str_replace('height=', 'original-height=', $html);
  $html = str_replace('class="custom-logo-link"', 'class="navbar-brand logo"', $html);
  return $html;
}

if ( ! function_exists( 'mywiki_entry_meta' ) ) :
/**
 * Set up post entry meta.
 *
 * Meta information for current post: categories, tags, permalink, author, and date.
 **/
function mywiki_entry_meta() {
	$mywiki_category_list = get_the_category_list(', '); 
  $mywiki_tags_list = get_the_tags(', ');  ?>
  <i class="fa fa-calendar-check-o"></i>&nbsp;&nbsp;
  <a href="<?php echo esc_url(get_permalink()); ?>" title="<?php echo esc_attr(get_the_time()); ?>" ><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></a>
  &nbsp;  
  <?php if ( $mywiki_category_list ) { ?>
   <i class="fa fa-folder-open"></i>
  <?php echo wp_kses_post(get_the_category_list(', '));    }  
 }
endif;
/**
 * Add default menu style if menu is not set from the backend.
 */
function mywiki_add_menuclass ($page_markup) {
  preg_match('/^<div class=\"([a-z0-9-_]+)\">/i', $page_markup, $mywiki_matches);
  $mywiki_toreplace = array('<div class="navbar-collapse collapse top-gutter">', '</div>');
  $mywiki_replace = array('<div class="navbar-collapse collapse top-gutter">', '</div>');
  $mywiki_new_markup = str_replace($mywiki_toreplace,$mywiki_replace, $page_markup);
  $mywiki_new_markup= preg_replace('/<ul/', '<ul class="nav navbar-nav navbar-right mywiki-header-menu"', $mywiki_new_markup);
  return $mywiki_new_markup; 
} //}
add_filter('wp_page_menu', 'mywiki_add_menuclass');

/**
 * Wiki search
 */
function mywiki_search() {
	global $wpdb;
	$mywiki_title=(isset($_POST['queryString']))?trim(sanitize_text_field(wp_unslash($_POST['queryString']))):'';
  if(strpos($mywiki_title,"#")>-1):
    $tags = strtolower(str_replace(array(' ','#'),array( '-',''),$mywiki_title));
    
	//Mel: 24/11/21
	$mywiki_args = array('posts_per_page' => -1, 'order'=> 'ASC', "orderby"=> "title", "post_type" => "acadp_listings",'post_status'=>'publish',"tag" => $tags);
	//$mywiki_args = array('posts_per_page' => -1, 'order'=> 'ASC', "orderby"=> "title", "post_type" => "post",'post_status'=>'publish',"tag" => $tags);
  else:
    
	//Mel: 24/11/21
	$mywiki_args = array('posts_per_page' => -1, 'order'=> 'ASC', "orderby"=> "title", "post_type" => "acadp_listings",'post_status'=>'publish', "s" => $mywiki_title);
	//$mywiki_args = array('posts_per_page' => -1, 'order'=> 'ASC', "orderby"=> "title", "post_type" => "post",'post_status'=>'publish', "s" => $mywiki_title);
  endif;	
  $mywiki_posts = get_posts( $mywiki_args );
	$mywiki_output='';
	if($mywiki_posts):
		 $mywiki_h=0; ?>
		 <ul id="search-result">
  		 <?php foreach ( $mywiki_posts as $mywiki_post ) { setup_postdata( $mywiki_post );?>
			<?php $terms = wp_get_post_terms($mywiki_post->ID, 'acadp_categories'); //Get acadp category for the post?>
			<?php if ($terms[0]->term_id == 224) { //Only display post where category is public file (acadp category ID = 224) ?>
  			 <li class="que-icn">
            <a href="<?php echo esc_url(get_the_permalink($mywiki_post->ID))?>"> <i class="fa fa-angle-right"></i><?php echo esc_html($mywiki_posts[$mywiki_h]->post_title);?> </a>
          </li>
		 	<?php } else { esc_html_e('No','mywiki');} ?>		 
  		 <?php $mywiki_h++; } ?>
  	 </ul>
	<?php  wp_reset_postdata();	
  else: ?>
  	<ul id="search-result">
		<li class="que-icn">
      <?php esc_html_e('No','mywiki'); ?>
	  	</li>
	</ul>
	<?php endif;
	die();
}
add_action('wp_ajax_mywiki_search', 'mywiki_search');
add_action('wp_ajax_nopriv_mywiki_search', 'mywiki_search' );

if ( ! function_exists( 'mywiki_comment' ) ) :
  /**
   * Template for comments and pingbacks.
   *
   * To override this walker in a child theme without modifying the comments template
   * simply create your own mywiki_comment(), and that function will be used instead.
   *
   * Used as a callback by wp_list_comments() for displaying the comments.
   *
   * @since Twenty Twelve 1.0
   */
  function mywiki_comment( $comment, $args, $depth ) {
  	//$GLOBALS['comment'] = $comment;
  		// Proceed with normal comments.
 		global $post; ?>
  		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> >
      	<article class="div-comment-<?php comment_ID(); ?>" id="div-comment-1">
  				<footer class="comment-meta">
  					<div class="comment-author vcard">
  						<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
  					<b class="fn">	<?php printf( /* translators: 1 is author link */ esc_html__( '%s says:','mywiki' ), get_comment_author_link()  ); ?></b>
  					</div><!-- .comment-author -->
  					<div class="comment-metadata">
  						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
  							<time datetime="<?php comment_time( 'c' ); ?>">
  								<?php printf( /* translators: 1 is post date , 2 is post time */ esc_html__( '%1$s at %2$s', 'mywiki' ), get_comment_date(), get_comment_time() ); ?>
  							</time>
  						</a>
  						<?php edit_comment_link( __( 'Edit','mywiki' ), '<span class="edit-link">', '</span>' ); ?>
            </div><!-- .comment-metadata -->
  				</footer><!-- .comment-meta -->
  				<div class="comment-content">
  					<?php comment_text(); ?>
  				</div><!-- .comment-content -->
  				<div class="reply">
  					<?php comment_reply_link( array_merge( $args, array( 'add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                 </div><!-- .reply -->
  			</article>
  	<?php
  }
endif;
add_action('wp_ajax_mywiki_header', 'mywiki_header_image_function');
add_action('wp_ajax_nopriv_mywiki_header', 'mywiki_header_image_function' );
function mywiki_header_image_function(){
	$mywiki_return['header'] = get_header_image();
	echo json_encode($mywiki_return);
	die;
}


function mywiki_search_form($html) {   
    $html= '<form class="asholder search-main col-md-12 col-sm-12 col-xs-12" role="search" method="get" id="searchformtop" action="'.esc_url(home_url()).'">        
          <div class="input-group" id="suggest">
            <input name="s" id="s" type="text" onKeyUp="suggest(this.value);" onBlur="fill();" class="search-query form-control pull-right" autocomplete="off" placeholder="'.esc_attr__('Have a Question? Write here and press enter','mywiki').'" data-provide="typeahead" data-items="4" data-source="">
            <div class="suggestionsbox" id="suggestions" style="display: none;"> <img src="'.esc_url(get_template_directory_uri().'/img/arrow1.png').'" height="18" width="27" class="upArrow" alt="upArrow" />
              <div class="suggestionlist" id="suggestionslist"></div>
            </div>        
        </div>
      </form>';
   
 return $html;
}

/*Customizer*/
require get_template_directory() . '/function/customizer.php';
/*theme-default-setup*/
require get_template_directory() . '/function/theme-default-setup.php';
// Implement Custom Header features.
require get_template_directory() . '/function/custom-header.php';