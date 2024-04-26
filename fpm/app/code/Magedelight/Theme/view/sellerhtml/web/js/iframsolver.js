require(['jquery'], function ($) {
    $( document ).ready(function() {    
        if(window.top != window.self){
            if($("#login-form").length){
                window.parent.location = document.referrer;
            } 
            if($("#notice-cookie-block").length){
                $("#notice-cookie-block").remove();
            }               
        }
    });
    $( window ).load(function() {
        if(window.top != window.self){
            if($(".zopim").length){
                $(".zopim").remove();
            }
        }
    });
});