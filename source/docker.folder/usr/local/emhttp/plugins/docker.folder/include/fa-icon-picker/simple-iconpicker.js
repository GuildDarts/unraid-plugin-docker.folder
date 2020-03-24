/*!
 * Simple Font Awesome Icon Picker
 * http://howlthemes.com
 *
 * Originally written by (c) 2016-17 Aumkar Thakur
 * Special Thanks To Deepak Kamat
 * Licensed under the MIT License
 * https://github.com/aumkarthakur/simple-fontawesome-iconpicker/blob/master/LICENSE
 *
 */

jQuery(document).ready(function(){
    jQuery('body').prepend('<div class="howl-iconpicker-outer"><div class="howl-iconpicker-middle"><div class="howl-iconpicker"><div class="howl-closebtn"></div><input type="text" class="srchicons" placeholder="eg:google" /> <div class="iconsholder"></div></div><div class="howl-iconpicker-close">Close</div></div></div>'); // Appending Iconpicker box below input box
    
    // remove search for now (broke)
    jQuery('.howl-iconpicker').find('input').remove()
    
    // All FontAwesome Icons Class
    var fontawesomeicon = 'globe play stop refresh arrow-down',
        fontawesomeiconArray = fontawesomeicon.split(' '); // creating array

    // This loop will add icons inside BOX
    for (var i = 0; i < fontawesomeiconArray.length; i++) {
        jQuery(".howl-iconpicker .iconsholder").append('<p class="geticonval"><i class="fa fa-' + fontawesomeiconArray[i] + '"></i>'+fontawesomeiconArray[i]+'</p>');
    }

    //Search Box Code Starts
    jQuery(".howl-iconpicker .srchicons").keyup(function() {

        var filter = jQuery(this).val(),
            count = 0;

        jQuery(".howl-iconpicker .geticonval").each(function() {

            if (jQuery(this).text().search(new RegExp(filter, "i")) < 0) {
                jQuery(this).fadeOut();
            } else {
                jQuery(this).show();
                count++;
            }
        });
    }); //Search box code Ends

    //Close button code
    jQuery('.howl-iconpicker-close').click(function(){
        jQuery('.howl-iconpicker-outer').css('display', 'none');
    });

    jQuery(".howl-iconpicker").on("click", function(e){

        e.stopPropagation();
    });

    jQuery('.howl-iconpicker-outer').on('click', function(){
        jQuery('.howl-iconpicker-outer').css('display', 'none');
    });
});

// This function is Heart of this plugin LOL sorry :P
(function(jQuery) {

    jQuery.fn.iconpicker = function(selector) {

        // if user focus on inputbox SHOW iconpicker box
        jQuery(this).focusin(function() {

            jQuery('.howl-iconpicker-outer').css('display', 'table');
            jQuery('.howl-iconpicker .geticonval').removeClass('selectedicon');
            whichInputClass = jQuery(this).attr('class');
            whichInputId = jQuery(this).attr('id');
            jQuery(".howl-iconpicker .geticonval").on('click', function() {
                var getIconId = jQuery(this).text();
                jQuery('.howl-iconpicker .geticonval').removeClass('selectedicon');
                jQuery(this).addClass('selectedicon');
                if ( jQuery(selector).attr('class') == whichInputClass && jQuery(selector).attr('id') == whichInputId) {
                    jQuery(selector).val(getIconId).change();
                }
                jQuery('.howl-iconpicker-outer').css('display', 'none');
            });

        });


    }

}(jQuery));
