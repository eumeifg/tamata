/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 *This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_SocialLogin
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/mage'
    ],


    function (jQuery, modal) {

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Login/Registration',
            buttons: false,
            modalClass: 'social-popup'
        };
        var popup = modal(options, jQuery('#md_popup'));
        jQuery('#md_popup').modal();


        jQuery(document).on('click touchstart', '.header li.md-custom-toplink a', function () {

            var display_id = jQuery(this).attr("class");
            //alert(display_id);
            if (jQuery(window).width() <= 767) {
                jQuery('html').toggleClass('nav-open nav-close');
            }
            jQuery('.modal-inner-wrap').addClass('md-social-popup');
            if (display_id == 'md-login-form') {
                displaysignin();
            } else if (display_id == 'md-create-user') {
                displayregister();
            }
            jQuery('#md_popup').modal('openModal');
        });

        /* Login form */
        jQuery('#md-button-sociallogin-login').click(function () {
            jQuery('#md-sociallogin-form').submit();
        });

        jQuery("#md-login-form input").keypress(function (e) {
            if (e.keyCode == 13) {
                jQuery('#md-sociallogin-form').submit();
            }
        });
        
        jQuery('#md-sociallogin-form').mage('validation', {
            submitHandler: function (form) {
                jQuery.ajax({
                    url: form.action,
                    data: jQuery('#md-sociallogin-form').serialize(),
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                }).done(function (data) {
                    if (data) {
                        var messageBox = jQuery('#md-invalid-email');
                        messageBox.html(data.html_message);
                        if (data.url) {
                            window.location.href = data.url;
                        }
                    }
                });
            }
        });

        /* Forgot Form */
        jQuery('#md-button-sociallogin-login-forgot').click(function () {
            jQuery('#md-sociallogin-form-forgot').submit();
        });
        jQuery('#md-sociallogin-form-forgot').mage('validation', {
            submitHandler: function (form) {
                jQuery.ajax({
                    url: form.action,
                    data: jQuery('#md-sociallogin-form-forgot').serialize(),
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                }).done(function (data) {
                    console.log(data);
                    if (data) {
                        var messageBox = jQuery('#md-forgot-email');
                        messageBox.html(data.html_message);
                    }
                });
            }
        });

        /* Register form */
        jQuery('#md-button-social-login-register').click(function () {
            jQuery('#md-sociallogin-form-create').submit();
        });
        jQuery('#md-sociallogin-form-create').mage('validation', {
            submitHandler: function (form) {
                jQuery.ajax({
                    url: form.action,
                    data: jQuery('#md-sociallogin-form-create').serialize(),
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                }).done(function (data) {
                    var messageBox = jQuery('#md-invalid-register');
                    messageBox.html('');
                    if (data) {
                        messageBox.html(data.html_message);
                        if (data.url) {
                            window.location.href = data.url;
                        }
                    }
                });
            }
        });
    }
);

function displaysignin() {
    jQuery('.md-forgot-user').hide();
    jQuery('.md-register-user').hide();
    jQuery('.md-login-user').show();
}

function displayforgot() {
    jQuery('.md-forgot-user').show();
    jQuery('.md-register-user').hide();
    jQuery('.md-login-user').hide();
}

function displayregister() {
    jQuery('.md-forgot-user').hide();
    jQuery('.md-register-user').show();
    jQuery('.md-login-user').hide();
}