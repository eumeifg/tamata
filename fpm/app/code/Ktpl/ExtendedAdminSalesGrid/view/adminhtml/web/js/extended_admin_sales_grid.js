require([
    'jquery'
], function ($) {
    'use strict';
    $( document ).ready(function() {

        setInterval(function(){ 
            $("table.data-grid.data-grid-draggable span").each(function () {
                if($(this).text() === "Vendor Order Status"){                  
                     $(this).parent().css({"display": "none"});
                }
            });
        }, 5000);
   });  

      $( document ).on( "change", ":checkbox", function () {

         if(this.checked) {
            
              $("table.data-grid.data-grid-draggable span").each(function () {
                if($(this).text() === "Vendor Order Status"){
                     $(this).parent().css({"display": "none"});
                    }
                });
            }
        
      });
       

});