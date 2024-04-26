define(
    [
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal',
    'jquery'
    ], function (_, uiRegistry, select, modal, $) {
        'use strict';

        $('body').on(
            'DOMNodeInserted', 'select[name="labeltype"]', function () {
                var textcolorpicker = uiRegistry.get('index = textcolorpicker');
                if(uiRegistry.get('index = labeltype').initialValue == 'image') {
                    uiRegistry.get('index = image').show();
                    textcolorpicker.hide();
                }
                else {
                    uiRegistry.get('index = image').hide();
                    textcolorpicker.show();
                }
            }
        );

        return select.extend(
            {

                /**
                 * On value change handler.
                 *
                 * @param {String} value
                 */
                onUpdate: function (value) {
                    console.log('Selected Value: ' + value);

                    var field1 = uiRegistry.get('index = image');
                    var field2 = uiRegistry.get('index = productlabeltext');
                    var textcolorpicker = uiRegistry.get('index = textcolorpicker');
                    if (value == 'image') {
                        field1.show();
                        field2.hide();
                        textcolorpicker.hide();
                    }else{
                        field1.hide();
                        textcolorpicker.show();
                    }

                    return this._super();
                },
            }
        );
    }
);