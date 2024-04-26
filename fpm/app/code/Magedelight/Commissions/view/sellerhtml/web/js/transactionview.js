/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

define([
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($,modal) {
        'use strict';

        return{
                view: function (config,element) 
                {
                    $(element).click(function (event) {
                        var transaction_id = $(this).data('transaction_id');
                        

                        $.ajax({
                            url:config.ajaxUrl,
                            type: 'post',
                            data:{'id':transaction_id},
                            dataType: 'json',
                            showLoader:true,
                            success: function(res)
                            {
                                $('#popup-modal').html(res);
                                var options = {
                                    type: 'popup',
                                    responsive: true,
                                    innerScroll: true,
                                    title: $.mage.__('Transaction Details'),
                                    buttons: false,
                                };
                                var popup = modal(options, $('#popup-modal'));
                                $('#popup-modal').modal('openModal');
                            }
                        }).always(function(){});
                    });
                },
                getSampleTemplates: function (config,element) 
                {
                    $(element).change(function (event) {
                        var transaction_id = $(this).val();

                        $.ajax({
                            url:config.ajaxUrl,
                            type: 'post',
                            data:{'id':transaction_id},
                            dataType: 'json',
                            showLoader:true,
                            success: function(res)
                            {
                                tinymce.activeEditor.setContent(res);
                            }
                        }).always(function(){});
                    });
                }
        } 
    }
);
