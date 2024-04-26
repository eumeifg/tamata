define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        initialize: function (){
            var status = this._super().initialValue;
            this.makevisilibily(status);
            return this;

        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {

            this.makevisilibily(value);
            return this._super();
        },

        makevisilibily: function (value) {

            var field1 = uiRegistry.get('slider_form.slider_form.design.is_responsive');
            var field2 = uiRegistry.get('slider_form.slider_form.design.responsive_items');
            var field3 = uiRegistry.get('slider_form.slider_form.design.autoWidth');
            var field4 = uiRegistry.get('slider_form.slider_form.design.autoHeight');
            var field5 = uiRegistry.get('slider_form.slider_form.design.loop');
            var field6 = uiRegistry.get('slider_form.slider_form.design.nav');
            var field7 = uiRegistry.get('slider_form.slider_form.design.dots');
            var field8 = uiRegistry.get('slider_form.slider_form.design.lazyLoad');
            var field9 = uiRegistry.get('slider_form.slider_form.design.autoplay');
            var field10 = uiRegistry.get('slider_form.slider_form.design.autoplayTimeout');
            var field11 = uiRegistry.get('slider_form.slider_form.design.autoplayHoverPause');

            if (value == 1) {
                field1.visible(true);
                field2.visible(true);
                field3.visible(true);
                field4.visible(true);
                field5.visible(true);
                field6.visible(true);
                field7.visible(true);
                field8.visible(true);
                field9.visible(true);
                field10.visible(true);
                field11.visible(true);

            } else {
                field1.visible(false);
                field2.visible(false);
                field3.visible(false);
                field4.visible(false);
                field5.visible(false);
                field6.visible(false);
                field7.visible(false);
                field8.visible(false);
                field9.visible(false);
                field10.visible(false);
                field11.visible(false);

            }

            return this._super();
        }
    });
});
