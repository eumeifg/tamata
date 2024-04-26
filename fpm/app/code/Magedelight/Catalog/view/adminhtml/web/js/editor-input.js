/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
require([
    'jquery',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function (jQuery) {
    jQuery(document).ready(function () {
        var editor;

        editor = new wysiwygSetup(
                'productrequest_short_description', {
                    "width": "99%",
                    "height": "200px",
                    "plugins": [{"name": "image"}],
                    "tinymce4": {"toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap", "plugins": "advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });

        editor.setup("exact");
        jQuery('#productrequest_short_description')
                .addClass('wysiwyg-editor')
                .data(
                        'wysiwygEditor',
                        editor
                        );

        editor = new wysiwygSetup(
                'productrequest_description',
                {
                    "width": "99%",
                    "height": "200px",
                    "plugins": [{"name": "image"}],
                    "tinymce4": {"toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap", "plugins": "advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                    }
                });

        editor.setup("exact");
        jQuery('#productrequest_description')
                .addClass('wysiwyg-editor')
                .data(
                        'wysiwygEditor',
                        editor
                        );

        jQuery('#productrequest_short_description').attr('maxlength', 255);
    });
});