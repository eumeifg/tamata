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
/*jshint browser:true, jquery:true*/
/*global define, clearTimeout, setTimeout*/
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'rbpopup',
    'mage/calendar',
    'mage/translate',
    'domReady!'
], function ($, confirmation,alert) {
    'use strict';

    return {
        createTable: function(
            variantOpt,
            headerColumns,
            requiredOptionsMsg,
            conditions,
            warrantyTypes
        ) {
            var self = this;
            var attrColOffset = 2;
            var mytable = $('<table></table>').attr({id: "basicTable", class: "data table variant-table-grid"});
            $('#variantGrid').html('');

            /* Set/Append attributes in header columns*/
            var variantAttributes = $('#variantsAttributeCodes').val().split(',');
            var totalAttrs = Object.keys(variantAttributes).length;
            for (var i in variantAttributes) {
                if ($.type(variantAttributes[i]) === 'string' ) {
                    headerColumns.splice((Number(i) + Number(attrColOffset)), 0, self.toTitleCase(variantAttributes[i]));
                }
            }
            /* Set/Append attributes in header columns*/

            /* Create header row */
            self.createTableHeaderRow(mytable, headerColumns);
            /* Create header row */

            /* Get possible combinations. */
            var total_combinations = 1;
            for (var p = 0; p < totalAttrs; p++) {
                total_combinations *= $('input[name="' + variantAttributes[p] + '[]"]:checked').length;
            }
            /* Get possible combinations. */

            /* Validate atleaset one or more options checked or not in attributes*/
            if(!self.validateOptions(total_combinations, requiredOptionsMsg)){
                return false;
            }

            /* Create matrix of col_row with attribute label and value */
            self.createTableMatrix(
                variantOpt,
                mytable,
                headerColumns,
                total_combinations,
                attrColOffset,
                variantAttributes,
                conditions,
                warrantyTypes
            );

            mytable.appendTo("#variantGrid");

            /* Set date picker to date columns */
            self.setDatePickerToFields()
        },

        toTitleCase: function (str) {
            return str.replace(/(?:^|\s)\w/g, function(match) {
                return match.toUpperCase();
            });
        },

        createTableHeaderRow: function (mytable, headerColumns) {
            /* Create header row */
            var rowHead = $('<tr></tr>').attr({class: ["col"].join(' ')}).appendTo(mytable);
            $.each(headerColumns, function( key, value ) {
                var attributeName = value.replace(/\s+/g, '_').toLowerCase();
                var header = $('<th></th>').attr({class: attributeName.split('_').join('-')}).text(value);
                if (attributeName === 'image') {
                    attributeName = 'images';
                    header = $('<th></th>').attr({class: attributeName.split('_').join('-')}).text(value);
                }
                var mandatoryFields = ['Price'];
                if ($.inArray(value, mandatoryFields) != -1) {
                    $("<sup>*</sup>").appendTo(header);
                }
                header.appendTo(rowHead);
            });

            mytable.prepend('<thead></thead>');
            mytable.find('thead').append(mytable.find("tr:eq(0)"));
        },

        createTableMatrix: function (
            variantOpt,
            mytable,
            headerColumns,
            total_combinations,
            attrColOffset,
            variantAttributes,
            conditions,
            warrantyTypes
        ) {
            var self = this;
            /* Create matrix of col_row with attribute label and value */
            for (var x = 0; x < total_combinations; x++)
            {
                var row = $('<tr></tr>').appendTo(mytable);
                var vendorSku = '';

                $.each(headerColumns, function( key, value ) {
                    var attributeName = self.attributeNameToLowerCaseWithSeparator(value);
                    var eleId = x + "_" + attributeName;
                    if(attributeName === 'image') {
                        attributeName = "child_"+attributeName;
                    }
                    var eleName = "variants_data[" + x + "][" + attributeName + "]";
                    var eleValue = variantOpt.label[x][key - attrColOffset];
                    var eleValueId = variantOpt.value[x][key - attrColOffset];

                    $('<td class="col col-' + self.attributeNameToClassNameFormat(value) + '"></td>').html(
                        self.getRender(
                            attributeName,
                            eleId,
                            eleName,
                            eleValue,
                            eleValueId,
                            conditions,
                            warrantyTypes
                        )
                    ).appendTo(row);

                    for (var i in variantAttributes)
                    {
                        if (variantAttributes[i] == attributeName) {
                            vendorSku += '-' + eleValue;
                            break;
                        }
                    }
                });
                $('.col-vendor-sku input', row).val($('#vital-name'). val() + vendorSku);
                $('.col-name input', row).val($('#vital-name'). val() + vendorSku);
                $('#show_hide_fields').show();
            }
        },


        validateOptions: function (total_combinations, requiredOptionsMsg) {
            /* Validate atleaset one or more options checked or not in attributes*/
            if (total_combinations < 1) {
                alert({
                    content: $.mage.__(requiredOptionsMsg + ' ' + $(
                        '#variantsAttributeLabels').val().replace(/,([^,]*)$/,
                        ' & ' + '$1') + '.'
                    )
                });
                $('#show_hide_fields').hide();
                return false;
            }
            return true;
        },

        setDatePickerToFields: function () {
            /* Set date picker to date columns */
            $('#variantGrid tr').each(function () {
                var row = $(this);
                var fromId = $(".date-from", row).attr('id');
                var toId = $(".date-to", row).attr('id');
                row.dateRange({
                    buttonText: $.mage.__('Select Date'),
                    showOn: "button",
                    from: {
                        id: fromId
                    },
                    to: {
                        id: toId
                    }
                });
            });
        },

        attributeNameToLowerCaseWithSeparator: function (str) {
            if(typeof str == 'object'){
                str = JSON.stringify(str);
            }
            return str.replace(/\s+/g, '_').toLowerCase();
        },
        
        attributeNameToClassNameFormat: function (attributeName) {
            var str = this.attributeNameToLowerCaseWithSeparator(attributeName);
            return str.split('_').join('-');
        },

        getRender: function (
            attributeName,
            $eleId,
            eleName,
            eleValue,
            eleValueId,
            conditions,
            warrantyTypes
        ) {
            var variantAttributes = $('#variantsAttributeCodes').val().split(',');
            var selectFlag = false;
            var eleId = $eleId.toString().replace(/_/g, '-');
            for (var i in variantAttributes)
            {
                if (variantAttributes[i] == attributeName) {
                    selectFlag = true;
                    break;
                }
            }

            var html= '';
            var input_select_start = "<select id='" + eleId + "' name='" + eleName + "' >";
            var input_select_end = "</select>";
            if (selectFlag) {
                html = "<label>" + eleValue + "</label> \n\
                 <input type='hidden' id='" + eleId + "' name='" + eleName + "' value='" + eleValueId + "' />";
            } else if (attributeName == 'condition') {
                html = input_select_start;
                $.each(conditions, function( key, condition ) {
                      html += "<option value='"+condition.value+"'>"+condition.label+"</option>";
                });
                html += input_select_end;
            } else if (attributeName == 'warranty_type') {
                html = input_select_start;
                $.each(warrantyTypes, function( key, warrantyType ) {
                    html += "<option value='"+warrantyType.value+"'>"+warrantyType.label+"</option>";
                });
                html += input_select_end;
            } else if (attributeName == 'special_from_date') {
                html = "<div class='control date-field-container'><input class='date-from' type='text' id='date_" + eleId + "' name='" + eleName + "' /></div>";
            } else if (attributeName == 'special_to_date') {
                html = "<div class='control date-field-container'><input class='date-to' type='text' id='date_" + eleId + "' name='" + eleName + "' /></div>";
            } else if (attributeName == 'action') {
                html = "<button type='button' title='"+$.mage.__('Remove')+"' class='action button secondary remove-row btn'><span>"+$.mage.__('Remove')+"</span></button>";
            } else if ($.inArray(attributeName, ["vendor_sku"]) > -1) {
                html = "<input type='text' class='input-text required-entry vendor-sku' id='" + eleId + "' name='" + eleName + "' />";
            } else if ($.inArray(attributeName, ["qty"]) > -1) {
                html = "<input type='text' class='input-text vendor-qty' id='" + eleId + "' name='" + eleName + "' />";
            } else if ($.inArray(attributeName, ["manufacturer_sku"]) > -1) {
                html = "<input type='text' class='input-text required-entry manufacturer-sku manufacturer_sku' id='" + eleId + "' name='" + eleName + "' />";
            } else if (attributeName == 'price') {
                html = "<input type='text' class='input-text required-entry price-update' id='" + eleId + "' name='" + eleName + "' data-validate=\"{required:true, 'validate-greater-than-zero':true, 'validate-number': true}\"/>";
            } else if (attributeName == 'special_price') {
                html = "<input type='text' class='price-update' id='" + eleId + "' name='" + eleName + "' data-validate=\"{'validate-greater-than-zero':true, 'validate-number': true, 'validate-less-than-price':true}\"/>";
            } else if ($.inArray(attributeName, ["condition_note", "warranty_description"]) > -1) {
                html = "<textarea class='input-text textarea' id='" + eleId + "' name='" + eleName + "'></textarea>";
            } else if (attributeName == 'child_image') {
                html = "<div id='child_images'><input type='file' accept='image/*' name='"+ eleName +"' /></div>";
            } else {
                html = "<input type='text' id='" + eleId + "' name='" + eleName + "' >";
            }
            return html;
        },

        showHideFields : function (str) {
            var hideFields = ['Special Price', 'Special From Date', 'Special To Date'];
            var self= this;
            $.each(hideFields, function ( index, value) {
                var thindex = $('.' + self.attributeNameToClassNameFormat(value)).index();
                if (str) {
                    $("#basicTable th:nth-child(" + (thindex + 1) + ")").hide();
                    $("#basicTable td:nth-child(" + (thindex + 1) + ")").hide();
                } else {
                    $("#basicTable th:nth-child(" + (thindex + 1) + ")").show();
                    $("#basicTable td:nth-child(" + (thindex + 1) + ")").show();
                }
            });
        },

        toggleAttributeOptions : function(resetGrid = true)
        {
            var variantAttributes = [];
            var variantsAttributeLabels = [];
            var usedAttributeIds = {};
            if(resetGrid){
                $('#variantGrid').html('');
                $('#show_hide_fields').hide();
            }
            $(".options-selector").each(function( index ) {
                if($(this).is(":checked")){
                    usedAttributeIds[$(this).val()]= $(this).prop('id');
                    variantAttributes.push($(this).prop('id'));
                    variantsAttributeLabels.push($(this).prop('name'));
                    $('.field.variant-'+$(this).prop('id')+'.variant-container').show();
                }else{
                    $('.field.variant-'+$(this).prop('id')+'.variant-container')
                        .find('.checkbox').prop("checked", false);
                    $('.field.variant-'+$(this).prop('id')+'.variant-container').hide();
                }
            });
            $('.variantsAttributeCodes').val(variantAttributes.toString());
            $('.usedAttributeIds').val(JSON.stringify(usedAttributeIds));
            $('.variantsAttributeLabels').val(variantsAttributeLabels.toString());
        },

        getCartesianArray : function (a) { /* a = array of array */
            var i, j, l, m, a1, o = [];
            if (!a || a.length == 0)
                return a;

            a1 = a.splice(0, 1);
            a = this.getCartesianArray(a);
            for (i = 0, l = a1[0].length; i < l; i++) {
                if (a && a.length)
                    for (j = 0, m = a.length; j < m; j++)
                        o.push([a1[0][i]].concat(a[j]));
                else
                    o.push([a1[0][i]]);
            }
            return o;
        },

        createArr : function () {
        /*retrived configurable attributes code*/
            var variantAttributes = $('#variantsAttributeCodes').val().split(',');
            var totalAttrs = Object.keys(variantAttributes).length;

            var variantLabel = new Array(totalAttrs);
            var variantVal = new Array(totalAttrs);

            for (var i = 0; i < totalAttrs; i++) {

                variantLabel[i] = new Array();
                variantVal[i] = new Array();
                $('input[name="' + variantAttributes[i] + '[]"]:checked').each(function () {
                    variantLabel[i].push($(this).siblings('label').html());
                    variantVal[i].push($(this).val());
                });
            }
            return {'label': this.getCartesianArray(variantLabel), 'value': this.getCartesianArray(variantVal)};
        }
    }
});