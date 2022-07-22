<?php get_header(); ?>
<div id="content" class="row clearfix">
  <div id="main" class="col-sm-8 clearfix" role="main">
    <div id="home-main" class="home-main home">
      <header>
        <div class="page-catheader cat-catheader">
            <h4 class="cat-title">
				    <?php if ( have_posts() ) : 
                	?><span><?php esc_html_e('Author','mywiki'); echo " : "?></span>
				      <?php echo get_the_author(); 
                endif; ?>
            </h4>
         </div>
      </header>
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
        <header>
            <div class="cat-hadding">
                 <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title();?></a>
            </div>
            <p class="meta post-meta-entry"><?php mywiki_entry_meta(); ?></p>
        </header>
        <!-- end article header -->
        <section class="post_content">
          <?php the_post_thumbnail( 'wpbs-featured' ); ?>
          <?php the_excerpt(); ?>
        </section>
        <!-- end article section -->
      </article>
      <!-- end article -->
      <?php endwhile;
	       endif; ?>
		<!--Pagination Start-->
    <?php if(get_option('posts_per_page ') < $wp_query->found_posts) { ?>
    <nav class="mywiki-nav">
            <span class="mywiki-nav-previous"><?php previous_posts_link(); ?></span>
            <span class="mywiki-nav-next"><?php next_posts_link(); ?></span>
        </nav>
    <?php } ?>
    <!--Pagination End-->
    </div>
  </div>
  <!-- end #main -->
  <?php get_sidebar(); ?>
</div>
<!-- end #content -->
<?php get_footer(); ?>