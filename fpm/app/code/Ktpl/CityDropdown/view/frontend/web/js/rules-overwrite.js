define([
    'jquery',
    'moment',
    'Magento_Ui/js/lib/validation/utils',
], function ($, moment, utils) {
    'use strict';
    return function (validator) {
        validator.addRule(
            'validate-iraq-number',
            function (value) {
                return utils.isEmpty(value) || value.length <= 11 ?
                    value.match(/^((075|077|078|079)[0-9]{8})$/) : value.match(/^((00964|\+964)(75|77|78|79)[0-9]{8})$/);
            },
            $.mage.__('Please specify a valid phone number. e.g. Telephone: 075/077/078/079xxxxxxxx Mobile: +964/00964 | 75/77/78/79 | xxxxxxxx')
        );
        return validator;
    };
});
