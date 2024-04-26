/* 
 * Copyright © 2017 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
define(['jquery', 'Magento_Ui/js/modal/confirm'], function ($, confirmation) {

return {
        handleClicks : function (obj){
            if(obj){
                    $element = obj;
                    if ($($element).hasClass('confirm-msg')) {
                        confirmation({
                            title: '',
                            content: $($element).data('confirm-msg'),
                            actions: {
                                confirm: function () {
                                    location.href = $($element).data('url');
                                },
                                cancel: function () {
                                    /* to do on cancel*/
                                }
                            },
                            buttons: [{
                                    text: $.mage.__('OK'),
                                    class: 'action secondary action-accept',
                                    /**
                                     * Click handler.
                                     */
                                    click: function (event) {
                                        this.closeModal(event, true);
                                    }
                                },
                                {
                                    text: $.mage.__('Cancel'),
                                    class: 'action secondary action-dismiss',
                                    /**
                                     * Click handler.
                                     */
                                    click: function (event) {
                                        this.closeModal(event);
                                    }
                                }
                            ]
                        });
                    } else {
                        location.href = $($element).attr('data-url');
                    }
                }
            }
    }
});