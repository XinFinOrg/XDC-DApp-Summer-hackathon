</div>

<!--Mel: 07/12/21-->
<br />
<div class="col-md-4">
</div>

<hr />
<footer role="contentinfo" id="footer">  
  <div id="inner-footer" class="clearfix container padding-top-bottom">
  	<?php $mywiki_options = get_option( 'faster_theme_options' ); ?>
	<div id="widget-footer" class="clearfix row">
    	<div class="col-md-4">
		  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer1') ) : ?>
          <?php endif; ?>
         </div>
         <div class="col-md-4">
		  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer2') ) : ?>
        <?php endif; ?>
		</div>
        <div class="col-md-4">
		  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer3') ) : ?>
        <?php endif; ?>
		</div>
    </div>
        <nav class="footer-menu-nav">
     	<ul class="footer-nav nav navbar-nav">
        	<?php $footer_silk1 = get_theme_mod('footer_social_icon_link1'); $footer_silk2 = get_theme_mod('footer_social_icon_link2'); $footer_silk3 = get_theme_mod('footer_social_icon_link3'); $footer_silk4 = get_theme_mod('footer_social_icon_link4');          
                $footer_social_icon_default = array(
                  array('url'=>$mywiki_options['fburl'],'icon'=>'fa-facebook'),
                  array('url'=>$mywiki_options['twitter'],'icon'=>'fa-twitter'),
                  array('url'=>$mywiki_options['googleplus'],'icon'=>'fa-google-plus'),
                  array('url'=>$mywiki_options['linkedin'],'icon'=>'fa-linkedin'),
                );?>
          <?php for($i=1; $i<=4; $i++) : 
              $footer_silk = get_theme_mod('footer_social_icon_link'.$i,$footer_social_icon_default[$i-1]['url']);
               if(!empty($footer_silk)): ?>
                 <li><a href="<?php echo esc_url(get_theme_mod('footer_social_icon_link'.$i,$footer_social_icon_default[$i-1]['url'])); ?>" class="socia_icon" title="" target="_blank">
                      <i class="fa <?php echo esc_attr(get_theme_mod('footer_social_icon'.$i,$footer_social_icon_default[$i-1]['icon'])); ?>"></i>
                  </a></li>
            <?php endif; ?>
        <?php endfor; ?>
      </ul>
    </nav>
    <p class="attribution">
	   <?php $footertext = get_theme_mod('footertext',$mywiki_options['footertext']);
      if(!empty($footertext)):
        echo esc_attr(get_theme_mod('footertext',$mywiki_options['footertext'])); 
      endif;

      //Mel: 07/12/21
      //$footer_powered= sprintf(/* translators: 1 is site url */esc_html__( ' Powered by %1$s', 'mywiki' ), '<a href="'.esc_url('http://fasterthemes.com/wordpress-themes/mywiki').'" target="_blank">MyWiki WordPress Theme</a>' );
      echo wp_kses_post($footer_powered);    ?>
     </p>
</footer>
    
  </div>
  <!-- end #inner-footer -->
<!-- end footer -->
<!-- end #maincont .container --> 
<?php wp_footer(); // js scripts are inserted using this function ?>
</body>
</html>