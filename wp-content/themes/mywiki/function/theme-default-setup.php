<?php
/*
 * Mywiki Main Sidebar
 */
function mywiki_widgets_init() {
     register_sidebar(array(
      'id' => 'sidebar1',
      'name' => __('Main Sidebar','mywiki'),
      'description' => __('Used on every page.','mywiki'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="sidebar-heading"><span>',
      'after_title' => '</span></h4>',
    ));
    register_sidebar(array(
      'id' => 'footer1',
      'name' => __('Footer Content Area 1','mywiki'),
      'description' => __('Used on Footer.','mywiki'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="sidebar-heading"><span>',
      'after_title' => '</span></h4>',
    ));
    register_sidebar(array(
      'id' => 'footer2',
      'name' => __('Footer Content Area 2','mywiki'),
      'description' => __('Used on Footer.','mywiki'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="sidebar-heading"><span>',
      'after_title' => '</span></h4>',
    ));
    register_sidebar(array(
      'id' => 'footer3',
      'name' => __('Footer Content Area 3','mywiki'),
      'description' => __('Used on Footer.','mywiki'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="sidebar-heading"><span>',
      'after_title' => '</span></h4>',
    ));
}
add_action('widgets_init', 'mywiki_widgets_init');
add_action( 'widgets_init', 'mywiki_popular_load_widgets' );
function mywiki_popular_load_widgets() {
  register_widget( 'mywiki_popular_widget' );
  register_widget( 'mywiki_recentpost_widget' );
}
/** Define the Widget as an extension of WP_Widget **/
class mywiki_popular_widget extends WP_Widget {
  function __construct() {
    /* Widget settings. */
    $mywiki_widget_ops = array( 'classname' => 'widget_popular', 'description' => __('Displays most popular posts by comment count','mywiki'));
     
    /* Widget control settings. */
    $mywiki_control_ops = array( 'id_base' => 'popular-widget' );
     
    /* Create the widget. */
    parent::__construct( 'popular-widget', __('Popular Posts','mywiki'), $mywiki_widget_ops, $mywiki_control_ops );
  }
   
  // Limit to last 30 days
  function filter_where( $where = '' ) {
    // posts in the last 30 days
    $where .= " AND post_date > '" . date('Y-m-d', strtotime('-' . $instance['days'] .' days')) . "'";
    return $where;
  }
  function widget( $args, $instance ) {
    extract( $args );
    echo $before_widget;
    if( !empty( $instance['title'] ) ) echo $before_title .'<p class="wid-category"><span>'.esc_html($instance['title']).'</span></p>' . $after_title;
    $loop_args = array(
    'posts_per_page' => (int) $instance['count'],
    'orderby' => 'comment_count'
    );
    if( 0 == $instance['days'] ) {
    $loop = new WP_Query( $loop_args );
    }else{
    add_filter( 'posts_where', array( $this, 'filter_where' ) );
    $loop = new WP_Query( $loop_args );
    remove_filter( 'posts_where', array( $this, 'filter_where' ) );
    }echo "<div class='wid-cat-container'><ul>";
    if( $loop->have_posts() ): while( $loop->have_posts() ): $loop->the_post(); global $post;
    ?><li>
    <a href="<?php echo esc_url(get_permalink());?>" class="wid-cat-title wid-popular-post">
      <?php the_title() ;?>
    </a></li>
    <?php endwhile; endif; wp_reset_query();
    echo "</ul></div>";
    echo $after_widget;
  }
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    /* Strip tags (if needed) and update the widget settings. */
    $mywiki_instance['title'] = esc_attr( $new_instance['title'] );
    $mywiki_instance['count'] = (int) $new_instance['count'];
    $mywiki_instance['days'] = (int) $new_instance['days'];
    return $instance;
  }
  function form( $instance ) {
    /* Set up some default widget settings. */
    $mywiki_defaults = array( 'title' => '', 'count' => 5, 'days' => 30 );
    $instance = wp_parse_args( (array) $instance, $mywiki_defaults ); ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e('Title', 'mywiki') ?>:</label>
      <input id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id( 'count' )); ?>"><?php esc_html_e('Number of Posts', 'mywiki') ?>:</label>
      <input id="<?php echo esc_attr($this->get_field_id( 'count' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'count' )); ?>" size="3" value="<?php echo esc_attr($instance['count']); ?>" />
    </p>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id( 'days' )); ?>"><?php esc_html_e('Posted in the past X days', 'mywiki') ?>:</label>
      <input id="<?php echo esc_attr($this->get_field_id( 'days' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'days' )); ?>" size="3" value="<?php echo esc_attr($instance['days']); ?>" />
    </p>
    <p class="description"><?php esc_html_e('Use 0 for no time limit.', 'mywiki') ?></p>
    <?php
  }
}

class mywiki_recentpost_widget extends WP_Widget {
  function __construct() {
    /* Widget settings. */
    $mywiki_widget_ops = array( 'classname' => 'widget_recentpost', 'description' => __('Displays most recent posts by post count','mywiki') );
     
    /* Widget control settings. */
    $mywiki_control_ops = array( 'id_base' => 'recent-widget' );
     
    /* Create the widget. */
    parent::__construct( 'recent-widget', __('Recent Posts','mywiki'), $mywiki_widget_ops, $mywiki_control_ops );
  }
  function widget( $args, $instance ) {
    extract( $args );
    echo $before_widget;
    if( !empty( $instance['title'] ) ) echo $before_title .'<p class="wid-category"><span>'.esc_html($instance['title']).'</span></p>' . $after_title;
    $mywiki_loop_args = array(
    'posts_per_page' => (int) $instance['count'],
    'orderby' => 'DESC'
    );
    $mywiki_loop = new WP_Query( $mywiki_loop_args );
    echo "<div class='wid-cat-container'><ul>";
    if( $mywiki_loop->have_posts() ): while( $mywiki_loop->have_posts() ): $mywiki_loop->the_post(); global $post;
    ?><li>
    <a href="<?php echo esc_url(get_permalink());?>" class="wid-cat-title wid-popular-post"><?php the_title() ;?></a></li>
    <?php endwhile; endif; wp_reset_query();
    echo "</ul></div>";
    echo $after_widget;
  }
  function update( $new_instance, $old_instance ) {
    $mywiki_instance = $old_instance;
    /* Strip tags (if needed) and update the widget settings. */
    $mywiki_instance['title'] = esc_attr( $new_instance['title'] );
    $mywiki_instance['count'] = (int) $new_instance['count'];
    return $mywiki_instance;
  }
  function form( $instance ) {
    /* Set up some default widget settings. */
    $mywiki_defaults = array( 'title' => '', 'count' => 5, 'days' => 30 );
    $instance = wp_parse_args( (array) $instance, $mywiki_defaults ); ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e('Title', 'mywiki') ?>:</label>
      <input id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id( 'count' )); ?>"><?php esc_html_e('Number of Posts', 'mywiki') ?>:</label>
      <input id="<?php echo esc_attr($this->get_field_id( 'count' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'count' )); ?>" size="3" value="<?php echo esc_attr($instance['count']); ?>" />
    </p>
    <?php
  } 
}

/* 
Adding Read More
*/
function mywiki_trim_excerpt($mywiki_text) {
 $text = substr($mywiki_text,0,-10); 
 return $text.'..<div class="clear-fix"></div><a href="'.get_permalink().'" title="'.__('read more...','mywiki').'">'.__('Read more','mywiki').'</a>';
}
add_filter('get_the_excerpt', 'mywiki_trim_excerpt');