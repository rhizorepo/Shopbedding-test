(function ($) {
    'use strict';
    $.fn.drResponse = function (options) {
        var Response = function (options) {
            $.extend(true, this.options, options);
            this.init();
        };

        Response.prototype.options = {
            'defaultPoints': {
                width: [288, 547, 785, 961, 1025]
            },
            'container': '.reviews-wrapper',
            'prefix': 'dr-width',
            'lastWidth': '0px'
        };

        Response.prototype.init = function() {
            //this.addClassWrapper();
            this.checkForChanges();
        };

        Response.prototype.addClassWrapper = function() {
            var className = this.options.prefix + '-' + this.options.defaultPoints.width.first() + '-more';
            var containerWidth = $(this.options.container).width();
            var index;
            for (index = this.options.defaultPoints.width.length - 1; index >= 0 ; --index) {
               if (containerWidth >= this.options.defaultPoints.width[index]) {
                   className = this.options.prefix + '-' + this.options.defaultPoints.width[index] + '-more';
                   break;
               }
            }
            $(this.options.container).addClass(className);
            $('#responseLoading').css('display', 'none');
            $('#feedback').css('display', 'block');
        };

        Response.prototype.removeClassWrapper = function() {
            $('#feedback').css('display', 'none');
            var self = this;
            $(this.options.container).each(function(i, el) {
                var classes = el.className.split(" ").filter(function(c) {
                    return c.lastIndexOf(self.options.prefix, 0) !== 0;
                });
                el.className = $.trim(classes.join(" "));
            });
        };

        Response.prototype.changeClassWrapper = function() {
            if ($(this.options.container).css('width') != '0px') {
                this.removeClassWrapper();
                this.addClassWrapper();
            }
        };

        Response.prototype.checkForChanges = function(obj) {
            var self = this;

            this.updateCountdown = function() {
                if ($(self.options.container).css('width') != self.options.lastWidth)
                {
                    self.changeClassWrapper();
                    self.options.lastWidth = $(self.options.container).css('width');
                }
            };
            self.interval = setInterval(this.updateCountdown, 500);
        };

        Response.prototype.clearInterval = function()
        {
            clearInterval(this.interval);
        };

        return new Response(options);
    };

}(DRjQuery));
