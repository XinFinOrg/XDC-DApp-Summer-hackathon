(function ($, $document) {
    "use strict";

    function disable_register_menu_item() {
        var $disabled = $('.user-menus-registration-disabled');

        if ($disabled.length) {
            $disabled.find('li:eq(1) input[type="checkbox"]').attr('disabled', true);
        }
    }


    function redirect_type() {
        var $this = $(this),
            $url = $this.parents('.menu-item').find('.nav_item_options-redirect_url');

        if ($this.val() == 'custom') {
            $url.slideDown();
        } else {
            $url.slideUp();
        }
    }

    function avatar_check() {
        var $label = $(this),
            $size = $label.parents('.menu-item').find('.nav_item_options-avatar_size');

        if ($label.val().indexOf('{avatar}') >= 0) {
            $size.slideDown();
        } else {
            $size.slideUp();
        }
    }

    function which_users() {
        var $this = $(this),
            $item = $this.parents('.menu-item'),
            $can_see = $item.find('.nav_item_options-can_see'),
            $roles = $item.find('.nav_item_options-roles'),
            $insert_button = $item.find('.jpum-user-codes');

        if ($this.val() === 'logged_in') {
            $can_see.slideDown();
            $roles.slideDown();
            $item.addClass('show-insert-button');
            $insert_button.fadeOut(0).fadeIn();
        } else {
            $can_see.slideUp();
            $roles.slideUp();
            $insert_button.fadeOut(function () {
                $item.removeClass('show-insert-button');
            });
        }
    }

    function toggle_user_codes() {
        $(this).parent().toggleClass('open');
    }

    function reset_user_codes(e) {
        if (e !== undefined && $(e.target).parents('.jpum-user-codes').length) {
            return;
        }

        $('.jpum-user-codes').removeClass('open');
    }

    function insert_user_code(event) {
        var $this = $(this),
            $input = $this.parents('p').find('input'),
            val = $input.val();

        event.which = event.which || event.keyCode;

        if (event.type === 'keypress' && event.keyCode !== 13 && event.keyCode !== 32) {
            return;
        }

        $input.val(val + "{" + $this.data('code') + "}").trigger('change');
        reset_user_codes();

        event.preventDefault();
    }

    function append_user_codes() {
        return $('input.edit-menu-item-title').each(function () {
            var $this = $(this).parents('label'),
                template = _.template($('#tmpl-jpum-user-codes').html());

            if (!$this.parents('p').find('.jpum-user-codes').length) {
                $this.after(template());
            }
        });
    }

    function refresh_all_items() {
        append_user_codes();
        $('.nav_item_options-redirect_type select').each(redirect_type);
        $('.nav_item_options-which_users select').each(which_users);
        $('.nav_item_options-which_users select').each(which_users);
        $('input.edit-menu-item-title').each(avatar_check);
    }

    $document
        .on('change', '.nav_item_options-redirect_type select', redirect_type)
        .on('change', '.nav_item_options-which_users select', which_users)
        .on('change keyup focusout', 'input.edit-menu-item-title', avatar_check)
        .on('click', '.jpum-user-codes > button', toggle_user_codes)
        .on('click keypress', '.jpum-user-codes li > a', insert_user_code)
        .on('click', reset_user_codes)
        .on('menu-item-added', refresh_all_items);

    // Add click event directly to submit buttons to prevent being prevented by default action.
    $('.submit-add-to-menu').click(function () {
        setTimeout(refresh_all_items, 1000);
    });

    $(refresh_all_items);
    $(disable_register_menu_item);

}(jQuery, jQuery(document)));