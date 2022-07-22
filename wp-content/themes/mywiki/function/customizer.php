<?php 
function mywiki_cats() {
  $cats = array();
  $cats[0] = "All";
  foreach ( get_categories() as $categories => $category ) {
    $cats[$category->term_id] = $category->name;
  }
  return $cats;
}
function mywiki_sanitize_category( $input )
{
    $valid = mywiki_cats();
    foreach ($input as $value) {
        if ( !array_key_exists( $value, $valid ) ) {
            return array();
        }
    }
    return $input;
}
if ( class_exists( 'WP_Customize_Control' ) ) {
class MyWiki_Customize_Control_Multiple_Select extends WP_Customize_Control {

/**
 * The type of customize control being rendered.
 */
public $type = 'multiple-select';

/**
 * Displays the multiple select on the customize screen.
 */
public function render_content() {

if ( empty( $this->choices ) )
    return;
?>
    <label>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <select <?php $this->link(); ?> multiple="multiple" style="height: 100%;">
            <?php
                foreach ( $this->choices as $value => $label ) {
                    $select_val = $this->value();
                    $selected = ( in_array( $value,  $select_val  ) ) ? selected( 1, 1, false ) : '';
                    echo '<option value="' . esc_attr( $value ) . '"' . esc_attr($selected) . '>' . esc_html($label) . '</option>';
                }
            ?>
        </select>
    </label>
<?php }} }

function mywiki_field_sanitize_input_choice( $input, $setting ) {

  // Ensure input is a slug.
  $input = sanitize_key( $input );

  // Get list of choices from the control associated with the setting.
  $choices = $setting->manager->get_control( $setting->id )->choices;

  // If the input is a valid key, return it; otherwise, return the default.
  return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

function mywiki_customize_register( $wp_customize ) {
	$mywiki_options = get_option( 'faster_theme_options' );

	$wp_customize->add_panel(
    'general',
	    array(
	        'title' => __( 'General', 'mywiki' ),
	        'description' => __('styling options','mywiki'),
	        'priority' => 20, 
	    )
	  );
	
	$wp_customize->get_section('title_tagline')->panel = 'general';
	$wp_customize->get_section('static_front_page')->panel = 'general';
	$wp_customize->get_section('header_image')->panel = 'general';
	$wp_customize->get_section('title_tagline')->title = __('Header & Logo','mywiki');


/*-------------------- Home Page Option Setting --------------------------*/

$wp_customize->add_section( 'frontpage_options_section' ,
   array(
      'title'       => __( 'Front Page : Options', 'mywiki' ),
      'priority'    => 32,
      'capability'     => 'edit_theme_options', 
      
  )
);    

$wp_customize->add_setting( 'mywiki_category_list',
      array(
        'default' => 0,
        'transport'   => 'refresh',
        'sanitize_callback' => 'mywiki_sanitize_category'
      )
  );
$wp_customize->add_control(
    new MyWiki_Customize_Control_Multiple_Select (
        $wp_customize,
        'mywiki_category_list',
        array(
            'settings' => 'mywiki_category_list',
            'label'    => 'Featured category',
            'section'  => 'frontpage_options_section', // Enter the name of your own section
            'type'     => 'multiple-select', // The $type in our class
            'choices' => mywiki_cats()
        )
    )
);

  $wp_customize->add_setting( 'mywiki_category_count',
      array(
          'capability'     => 'edit_theme_options',
          'sanitize_callback' => 'absint',
          'priority' => 20, 
      )
  );
  $wp_customize->add_control( 'mywiki_category_count',
      array(
          'default' => 1,
          'section' => 'frontpage_options_section',                
          'label'   => __('Number of posts to show: ','mywiki'),
          'type'    => 'number',
          'input_attrs' => array( 'placeholder' => esc_html__('Enter Number of post','mywiki')),
      )
  );  

  $wp_customize->add_setting( 'mywiki_category_count',
      array(
          'capability'     => 'edit_theme_options',
          'sanitize_callback' => 'absint',
          'priority' => 20, 
          'default' => 1,
      )
  );
  $wp_customize->add_control( 'mywiki_category_count',
      array(
          
          'section' => 'frontpage_options_section',                
          'label'   => __('Number of posts to show: ','mywiki'),
          'type'    => 'number',
          'input_attrs' => array( 'placeholder' => esc_html__('Enter Number of post','mywiki')),
      )
  ); 

  $wp_customize->add_setting( 'mywiki_category_title',
      array(
          'capability'     => 'edit_theme_options',
          'sanitize_callback' => 'sanitize_text_field',
          'priority' => 20, 
          'default' => esc_html__('Knowledgebase Categories','mywiki'),
      )
  );
  $wp_customize->add_control( 'mywiki_category_title',
      array(          
          'section' => 'frontpage_options_section',                
          'label'   => __('Category Header Title : ','mywiki'),          
          'type'    => 'text',
          'input_attrs' => array( 'placeholder' => esc_html__('Enter Title like Knowledgebase Categories','mywiki')),
      )
  ); 

  $wp_customize->add_setting( 'mywiki_category_icon',
      array(
          'capability'     => 'edit_theme_options',
          'sanitize_callback' => 'sanitize_text_field',
          'priority' => 20, 
          'default' => 'fa-list-alt',
      )
  );
  $wp_customize->add_control( 'mywiki_category_icon',
      array(          
          'section' => 'frontpage_options_section',                
          'label'   => __('Category Icon : ','mywiki'),
          'description' => __( 'In input box, you need to add FONT AWESOME shortcode which you can find ' ,  'mywiki').'<a target="_blank" href="'.esc_url('https://fortawesome.github.io/Font-Awesome/icons/').'">'.__('here' ,  'mywiki').'</a>',
          'type'    => 'text',
          'input_attrs' => array( 'placeholder' => esc_html__('Enter Font Awesome Icon','mywiki')),
      )
  ); 

  $wp_customize->add_setting( 'mywiki_category_post_icon',
      array(
          'capability'     => 'edit_theme_options',
          'sanitize_callback' => 'sanitize_text_field',
          'priority' => 20, 
          'default' => 'fa-file-text-o',
      )
  );
  $wp_customize->add_control( 'mywiki_category_post_icon',
      array(          
          'section' => 'frontpage_options_section',                
          'label'   => __('Category Post List Icon : ','mywiki'),
          'description' => __( 'In input box, you need to add FONT AWESOME shortcode which you can find ' ,  'mywiki').'<a target="_blank" href="'.esc_url('https://fortawesome.github.io/Font-Awesome/icons/').'">'.__('here' ,  'mywiki').'</a>',
          'type'    => 'text',
          'input_attrs' => array( 'placeholder' => esc_html__('Enter Font Awesome Icon','mywiki')),
      )
  );    
 

	//Footer Section
	$wp_customize->add_panel(
    'footer',
	    array(
	        'title' => __( 'Footer', 'mywiki' ),
	        'description' => __('Footer  options','mywiki'),
	        'priority' => 200, 
	    )
	);
  
	$wp_customize->add_section( 'footerCopyright' , array(
	    'title'       => __( 'Footer', 'mywiki' ),
	    'priority'    => 100,
	    'capability'     => 'edit_theme_options',
	    'panel' => 'footer'
	  ) );

	$wp_customize->add_setting(
	    'footertext',
	    array(
	        'default' => $mywiki_options['footertext'],
	        'capability'     => 'edit_theme_options',
	        'sanitize_callback' => 'wp_kses_post',
	        'priority' => 20, 
	    )
	);
	$wp_customize->add_control(
	    'footertext',
	    array(
	        'section' => 'footerCopyright',                
	        'label'   => __('Enter Copyright Text','mywiki'),
	        'type'    => 'textarea',
	    )
	);

	$wp_customize->add_section(
    'footer_social_links',
    array(
      'title' => __('Footer Social Accounts', 'mywiki'),
      'priority' => 120,
      'description' => __( 'In first input box, you need to add FONT AWESOME shortcode which you can find ' ,  'mywiki').'<a target="_blank" href="'.esc_url('https://fortawesome.github.io/Font-Awesome/icons/').'">'.__('here' ,  'mywiki').'</a>'.__(' and in second input box, you need to add your social media profile URL.', 'mywiki').'<br />'.__(' Enter the URL of your social accounts. Leave it empty to hide the icon.' ,  'mywiki'),
      'panel' => 'footer'
    )
  );

$footer_social_icon_default = array(
	  array('url'=>$mywiki_options['fburl'],'icon'=>'fa-facebook'),
	  array('url'=>$mywiki_options['twitter'],'icon'=>'fa-twitter'),
	  array('url'=>$mywiki_options['googleplus'],'icon'=>'fa-google-plus'),
	  array('url'=>$mywiki_options['linkedin'],'icon'=>'fa-linkedin'),
  );

$footer_social_icon_link = array();
  for($i=1;$i <= 4;$i++):  
    $footer_social_icon[] =  array( 'slug'=>sprintf('footer_social_icon%d',$i),   
      'default' => $footer_social_icon_default[$i-1]['icon'],   
      'label' => esc_html__( 'Social Account ', 'mywiki') .$i,   
      'priority' => sprintf('%d',$i) );  
  endfor;
  foreach($footer_social_icon as $footer_social_icons){
    $wp_customize->add_setting(
      $footer_social_icons['slug'],
      array( 
       'default' => $footer_social_icons['default'],       
        'capability'     => 'edit_theme_options',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_text_field',
      )
    );
    $wp_customize->add_control(
      $footer_social_icons['slug'],
      array(
        'type'  => 'text',
        'section' => 'footer_social_links',
        'input_attrs' => array( 'placeholder' => esc_attr__('Enter Icon','mywiki') ),
        'label'      =>   $footer_social_icons['label'],
        'priority' => $footer_social_icons['priority']
      )
    );
  }
  $footer_social_icon_link = array();
  for($i=1;$i <= 4;$i++):  
    $footer_social_icon_link[] =  array( 'slug'=>sprintf('footer_social_icon_link%d',$i),   
      'default' => $footer_social_icon_default[$i-1]['url'],   
      'label' => esc_html__( 'Social Link ', 'mywiki' ) .$i,
      'priority' => sprintf('%d',$i) );  
  endfor;
  foreach($footer_social_icon_link as $footer_social_icon_links){
    $wp_customize->add_setting(
      $footer_social_icon_links['slug'],
      array(
        'default' => $footer_social_icon_links['default'],
        'capability'     => 'edit_theme_options',
        'type' => 'theme_mod',
        'sanitize_callback' => 'esc_url_raw',
      )
    );
    $wp_customize->add_control(
      $footer_social_icon_links['slug'],
      array(
        'type'  => 'text',
        'section' => 'footer_social_links',
        'priority' => $footer_social_icon_links['priority'],
        'input_attrs' => array( 'placeholder' => esc_html__('Enter URL','mywiki')),
      )
    );
  }

// Text Panel Starts Here 

}
add_action( 'customize_register', 'mywiki_customize_register' );