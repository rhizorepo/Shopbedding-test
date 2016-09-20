/*jslint browser: true, regexp: true, devel: true */
(function($){
    'use strict';
    $(document).ready(function(){
        $('.image-popup').magnificPopup({
            type:'image',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0]
            }
        });
    });
    $(document).keyup(function(e){
        if(e.which == 27){
            jQuery("#jquery-lightbox").fadeOut("slow");
            jQuery("#jquery-overlay").fadeOut("slow");
        }
    });
})(DRjQuery);
