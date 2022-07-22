<?php get_header(); ?>
<div id="content" class="row">
  <div id="main" class="col-sm-8 clearfix" role="main">
    <div id="home-main" class="home-main home mywiki-post">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article" itemscope>
        <header>
            <header>
              <div class="page-catheader cat-catheader">
                <h4 class="cat-title">
                  <?php the_title(); ?>
                </h4>
              </div>
            </header>            
            <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
              <header>
              <div class="single-page">
                <div class="meta nopadding">
                  <time class="sprite date-icon" datetime="<?php echo the_time('M-j-Y'); ?>" pubdate>
                    <i class="fa fa-calendar-check-o"></i> &nbsp;<?php the_date(); ?>
                  </time>                  
                   &nbsp;<span class="sprite amp cat-icon-small"><i class="fa fa-folder-open"></i>
                  <?php the_category(', '); ?>                  
                  </span> 
                </div>
               </div> 
              </header>
              <!-- end article header -->
              <section class="post_content">
                <?php the_content();
                if(has_post_thumbnail()){ ?>
                <figure class="single_cat_image"> <img src="<?php echo esc_url(wp_get_attachment_url(get_post_thumbnail_id($post->ID) )); ?>" /> </figure>
                <?php }
                wp_link_pages(); ?>
              </section>
              <!-- end article section -->
            </article>
        </header>
      </article>
      <!-- end article -->
      <?php endwhile;
      endif; ?>
      <nav class="mywiki-nav">
          <span class="mywiki-nav-previous"><?php previous_post_link( '%link', '<span>'.'<< </span> %title' ); ?></span>
          <span class="mywiki-nav-next"><?php next_post_link( '%link', '%title <span>'.'>> </span>' ); ?></span>
		  </nav>
    </div>
	<?php comments_template( '', true ); ?>
  </div>
  <!-- end #main -->
  <?php //Mel: 05/12/21 get_sidebar(); // sidebar 1 ?>
</div>
<!-- end #content -->
<?php get_footer(); ?>