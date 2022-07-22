<?php get_header(); ?>
			<div id="content" class="clearfix">
				<div id="main" class="col-sm-8 clearfix" role="main">
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
						<header>
							<div class="page-header"><h1 class="single-title" itemprop="headline"><?php the_title(); ?></h1></div>
							<p class="meta post-meta-entry"><?php mywiki_entry_meta(); ?></p>
						</header> <!-- end article header -->
						<section class="post_content clearfix" itemprop="articleBody">	
							<div class="attachment-img">
							      <a href="<?php echo esc_url(wp_get_attachment_url($post->ID)); ?>">
							      <?php $mywiki_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' ); 
								      if ($mywiki_image) : ?>
								        <img src="<?php echo esc_url($mywiki_image[0]); ?>" alt="" />
								      <?php endif; ?>
							      </a>
							</div>
						</section> <!-- end article section -->
						<!-- end article footer -->
					</article> <!-- end article -->
					<?php comments_template('',true);
						endwhile;
						else : ?>
					<article id="post-not-found">
					    <header>
					    	<h1><?php esc_html_e("Not Found", "mywiki"); ?></h1>
					    </header>
					    <section class="post_content">
					    	<p><?php esc_html_e("Sorry, but the requested resource was not found on this site.", "mywiki"); ?></p>
					    </section>
					    <footer>
					    </footer>
					</article>
					<?php endif; ?>
				</div> <!-- end #main -->
				<?php get_sidebar(); // sidebar 1 ?>
			</div> <!-- end #content -->
<?php get_footer(); ?>