<?php

/**
 * This template displays the ACADP user dashboard.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-user acadp-user-dashboard">
	<div class="media acadp-margin-bottom">
  		<div class="pull-left">
      		<?php echo get_avatar( $userid ); ?>
  		</div>
  		<div class="media-body">
    		<h4 class="media-heading"><?php echo esc_html( $user->display_name ); ?></h4>
    		<?php echo esc_html( $user->description ); ?>
            <?php the_acadp_user_menu(); ?>
  		</div>
	</div>
    
    <?php if ( acadp_current_user_can('edit_acadp_listings') ) : ?>
    	<div class="row">
    		<div class="col-md-6">
        		<div class="panel panel-default">
  					<div class="panel-body text-center">
    					<p class="lead"><?php esc_html_e( "Total Listings", 'advanced-classifieds-and-directory-pro' ); ?></p>
                    	<span class="text-muted"><?php echo esc_html( acadp_get_user_total_listings() ); ?></span>
  					</div>
				</div>
        	</div>
        	<div class="col-md-6">
        		<div class="panel panel-default">
  					<div class="panel-body text-center">
    					<p class="lead"><?php esc_html_e( "Active Listings", 'advanced-classifieds-and-directory-pro' ); ?></p>
                    	<span class="text-muted"><?php echo esc_html( acadp_get_user_total_active_listings() ); ?></span>
  					</div>
				</div>
        	</div>
    	</div>
    <?php endif; ?>
</div>