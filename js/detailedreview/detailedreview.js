/*jslint browser: true, regexp: true, devel: true */
(function ($) {
    'use strict';

    var ACTIONS = {
        'OPEN': 'open',
        'CLOSE': 'close',
        'HIDE': 'hide',
        'SHOW': 'show',
        'DESTROY': 'destroy',
        'REMOVE': 'remove'
    };

    var DetailedReview = function (config) {
        this.config = {};
        $.extend(this.config, config);
        this.init();
    };


    DetailedReview.prototype.init = function () {
        this.initMagnificPopup();
        this.showAjaxLoader();
        this.hideAjaxLoader();
        this.closeForm();
        this.addReviewByPlaceholder();
        this.addReviewByPlaceholderDR();
        this.reviewDateFilters();
        this.showImageName();
        this.checkHash();
        this.showVersionDR();
        $(document).on("click", this.config.moreImagesLink, {config: this}, this.addImage);
        $(document).on("click", this.config.removeImageLink, {config: this}, this.removeImage);
        $(document).on("click", this.config.reviewDialog, {config: this}, this.showReviewForm);
        $(document).on("submit", this.config.reviewForm, {config: this}, this.submitForm);
        $(document).on("click", this.config.reviewVoteRating, {config: this}, this.voting);
        $(document).on("click", this.config.backButton, {config: this}, this.showReviewList);
        $(document).on("click", this.config.dateFilterLink, {config: this}, this.showFilterList);
        $(document).on("click", this.config.sortsLink, {config: this}, this.showSortsList);
        $(document).on("click", this.config.openedList, {config: this}, this.hideFilterList);
        $(document).on("click", this.config.overallRatingItem, {config: this.config}, this.checkRatingStars);
        $(this.config.prosCheckboxes).change({inverseType: 'cons'}, this.validateProsConsCheckboxes);
        $(this.config.consCheckboxes).change({inverseType: 'pros'}, this.validateProsConsCheckboxes);
        $(document).on("submit", this.config.loginForm, {config: this}, this.submitLoginForm);
    };
    DetailedReview.prototype.voting = function (event) {
        var dr_config = event.data.config,
            that = this;
        if (dr_config.config.isCustomerLoggedIn || dr_config.config.isGuestAllowToVote) {
            var $voteType = $(this).hasClass('helpful-btn')? 1 : 0;
            var $messageType = '';
            $.ajax({
                url: $('.helpful-form').attr('action'),
                data: {
                    is_helpful: $voteType,
                    review_id: $(this).closest('.js-helpful-voting').children('input[name=review_id]').attr('value')
                },
                dataType: 'json',
                success: function (data) {
                    if (data['msg']['type'] == 'success') {
                        if (parseInt($voteType)) {
                            $(that).closest('.rating-wrapper').find('.helpful-qty').html(data['helpful']);
                        } else {
                            $(that).closest('.rating-wrapper').find('.unhelpful-qty').html(data['unhelpful']);
                        }
                        $messageType = 'success';
                    } else {
                        $messageType = 'error';
                    }
                    PNotify.removeAll();
                    new PNotify({
                        text: data['msg']['text'],
                        type: $messageType,
                        icon: false
                    });
                }
                //error: function (data) {
                //}
            });
        } else {
            dr_config.initLoginDialog(ACTIONS.OPEN);
        }
    };

    DetailedReview.prototype.formDisplaying = function (obj, action, options) {
        var reviewFormOptions = {
            zIndex: 500
        };
        if (typeof options !== "undefined") {
            $(reviewFormOptions).extend(options);
        }
        var actionMapping = {
            popup: {
                open: ACTIONS.OPEN,
                close: ACTIONS.CLOSE,
                destroy: ACTIONS.DESTROY
            },
            non_popup: {
                open: ACTIONS.SHOW,
                close: ACTIONS.HIDE,
                destroy: ACTIONS.REMOVE
            }
        };
        if (this.config.isShowPopup) {
            obj.dialog(actionMapping['popup'][action]);
        } else {
            obj[actionMapping['non_popup'][action]]('fade');
        }
        if (typeof options !== 'undefined') {
            options.each(function (element) {
                obj(element);
            });
        }
        if (!this.config.isShowPopup) {
            this.formSwitcher(action);
        }

    };

    DetailedReview.prototype.formSwitcher = function (action) {
        if (action == ACTIONS.OPEN) {
            $(this.config.reviewTop).hide();
            $(this.config.customerReviews).hide();
        }
        if (action == ACTIONS.CLOSE ) {
            $(this.config.reviewTop).show();
            $(this.config.customerReviews).show();
        }
    };

    DetailedReview.prototype.submitForm = function (event) {
        var that = event.data.config;
            if (that.config.isCaptchaEnabled) {
                $.ajax({
                    url: that.config.checkCaptchaUrl,
                    data: this.serialize(),
                    async: false,
                    success: function (data) {
                        if (data == 'invalid') {
                            $(that.config.captchaError).html(that.config.messages.captchaError);
                            grecaptcha.reset();
                            that.hideReviewButtons();
                        } else {
                            $(that.config.captchaError).html('');
                        }
                    },
                    error: function () {
                        $(that.config.captchaError).html(that.config.messages.someError);
                        that.hideReviewButtons();
                        event.preventDefault();
                        return false;
                    }
                });
                if ($(that.config.captchaError).html() !== '') {
                    return false;
                }
            }
            if (dataForm.validator.validate() == true) {
                $(that.config.reviewSubmitButton).attr('disabled', 'disabled');
                if (that.config.isAjaxSubmit) {
                    that.submitFormAjax();
                } else {
                    that.formDisplaying(that.initReviewForm(), ACTIONS.CLOSE);
                    $(that.config.reviewSubmitButton).removeAttr('disabled');
                    window.location.replace(that.config.productPage);
                    return true;
                }
            } else {
                that.hideReviewButtons();
                if (typeof(grecaptcha) !== 'undefined') {
                    grecaptcha.reset();
                    return false;
                }
                event.preventDefault();
                return false;
            }
        event.preventDefault();
        return false;
    };

    DetailedReview.prototype.submitFormAjax = function (e) {
        var that = this;
        var $submitForm = $(that.config.reviewForm);
        $submitForm.ajaxSubmit({
            target: '#upload-img',
            type: 'POST',
            dataType: "json",
            url: $submitForm.attr('action'),
            success: function (response) {
                if (response.success) {
                    that.clearForm($submitForm);
                    that.formDisplaying(that.initReviewForm(), ACTIONS.CLOSE);

                    if (that.config.isSeparatePage) {
                        opener.location.reload();
                        window.close();
                    }

                    new PNotify({
                        text: response.messages.replace(/\[\[/g, '<'),
                        type: 'success'
                    });

                    if (response.html) {
                        var html = response.html.replace(/\[\[/g, '<');
                        $('.reviews-container').html(html);
                        that.initMagnificPopup();
                        if (that.config.isCaptchaEnabled) {
                            if (typeof grecaptcha != 'undefined') {
                                grecaptcha.reset();
                            }
                        }
                    }

                    if (that.config.isSeparatePage) {
                        window.location.replace(that.config.productPage);
                    }

                    if (that.config.autoApproveFlag && drReviewLoader) {
                        drReviewLoader.bindEvent();
                        var tab = $('.review-sorts ul li').get(1);
                        $(tab).trigger('click');
                    }
                } else {
                    new PNotify({
                        text: response.messages.replace(/\[\[/g, '<'),
                        type: response.type,
                        width: "400px"
                    });
                }
            },
            error: function (data) {
            },
            complete: function() {
                $(that.config.reviewSubmitButton).removeAttr('disabled');
            }
        });
    };


    DetailedReview.prototype.clearForm = function($form) {
        $form.clearForm();
        this.clearRatings();
        /* remove extra image uploader into Review Form */
        $('.more-images').not(':first').remove();
        /* remove selected images after Form submit */
        $('.choosed-image-name').remove();
        // 3 is middle size (default value on init)
        $('#slider').val(3);

        $('.dropcontainer > ul li').first().click();
        $('.dropcontainer > ul').removeClass('dropdownvisible').addClass('dropdownhidden');
    };

    DetailedReview.prototype.addReviewByPlaceholder = function () {
        if ($(this.config.reviewPlaceholder).length) { // addByPlaceholder
            if ($('#product_tabs_review_tabbed_contents #review-form').length != 0) {
                new PNotify({
                    text: this.config.messages.easyTabAlert,
                    type: 'error',
                    icon: false
                });
            }
            $(this.config.reviewPlaceholder).html($$(this.config.reviewsBlock).clone(true));
        } else if ($(this.config.reviewEasyTab).length) {
            $(this.config.reviewEasyTab).html($$(this.config.reviewsBlock).clone(true));
            dataForm = new VarienForm(this.config.reviewForm.substring(1));
            var validator = new Validation(this.config.reviewForm.substring(1), {immediate : true});
            validator.validate();
        }
    };

    DetailedReview.prototype.addReviewByPlaceholderDR = function() {
        if ($(this.config.reviewPlaceholder).length < 1 && !$(this.config.reviewEasyTab).length && $(this.config.reviewPlaceholderDR).length) { // addByPlaceholderDR
            $(this.config.reviewPlaceholderDR).html($$(this.config.reviewsBlock).clone(true));
        } else if ($(this.config.reviewEasyTab).length) {
            $(this.config.reviewPlaceholderDR).parent().remove();
        }
    };

    DetailedReview.prototype.openReviewEasyTabs = function() {
        if (!$(this.config.reviewPlaceholder).length && $(this.config.reviewEasyTab)) {
            $('.product-view .product-collateral ul.tabs li').each(function (index, el) {
                var $contents = $('#' + el.id+'_contents');
                if (this.id == 'product_tabs_review_tabbed') {
                    $(this).addClass('active');
                    $contents.show();
                } else {
                    $(this).removeClass('active');
                    $contents.hide();
                }
            });
            if ($('ul.tabs li#product_tabs_review_tabbed a').length) {
                Varien.Tabs.prototype.initTab($('ul.tabs li#product_tabs_review_tabbed a').get(0));
            }
            return true;
        }
        return false;
    };

    DetailedReview.prototype.addImage = function (event) {
        var that = event.data.config;
        if (that.config.currentImageCount < that.config.imageMaxCount) {
            var html = $('<div/>').html('&lt;div class="more-images">&lt;div class="choose-image">&lt;span>' + that.config.messages.chooseFile + '&lt;/span>&lt;input type="file" name="image[]" class="addedInput image_field" value="" />&lt;/div>&lt;a href="#" class="remove-img">&lt;/a>&lt;div class="clearboth">&lt;/div>').text();
            $('#add-file-input-box').append(html);
            that.showImageName();
            that.config.currentImageCount++;
        } else {
            $("#add-more-images").css('display', 'none');
            new PNotify({
                text: that.config.messages.maxUploadNotify,
                type: 'info',
                icon: false
            });
        }
        return false;
    };

    DetailedReview.prototype.removeImage = function (event) {
        var that = event.data.config;
        if (that.config.currentImageCount > 0) {
            $(this).parents('.more-images').remove();
            $("#add-more-images").css('display', 'block');
            if (that.config.currentImageCount == 1) {
                $(that.config.moreImagesLink).trigger("click");
            }
            that.config.currentImageCount--;
        }
        if (that.config.imageMaxCount == 1) {
            var html = $('<div/>').html('&lt;div class="more-images">&lt;div class="choose-image">&lt;span>' + that.config.messages.chooseFile + '&lt;/span>&lt;input type="file" name="image[]" class="addedInput image_field" value="" />&lt;/div>&lt;a href="#" class="remove-img">&lt;/a>&lt;div class="clearboth">&lt;/div>').text();
            $('#add-file-input-box').append(html);
        }
        return false;
    };

    DetailedReview.prototype.initReviewForm = function () {
        var $form = '';
        if (this.config.isShowPopup) {
            $form = this.initReviewDialog();
        } else {
            var that = this;
            $form = $(document).find(".review-dialog-block").addClass('non-popup');
            $('.minimize').bind('click', function () {
                that.formDisplaying($form, ACTIONS.CLOSE);
            });
        }
        return $form;
    };

    DetailedReview.prototype.initReviewDialog = function () {
        return $(".review-dialog-block").dialog({
            width: 575,
            minHeight: 460,
            autoOpen: false,
            show: "fade",
            draggable: false,
            resizable: false,
            modal: true,
            stack: false,
            dialogClass: "review-dialog-modal",
            open: function () {
                $('.ui-widget-overlay').bind('click', function () {
                    $('.review-dialog-block').dialog(ACTIONS.CLOSE);
                })
            }
        });
    };

    DetailedReview.prototype.checkHash = function() {
        if(window.location.hash) {
            var hash = window.location.hash.substring(1);
            if (hash == 'review-form') {
                var event = {data:
                {config: this}
                };
                var isReviewEasyTabs = this.openReviewEasyTabs();
                this.showReviewForm(event);
                if (isReviewEasyTabs) {
                    $('html, body').animate({
                        scrollTop: $('#feedback').offset().top
                    }, 1000);
                }
            }
        }
        return false;
    };

    DetailedReview.prototype.showReviewForm = function (event) {
        var that = event.data.config;
        if (that.config.onlyVerifiedBuyer) {
            that.showOnlyVerifiedBuyer();
        } else {
            if(that.config.writeReviewOnce) {
                that.allowWriteReviewOnce();
            } else {
                $('form [name=referer]').val($(this).prev().val());
                $('form [name=success_url]').val($(this).prev().prev().val());
                that.checkUserSettings();
            }
        }
    };
    DetailedReview.prototype.checkUserSettings = function () {
        var that = this;
        if (that.config.isCustomerLoggedIn || that.config.isGuestAllowToWrite) {
            if (!that.config.isSeparatePage) {
                that.getReviewForm();
            } else {
                window.open(that.config.separatePage, '_blank');
            }
        } else {
            that.initLoginDialog();
        }
    };

    DetailedReview.prototype.showOnlyVerifiedBuyer = function() {
        var that = this;
        var productId = that.config.productId;
        $.ajax({
            url: that.config.productIdsAllowReviewUrl,
            success: function (data) {
                var jsonObj = JSON.parse(data);
                if (jsonObj.length) {
                    if ($.inArray(productId, jsonObj) < 0) {
                        new PNotify({
                            text: that.config.messages.onlyVerifiedBuyer,
                            icon: false
                        });
                    } else {
                        if(that.config.writeReviewOnce) {
                            that.allowWriteReviewOnce();
                        } else {
                            that.checkUserSettings();
                        }
                    }
                } else {
                    new PNotify({
                        text: that.config.messages.onlyVerifiedBuyer,
                        icon: false
                    });
                }
            },
            error: function () {
            }
        });
    };

    DetailedReview.prototype.allowWriteReviewOnce = function() {
        var that =  this;
        var values = {};
        values['product_id'] = that.config.productId;
        $.ajax({
            url: that.config.checkWriteReviewOnce,
            data: values,
            success: function (data) {
                var jsonObj = JSON.parse(data);
                if (jsonObj.length) {
                    new PNotify({
                        text: that.config.messages.alreadyReviewed,
                        icon: false
                    });
                } else {
                    that.checkUserSettings();
                }
            },
            error: function () {
            }
        });
    };

    DetailedReview.prototype.getReviewForm = function(that) {
        $('form [name=referer]').val($(this).prev().val());
        $('form [name=success_url]').val($(this).prev().prev().val());
        this.formDisplaying(this.initReviewForm(), ACTIONS.OPEN);
    };

    DetailedReview.prototype.closeForm = function (e) {
        $(document).keyup(function (e) {
            if (e.which == 27) {
                $("#jquery-lightbox").fadeOut("slow");
                $("#jquery-overlay").fadeOut("slow");
            }
        });
    };

    DetailedReview.prototype.initMagnificPopup = function () {
        $('.image-popup').magnificPopup(this.config.magnificConfig);
    };

    DetailedReview.prototype.initLoginDialog = function () {
        var $loginDialog = $('.login-dialog-block').dialog({
            width: 760,
            autoOpen: false,
            show: 'fade',
            draggable: false,
            resizable: false,
            modal: true,
            stack: false,
            open: function () {
                $('.ui-widget-overlay').bind('click', function () {
                    $('.login-dialog-block').dialog(ACTIONS.CLOSE);
                })
            }
        });
        return $loginDialog.dialog(ACTIONS.OPEN);
    };

    DetailedReview.prototype.submitLoginForm = function (event) {
        var that = event.data.config;
        var dataLoginForm = new VarienForm('login-form', true);
            if (dataLoginForm.validator && dataLoginForm.validator.validate()) {
                $.ajax({
                    url: that.config.checkLoginUrl,
                    data: $(dataLoginForm.form).serialize(),
                    success: function (data) {
                        if (data === '1') {
                            dataLoginForm.form.submit();
                        } else {
                            $('.account-login p.error-message').html(data);
                        }
                    },
                    error: function () {
                    }
                });
            }
            return false;
    };

    DetailedReview.prototype.submitFormValidate = function () {
        var that = this;
        var dataRegForm = new VarienForm('form-validate', true);
        $('#form-validate').submit(function (event) { //submitFormValidate
            if (dataRegForm.validator && dataRegForm.validator.validate()) {
                var $inputs = $('#form-validate :input');
                var values = {};
                $inputs.each(function () {
                    values[this.name] = $(this).val();
                });
                $.ajax({
                    url: that.config.checkRegistrateUrl,
                    data: values,
                    success: function (data) {
                        if (data === '1') {
                            var redirectUrl = $(dataRegForm.form).find('[name="success_url"]').val();
                            if (redirectUrl == window.location.href) {
                                window.location.reload();
                            } else {
                                window.location.href = redirectUrl;
                            }
                        } else {
                            var jsonObj = JSON.parse(data);
                            var $messageType = '';
                            if (typeof(jsonObj.success) !== 'undefined') {
                                $messageType = 'success';
                            } else if (typeof(jsonObj.error) !== 'undefined') {
                                $messageType = 'error';
                            }
                            var message = $('.account-create p.' + $messageType + '-message').html(jsonObj[$messageType]);
                            $('html, body').animate({
                                scrollTop: message.offset().top + 'px'
                            }, 'fast');
                        }
                    }
                });
            }
            event.preventDefault();
            return false;
        });
    };

    DetailedReview.prototype.showAjaxLoader = function () {
        $(document).ajaxStart(function () { //showAjaxLoader
            $("#imageLoading").show();
        });
    };

    DetailedReview.prototype.hideAjaxLoader = function () {
        $(document).ajaxStop(function () {
            $("#imageLoading").hide();
        });
    };

    DetailedReview.prototype.hideReviewButtons = function () {
        $(this.config.reviewFormButton).get(0).style.display = '';
        document.getElementById(this.config.reviewSpinner).style.display = 'none';
    };
    DetailedReview.prototype.reviewDateFilters = function () {
        var selected = $(this.config.dateFilter).find('.selected').text();
        var selectedSorts = $(this.config.reviewSorts).find('.selected a').text();
        if(selected) {
            $(this.config.dateFilterSpan).text(selected);
        }
        if(selectedSorts) {
            $(this.config.sortsSpan).text(selectedSorts);
        }
    };
    DetailedReview.prototype.showReviewList = function (event) {
        var that = event.data.config;
        if (that.config.isSeparatePage) {
            window.close();
        }
        $(that.config.reviewTop).show();
        $(that.config.customerReviews).show();
        if(that.config.isShowPopup) {
            $('.review-dialog-block').dialog(ACTIONS.CLOSE);
        } else {
            $(".review-dialog-block").hide();
        }
    };
    DetailedReview.prototype.showFilterList = function (event) {
        var that = event.data.config;
        $(that.config.dateFilterLink).closest('ul').css({"height" : "auto", "z-index" : "1000"});
        $(that.config.dateFilterLink).find('.dateFilter').addClass('openedList');
    };
    DetailedReview.prototype.showSortsList = function (event) {
        var that = event.data.config;
        if ($(that.config.sortsLink).hasClass('openedList')) {
            return;
        }
        $(that.config.sortsLink).closest('ul').css('height','auto');
        $(that.config.sortsLink).closest('ul').children('li').css('width','100%');
        $(that.config.sortsLink).addClass('openedList');
    };
    DetailedReview.prototype.hideFilterList = function (event) {
        var that = event.data.config;
        var $self = $(this);
        event.stopPropagation();
        if ($self.closest('.select-review-sorts').length) {
            $(that.config.sortsLink).closest('ul').css('height', '40px');
            $(that.config.sortsLink).removeClass('openedList');
        } else {
            $(that.config.dateFilterLink).closest('ul').css({"height" : "40px", "z-index" : "0"});
            $(that.config.dateFilterLink).find('.dateFilter').removeClass('openedList');
        }
    };
    DetailedReview.prototype.showImageName = function () {
        $('.upload-image :input').last().change(function () {
            var filename = $(this).val();
            var lastIndex = filename.lastIndexOf("\\");
            if (lastIndex >= 0) {
                filename = filename.substring(lastIndex + 1);
            }
            if (filename.length >= 25) {
                var fileNameLength = filename.length;
                filename = filename.substr(0, 12) + '...' + filename.substr(fileNameLength - 8, fileNameLength)
            }

            if ($(this).hasClass('showed')) {
                $(this).parent().next().remove();
            } else {
                $(this).addClass('showed');
            }
            var html = $('<div/>').html('&lt;div class="choosed-image-name">&lt;span>' + filename + '</span></div>').text();
            $(this).parent().after(html);
        });
    };
    DetailedReview.prototype.validateProsConsCheckboxes = function (event) {
        var inverseType = event.data.inverseType;
        $('.' + inverseType).find('input[data-property=' + $(this).data('property') + ']').prop('disabled', this.checked);
    };

    DetailedReview.prototype.showVersionDR = function () {
      if (this.getQueryVariable('versionDR')) {
          console.log('version DR ' + this.config.versionDR);
      }
    };

    DetailedReview.prototype.getQueryVariable = function (variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return null;
    };

    DetailedReview.prototype.checkRatingStars = function (event) {
        var tthis = this;
        var $li = $(tthis).parent().children('li');
        $li.removeClass('active');
        $li.find('input.radio').attr("checked", false);
        $li.find(event.data.config.separateRatingStar).css("background", 'url(' + event.data.config.unActiveImageAverage + ') no-repeat');
        $li.each(function(){
            $(this).addClass('active');
            $(this).find(event.data.config.separateRatingStar).css("background", 'url(' + event.data.config.activeImageAverage + ') no-repeat');
            if ( tthis == this ) return false;
        });
        $(this).find('input.radio').attr('checked', true);
    };

    DetailedReview.prototype.clearRatings = function() {
        $('.overall-rating-inline li').removeClass('active');
        $('.overall-rating-inline li input.radio').attr("checked", false);
    };

    $.fn.detailedReview = function (options) {
        new DetailedReview(options)
    };

}(DRjQuery));


