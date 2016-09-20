/*jslint browser: true, regexp: true, devel: true */
(function ($) {
    'use strict';
    $(document).on('click', 'div.social-share a', function () {
        var url = null,
            type = $(this).data('social-type'),
            long_url = $(this).data('url'),
            action_url = $(this).data('action');

        if (type === 'twitter') {
            var text = $(this).data('text'),
                via = $(this).data('via');
            url = 'http://twitter.com/share?count=none&text=' + encodeURIComponent(text || '') + '&via=' + encodeURIComponent(via || '') + '&url=';
        }
        if (type === 'facebook') {
            var image = $(this).data('img'),
                desc = $(this).data('descr'),
                title = $(this).data('title');
            url = 'http://www.facebook.com/sharer.php?s=100&p[title]=' + (title || '') + '&p[summary]=' + (desc || '') + '&p[images][0]=' + (image || '') + '&p[url]=';
        }
        $.ajax({
            type: 'POST',
            //long_url this is parameter which is transmitted to the action
            url: action_url,
            data: {url: long_url},
            async: false,
            dataType: 'json',
            success: function(response) {
                if (url && response.status_code === 200) {
                    window.open(url + encodeURIComponent(response.data.url), 'sharer', 'toolbar=0,status=0,width=700,height=400');
                }
                if (response && response.status_code !== 200) {
                    new PNotify({
                        text: response.message
                    });
                }
            }
        });
        return false;
    });

}(DRjQuery));
