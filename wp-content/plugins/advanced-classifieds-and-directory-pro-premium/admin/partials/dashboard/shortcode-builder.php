<?php

/**
 * Dashboard: Shortcode Builder.
 *
 * @link    http://pluginsware.com
 * @since   1.7.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

$fields = acadp_get_shortcode_fields();
?>

<div id="acadp-shortcode-builder">
    <!-- Shortcode Selector -->
    <div id="acadp-shortcode-selector">
        <p class="about-text"><?php esc_html_e( 'Select a shortcode type', 'advanced-classifieds-and-directory-pro' ); ?></p>
        <ul class="acadp-radio-list radio horizontal">
            <?php
            foreach ( $fields as $shortcode => $params ) {
                printf( '<li><label><input type="radio" name="shortcode" value="%s"%s/>%s</label></li>', esc_attr( $shortcode ), checked( $shortcode, 'listings', false ), esc_html( $params['title'] ) );
            }
            ?>
        </ul>         
    </div>

    <!-- Shortcode Builder -->
    <div class="acadp-row"> 
        <!-- Left Column -->  
        <div class="acadp-col acadp-col-p-60">
            <div class="acadp-col-content">         
                <?php foreach ( $fields as $shortcode => $params ) : ?>
                    <div id="acadp-shortcode-form-<?php echo esc_attr( $shortcode ); ?>" class="acadp-shortcode-form">
                        <?php foreach ( $params['sections'] as $name => $section ) : ?>                         
                            <div class="acadp-shortcode-section acadp-shortcode-section-<?php echo esc_attr( $name ); ?> <?php if ( 'general' == $name ) echo 'acadp-active'; ?>"> 
                                <div class="acadp-shortcode-section-header">            
                                    <span class="dashicons-before dashicons-plus"></span>
                                    <span class="dashicons-before dashicons-minus"></span>
                                    <?php echo esc_html( $section['title'] ); ?>
                                </div>  
                                                        
                                <div class="acadp-shortcode-controls" <?php if ( 'general' != $name ) echo 'style="display: none;"'; ?>>
                                    <?php foreach ( $section['fields'] as $field ) : ?>
                                        <div class="acadp-shortcode-control acadp-shortcode-control-<?php echo esc_attr( $field['name'] ); ?>">                                                
                                            <?php if ( 'text' == $field['type'] || 'url' == $field['type'] || 'number' == $field['type'] ) : ?>                                        
                                                <label><?php echo esc_html( $field['label'] ); ?></label>
                                                <input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" class="acadp-shortcode-field widefat" value="<?php echo esc_attr( $field['value'] ); ?>" data-default="<?php echo esc_attr( $field['value'] ); ?>" />
                                            <?php elseif ( 'textarea' == $field['type'] ) : ?>
                                                <label><?php echo esc_html( $field['label'] ); ?></label>
                                                <textarea name="<?php echo esc_attr( $field['name'] ); ?>" class="acadp-shortcode-field widefat" rows="8" data-default="<?php echo esc_attr( $field['value'] ); ?>"><?php echo esc_textarea( $field['value'] ); ?></textarea>
                                            <?php elseif ( 'select' == $field['type'] || 'radio' == $field['type'] ) : ?>
                                                <label><?php echo esc_html( $field['label'] ); ?></label> 
                                                <select name="<?php echo esc_attr( $field['name'] ); ?>" class="acadp-shortcode-field widefat" data-default="<?php echo esc_attr( $field['value'] ); ?>">
                                                    <?php
                                                    foreach ( $field['options'] as $value => $label ) {
                                                        printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $value, $field['value'], false ), esc_html( $label ) );
                                                    }
                                                    ?>
                                                </select>                                                                               
                                            <?php elseif ( 'checkbox' == $field['type'] ) : ?>                                        
                                                <label>				
                                                    <input type="checkbox" name="<?php echo esc_attr( $field['name'] ); ?>" class="acadp-shortcode-field" value="1" data-default="<?php echo esc_attr( $field['value'] ); ?>" <?php checked( $field['value'] ); ?> />
                                                    <?php echo esc_html( $field['label'] ); ?>
                                                </label>                                            
                                            <?php elseif ( 'color' == $field['type'] ) : ?>                                        
                                                <label><?php echo esc_html( $field['label'] ); ?></label>
                                                <input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" class="acadp-shortcode-field acadp-color-picker widefat" value="<?php echo esc_attr( $field['value'] ); ?>" data-default="<?php echo esc_attr( $field['value'] ); ?>" />
                                            <?php elseif ( 'locations' == $field['type'] ) : ?>
                                                <label><?php echo esc_html( $field['label'] ); ?></label> 
                                                <?php
                                                $args = array(
                                                    'show_option_none'  => '-- ' . esc_html( $field['label'] ) . ' --',
                                                    'option_none_value' => max( 0, (int) $general_settings['base_location'] ),
                                                    'child_of'          => max( 0, (int) $general_settings['base_location'] ),
                                                    'taxonomy'          => 'acadp_locations',
                                                    'name' 			    => esc_attr( $field['name'] ),
                                                    'class'             => 'acadp-shortcode-field widefat',
                                                    'orderby'           => 'name',
                                                    'selected'          => 0,
                                                    'hierarchical'      => true,
                                                    'depth'             => 10,
                                                    'show_count'        => false,
                                                    'hide_empty'        => false
                                                );
                                                
                                                wp_dropdown_categories( $args );
                                                ?>
                                            <?php elseif ( 'categories' == $field['type'] ) : ?>
                                                <label><?php echo esc_html( $field['label'] ); ?></label> 
                                                <?php
                                                $args = array(
                                                    'show_option_none'  => '-- ' . esc_html( $field['label'] ) . ' --',
                                                    'option_none_value' => 0,
                                                    'taxonomy'          => 'acadp_categories',
                                                    'name' 			    => esc_attr( $field['name'] ),
                                                    'class'             => 'acadp-shortcode-field widefat',
                                                    'orderby'           => 'name',
                                                    'selected'          => 0,
                                                    'hierarchical'      => true,
                                                    'depth'             => 10,
                                                    'show_count'        => false,
                                                    'hide_empty'        => false
                                                );                           
                                                
                                                wp_dropdown_categories( $args );
                                                ?>
                                            <?php endif; ?>

                                            <!-- Hint -->
                                            <?php if ( isset( $field['description'] ) ) : ?>                            
                                                <span class="description"><?php echo wp_kses_post( $field['description'] ); ?></span>                        
                                            <?php endif; ?>                                                            
                                        </div>    
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <p><input type="button" id="acadp-generate-shortcode" class="button-primary" value="<?php esc_attr_e( 'Generate Shortcode', 'advanced-classifieds-and-directory-pro' ); ?>" /></p>
            </div>
        </div>

        <!-- Right Column -->
        <div class="acadp-col acadp-col-p-40">
            <div class="acadp-col-content">
                <p class="about-description">
                    <?php esc_html_e( '"Advanced Classifieds and Directory Pro" provides several methods to add the plugin content (listings, categories, locations, search form, etc.) in your site front-end. Choose one of the following methods best suited for you,', 'advanced-classifieds-and-directory-pro' ); ?>
                </p>
                <p>
                    <span class="dashicons dashicons-arrow-left-alt"></span> 
                    <?php esc_html_e( 'Use the shortcode builder in this page to build your shortcode, then add it in your POST/PAGE.', 'advanced-classifieds-and-directory-pro' ); ?>
                </p>
                <p>
                    2. <?php printf( __( 'Use our "Advanced Classifieds and Directory Pro" <a href="%s" target="_blank">Gutenberg blocks</a>.', 'advanced-classifieds-and-directory-pro' ), esc_url( admin_url( 'post-new.php?post_type=page' ) ) ); ?>
                </p>
                <p>
                    3. <?php printf( __( 'Use our <a href="%s" target="_blank">widgets</a> in your website sidebars.', 'advanced-classifieds-and-directory-pro' ), esc_url( admin_url( 'widgets.php' ) ) ); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Shortcode Modal -->
    <div id="acadp-shortcode-modal" class="acadp-modal" style="display: none;">
        <div class="acadp-modal-bg"></div>          
        <div class="acadp-modal-content">                 
            <div class="acadp-modal-body">
                <span class="acadp-modal-close">&times;</span>

                <p class="acadp-modal-title"><?php esc_html_e( 'Congrats! copy the shortcode below and paste it in your POST/PAGE where you need the gallery,', 'advanced-classifieds-and-directory-pro' ); ?></p>
                <textarea id="acadp-shortcode" class="widefat code" autofocus="autofocus" onfocus="this.select()"></textarea>
            </div>
        </div>
    </div>
</div>
