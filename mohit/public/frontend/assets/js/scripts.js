
jQuery(document).ready(function(){
	

  jQuery(".search-icon").click(function(){
    jQuery(".search-form").slideToggle();
    });

  

jQuery(".toggle").click(function(){
jQuery("ul.menu").toggleClass('open');
jQuery(this).toggleClass('open');
});


jQuery('html').click(function() {
    if (jQuery('ul.menu').hasClass('open')){
        jQuery('ul.menu').removeClass('open');
        jQuery('.toggle').toggleClass('open');
    }
});

// replace mobile-nav with your entire nav container
jQuery('.nav_area').click(function(e){
    e.stopPropagation();
});


jQuery("li.menu-item-has-children > a").after('<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="15" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></svg></span>');


jQuery('li.menu-item-has-children  > span').click(function() {
    jQuery(this).next('.sub-menu').slideToggle();
    jQuery(this).closest('li').siblings().find('ul').hide();
    jQuery(this).find('svg').toggleClass('active-submenu');
    jQuery(this).closest('li').siblings().find('svg').removeClass('active-submenu');
	jQuery(this).closest('li').siblings().find('.fa').addClass('fa-chevron-down');	
});







jQuery('.adults-number .minus').click(function () {
    var jQueryinput = jQuery(this).parent().find('input');
    var count = parseInt(jQueryinput.val()) - 1;
    count = count < 1 ? 1 : count;
    jQueryinput.val(count);
    jQueryinput.change();
    return false;
});

jQuery('.child-number .minus').click(function () {
    var jQueryinput = jQuery(this).parent().find('input');
    var count = parseInt(jQueryinput.val()) - 1;
    count = count < 0 ? 0 : count;
    jQueryinput.val(count);
    jQueryinput.change();
    return false;
});


jQuery('.infant-number .minus').click(function () {
    var jQueryinput = jQuery(this).parent().find('input');
    var count = parseInt(jQueryinput.val()) - 1;
    count = count < 0 ? 0 : count;
    jQueryinput.val(count);
    jQueryinput.change();
    return false;
});


jQuery('.plus').click(function () {
    var jQueryinput = jQuery(this).parent().find('input');
    jQueryinput.val(parseInt(jQueryinput.val()) + 1);
    jQueryinput.change();
    return false;
});

});



