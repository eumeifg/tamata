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
var config = {
    map: {
        '*': {
             rbCommonMethods:    'Magedelight_Catalog/js/rb-common-methods',
             'multiple-file-uploader': 'Magedelight_Catalog/js/jquery.fileupload',
             'multiple-file-widget': 'Magedelight_Catalog/js/jquery.ui.widget',
             'multiple-file-validate': 'Magedelight_Catalog/js/jquery.fileupload-validate',
             'multiple-file-processor': 'Magedelight_Catalog/js/jquery.fileupload-process',
	     rbProductGallery:   'Magedelight_Catalog/js/product-gallery',
             baseImage:          'Magedelight_Catalog/js/base-image-uploader',
             'rbpopup' : 'Magedelight_Theme/js/fancybox',
             'rbIfram' : 'Magedelight_Catalog/js/iframsolver',
	     'chosen':'Magedelight_Catalog/js/bulkimport/event.simulate',
             'custom':'Magedelight_Catalog/js/bulkimport/custom',
	     'quickProductSearch':'Magedelight_Catalog/js/sellexisting',
        }
    },
    shim: {
        'rbpopup' : ['jquery'],
        'tablesorter': {
            deps: ['jquery']
        },
        'rbCommonMethods': {
            deps: ['jquery']
        },
        'multiple-file-uploader': {
            deps: ['jquery']
        },
	'rbProductGallery': {
            deps: ['jquery']
        },
        'baseImage': {
            deps: ['jquery']
        },
	'chosen': {
            deps: ['jquery']
        },
        'custom': {
            deps: ['jquery']
        }
    }
};
