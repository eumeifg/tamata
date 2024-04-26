define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'mage/url',
    'wysiwygAdapter'
], function (_, uiRegistry, select, url,tinyMCE) {
    'use strict';
    return select.extend({

        initialize: function (){

            var obj = this;
            var type= this._super().initialValue;
            setTimeout(function(){ 
               obj.makevisilibily(type);
           }, 1000);

            var defaultTemplate=uiRegistry.get('ktpl_banner_form.areas.general.general.default_template');

            setTimeout(function(){ 
               obj.displayTemplateImage(defaultTemplate.value());
           }, 1000);
            defaultTemplate.on('update',function(){
               obj.displayTemplateImage(defaultTemplate.value());
            })

            jQuery('body').on('click',"#banner_load_template_btn",function(){
                var toggleMCEEditor = jQuery('#togglebanner_form_content');
                var defaultTemplate=uiRegistry.get('ktpl_banner_form.areas.general.general.default_template');
                var templateContent=obj.displayTemplateContent(defaultTemplate.value());

                 tinyMCE.get('banner_form_content').setContent(templateContent);

                   toggleMCEEditor.trigger('click');
            })
            return this;

        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.makevisilibily(value);
            this._super();
            var defaultTemplate=uiRegistry.get('ktpl_banner_form.areas.general.general.default_template');

              this.displayTemplateImage(defaultTemplate.value());
            return this._super();
        },

        makevisilibily: function (value) {
            var field1 = uiRegistry.get('ktpl_banner_form.areas.general.general.default_template');
            var field2 = uiRegistry.get('ktpl_banner_form.areas.general.general.default_image');
            var fieldName = uiRegistry.get('inputName = general[content]');

            var field3 = uiRegistry.get('inputName = general[content]');
            var field4 = uiRegistry.get('inputName = general[image]');
            console.log(field3);

            if (value == 1) {
                field1.visible(true);
                field2.visible(true);
                field3.visible(true);
                field4.visible(false);
            } else {
                field1.visible(false);
                field2.visible(false);
                field3.visible(false);
                field4.visible(true);
            }

        },
          displayTemplateImage: function (defaultTemplate) { 
            var data = this.getContentAndImage(defaultTemplate);

            jQuery("img#mp-demo-image").attr("src",data['imgSrc']);

            },

            displayTemplateContent: function (defaultTemplate) { 
            var data = this.getContentAndImage(defaultTemplate);
            return data['templateContent'];

            },


            getContentAndImage:function (defaultTemplate) {
                var imgSrc="";
                var templateContent="";
                var data =[];

                if(defaultTemplate==0)
                {
                    imgSrc=window.defaultTemplate1;
                    templateContent=window.defaultContent1;
                }
                else if(defaultTemplate==1)
                {
                  imgSrc=window.defaultTemplate2;
                  templateContent=window.defaultContent2;
                }
                else if(defaultTemplate==2)
                {
                  imgSrc=window.defaultTemplate3;
                  templateContent=window.defaultContent3;
                }
                else if(defaultTemplate==3)
                {
                  imgSrc=window.defaultTemplate4;
                  templateContent=window.defaultContent4;
                }
                else if(defaultTemplate==4)
                {
                  imgSrc=window.defaultTemplate5;
                  templateContent=window.defaultContent5;
                }
                data['imgSrc'] = imgSrc;
                data['templateContent'] = templateContent;
                return data;
            }
        });
});
