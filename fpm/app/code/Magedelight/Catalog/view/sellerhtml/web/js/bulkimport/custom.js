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
define(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate'], function ($, alert) {

    return {
        /* Upload/import product requests starts. */
        uploadcatalog: function (url) {

            $('#import-category-error').html('');
            if ($('.catid').val() == "") {
                $('#import-category-error').html($.mage.__('Please select category.')).effect( "shake",{times:1,distance:2}, 1000 );
                return false;
            }

            formdata = new FormData();
            var file_data = $("#catalog-upload").prop("files")[0];
            formdata.append("catalog-upload", file_data);
            if (!file_data) {
                $('#import-category-error').html($.mage.__('Please upload a file.')).effect( "shake",{times:1,distance:2}, 1000 );
                return false;
            }

            /*Validate File Size.*/
            $fileValue = $('#catalog-upload').val();

            if ($fileValue) {
                var file_size = $('#catalog-upload')[0].files[0].size;
                var file_type = $('#catalog-upload')[0].files[0].type;
                var sizeInMB = (file_size / 1024 / 1024);
            }
            if (sizeInMB > 2) {
                $('#catalouge-upload-error').html($.mage.__('Please select file of appropriate size(Max. 2MB).'));
                $('#catalog-upload').val('');
                return false;
            }
            if ($fileValue.indexOf('csv') == -1) {
                $('#catalouge-upload-error').html($.mage.__('Please upload csv file only.'));
                $('#catalog-upload').val('');
                return false;
            }
            $('#catalouge-upload-error').html('');

            formdata.append("categoryId", $('.catid').val());
            
            var custom = this;
            $.ajax({
                url: url,
                type: "POST",
                showLoader: true,
                data: formdata,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('.catalog-upload').children().html($.mage.__("Uploading.."));
                },
                success: function (res)
                {
                    var results = JSON.parse(res);
                    alert({
                        content: results.html
                    });
                    custom.clearFileContent('catalog-upload','single');
                    $("#import-category option:first").attr("selected", true);
                    $('.catid').val("");
                },
                error: function (xhr) {
                    var results = JSON.parse(xhr.responseText);
                    alert({
                        content: results.message + '<br />' + results.parameters.fieldName
                    });
                }

            });

            $('.catalog-upload').children().html($.mage.__("Upload"));
        },
        /* Upload/import product requests ends. */

        clearFileContent: function (inputElementId,numberOfFilesToUpload)
        {
            if (inputElementId) {
                var file_element = $("#" + inputElementId);
                if(numberOfFilesToUpload == 'single'){
                    file_element.next().find('span').html($.mage.__('Choose a file'));
                }else{
                    file_element.next().find('span').html($.mage.__('Choose file(s)'));
                }
                file_element.replaceWith(file_element = file_element.clone(true));
            }
        },
        uploadbulkoffer: function (url) {

            /*Checking file selected or not */
            $fileValue = $('#catalog-bulk-upload').val();

            /*Validate File Size. */
            if ($fileValue) {
                var file_size = $('#catalog-bulk-upload')[0].files[0].size;
                var sizeInMB = (file_size / 1024);
            }
            if (sizeInMB > 512) {
                $('#catalog-bulk-upload-error').html($.mage.__('Please select appropriate file size(Maximum 512KB).'));
                $('#catalog-bulk-upload').val('');
                return false;
            }
            $('#catalog-bulk-upload-error').html('');
            formdata = new FormData();
            var file_data = $("#catalog-bulk-upload").prop("files")[0];
            formdata.append("catalog-bulk-upload", file_data);
            
            $('#catalog-bulk-upload-error').html('');
            if (!file_data) {
                $('#catalog-bulk-upload-error').html($.mage.__('Please upload a file.')).effect( "shake",{times:1,distance:2}, 1000 );
                return false;
            }

            $.ajax({
                url: url,
                type: "POST",
                showLoader: true,
                data: formdata,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('.catalog-bupload').children().html($.mage.__("Uploading.."));
                },
                success: function (res)
                {
                    var results = JSON.parse(res);
                    if (results.success) {
                        alert({
                            content: results.success
                        });
                    } else {
                        alert({
                            content: results.error
                        });
                    }
                }

            });

            this.clearFileContent('catalog-bulk-upload','single');
            $('.catalog-bupload').children().html($.mage.__("Upload"));
        },
        getAttributeList: function (url)
        {
            var len = jQuery('.search-choice').length;
            if (len <= '0') {
                alert({
                    content: $.mage.__('Select Category To Proceed From Drop Down.')
                });
                return false;

            }
            var cid = jQuery('.category-ids').val();
            window.location = url+'?cid='+cid;
        },
        setFileInputChangeEvent:function (element) 
        {
            var input = $( '#'+element);
            var label	 = input.next(),
            labelVal = label.html();

            input.on( 'change', function( e )
            {
                    var fileName = '';
                    if( this.files && this.files.length > 1 ){
                        fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
                    }else{
                        fileName = e.target.value.split( '\\' ).pop();
                    }

                    if( fileName ){
                        label.find( "span" ).html(fileName);
                    }else{
                        label.html(labelVal);
                    }
            });

        }

    }
});