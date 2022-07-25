<?php
$mywiki_options = get_option( 'faster_theme_options' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open();   }  ?>
<div id="wrap">
<header role="banner">
  <div id="inner-header" class="clearfix">
    <div class="navbar navbar-default top-bg">
      <div class="container" id="navbarcont">
        <div class="row">
        <div class="nav-container col-md-9">
          <nav role="navigation">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
              <?php if(has_custom_logo()){  
                     the_custom_logo();
                } 
               if (display_header_text()==true){ ?>
              <a class="navbar-brand logo" id="logo" title="<?php echo esc_html(get_bloginfo('description')); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <p><span class="header-text"><?php echo esc_html(bloginfo("name"));?></span></p>
                <h4><span class="header-description-text"><?php echo esc_html(get_bloginfo('description')); ?></span></h4>
              </a>
              <?php } ?>
            </div>
            <!-- end .navbar-header -->
          </nav>
        </div>
        <div class="navbar-collapse collapse top-menu">
          <?php	wp_nav_menu( array(
            'theme_location'  => 'primary',
            'container'       => 'div',         
            'echo'            => true,
            'fallback_cb'     => 'wp_page_menu',          
            'items_wrap'      => '<ul id="menu" class="nav navbar-nav navbar-right mywiki-header-menu">%3$s</ul>',
            'depth'           => 0,
            'walker'          => ''
            ) ); ?>
        </div>
        <!-- end .nav-container -->
        </div>  
      </div>
      <!-- end #navcont -->
    </div>
    <!-- end .navbar --> 
  </div>
  <!-- end #inner-header --> 
</header>
<!-- end header -->
<div class="searchwrap ">
  <div class="container" id="search-main">
    <div class="row">
      <?php 
			//Mel: 26/11/21
			//add_filter('get_search_form', 'mywiki_search_form');
            //get_search_form('mywiki_search_form');
            //remove_filter('get_search_form', 'mywiki_search_form');
       ?>
    </div>
  </div>
</div>
<div class="container " id="maincnot">