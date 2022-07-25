<?php
// MyWiki theme
get_header(); ?>
<div id="content" class="row clearfix">
  <div id="main" class="col-sm-8 clearfix" role="main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
      <header>
        <div class="page-header">
          <h1 class="page-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
        </div>
      </header>
      <!-- end article header -->      
      <!-- end article footer -->
      <section class="post_content clearfix" itemprop="articleBody">
        <?php the_content(); ?>
      </section>
      <!-- end article section -->
    </article>
    <!-- end article -->
    <?php endwhile; ?>
    <?php else : ?>
    <article id="post-not-found">
      <header>
        <h1>
          <?php esc_html_e("Not Found", "mywiki"); ?>
        </h1>
      </header>
      <section class="post_content">
        <p>
          <?php esc_html_e("Sorry, but the requested resource was not found on this site.", "mywiki"); ?>
        </p>
      </section>
      <footer> </footer>
    </article>
    <?php endif; ?>
  </div>
  <!-- end #main -->
  <?php  get_sidebar(); // sidebar 1 ?>
</div>
<!-- end #content -->
<?php get_footer(); ?>