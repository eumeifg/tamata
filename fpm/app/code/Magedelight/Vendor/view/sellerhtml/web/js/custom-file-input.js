/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

require([
    "jquery",
    "jquery/ui"
], function($){
'use strict';
    $(document).ready(function() {
        $('input[type="file"]').each(function(index,value ) {
            var label	 = $(this).next(),
                    labelVal = $(label).html();

            $(this).on( 'change', function( e ) {
                    var fileName = '';
                    if( $(this).files && $(this).files.length > 1 )
                            fileName = ( $(this).attr( 'data-multiple-caption' ) || '' ).replace( '{count}', $(this).files.length );
                    else
                            fileName = e.target.value.split( '\\' ).pop();

                    if( fileName )
                            $('span',label).html(fileName);
                    else
                            label.innerHTML = labelVal;
            });

            /* Firefox bug fix */
            $(this).on( 'focus', function(){ $(this).addClass( 'has-focus' ); });
            $(this).on( 'blur', function(){ $(this).removeClass( 'has-focus' ); });
        });
    });
});