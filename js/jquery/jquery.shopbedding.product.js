(function ($) {
    var isIE6 = $.browser.msie && $.browser.version < 7;
    var currentMainImageSrc;

    function setActiveDepths(currentDepthSelected) {
        $(".product-depth-option").each(function () {
            var depthAvailable = false;
            var depthArray = [];

            $('select option', this).each(function (i) {
                depthArray[i] = $(this).val();
            });
            //   $('.depthChoicesList .text-choice-wrapper', this).removeClass('valid');
            for (j = 0; j < depthArray.length; j++) {
                $('div.depthChoice[rel=' + depthArray[j] + ']', this).parent().addClass('valid');
                if (depthArray[j] == currentDepthSelected) {
                    depthAvailable = true;
                }

            }

            if (!depthAvailable) {
                $(".text-choice.active:first", this).removeClass('active').parent().removeClass('active');
            } else {
                $(".text-choice.active:first", this).click();
            }


        });
    }

    window.setActiveDepths = setActiveDepths;

    function setActiveDropLengths(currentDropSelected) {
        $(".product-drop-option").each(function () {
            var dropAvailable = false;
            var dropArray = [];
            $('select option', this).each(function (i) {
                dropArray[i] = $(this).val();
            });
            //    $('.dropLengthChoicesList .text-choice-wrapper', this).removeClass('valid');
            for (j = 0; j < dropArray.length; j++) {
                $('div.dropLengthChoice[rel=' + dropArray[j] + ']', this).parent().addClass('valid');
                if (dropArray[j] == currentDropSelected) {
                    dropAvailable = true;
                }

            }


            if (!dropAvailable || $(".text-choice.active", this).length == 0) {
                //$('#quicklook-sizeChoices span#selectedSize').html('Select a Size');
                //$("#product-dropLengthChoices .text-choice.active:first").removeClass('active').parent().removeClass('active');
                if (lengthTitle) {
                    $(".text-choice.active:first", this).click();
                } else {
                    $(".text-choice:visible:first", this).click();
                }


            } else {
                if ($(".text-choice.active:first", this).is(':visible')) {
                    $(".text-choice.active:first", this).click();
                } else {
                    $(".text-choice:first", this).click();
                }
            }
        });
    }

    window.setActiveDropLengths = setActiveDropLengths;

    //Pre-select size and quantity attributes
    function initProductDetail() {
        $(".product-option.quantity input").each(function (k, v) {
            if ($(this).val().length == 0) {
                $(this).val("1");
            }
        });

        $(".product-size-options").each(function () {
            $(".text-choice:first", this).click();
        });
    }

    window.initProductDetail = initProductDetail;

    function initMagicZoom() {
        if ($('#magic-zoom-trigger').length > 0) {
            $('#magic-zoom-trigger').unbind();
            $(".jqZoomWindow, .jqZoomPup, .jqzoom").remove();

            var options = {
                zoomWidth: 385,
                zoomHeight: 330,
                xOffset: 33,
                yOffset: 10,
                position: "right"
            };
            $('#magic-zoom-trigger').jqzoom(options);
        }
    }

    window.initMagicZoom = initMagicZoom;

    $(function () {

        //Text attribute hover state
        $(".product-option .text-choice").hover(
            function () {
                $(this).addClass("hover").parent().addClass("hover");
            },
            function () {
                $(this).removeClass("hover").parent().removeClass("hover");
            }
        );

        //Handles size option click event
        $(".product-size-options .text-choice").click(function () {

            $(".add-to-cart .review-selection").html('');
            var optionsParent = $(this).parents('.product-options');
            var parent = $(this).parents('.product-size-options');
            $('.active', parent).removeClass('active');
            $(this).addClass('active').parent().addClass('active');
            var whichSizeOption = $(this).attr('rel');//.replace("prodImg_attribute143_", "");

            $('select', parent).val(whichSizeOption);
            $('select', parent).trigger('change');

            //Re-trigger depth choice
            $(".product-depth-option", optionsParent).each(function () {
                setActiveDepths($('select', this).val());
            });

            //Re-trigger drop length
            var hasDropLengthOpt = false;
            $(".product-drop-option", optionsParent).each(function () {

                setActiveDropLengths($('select', this).val());
                hasDropLengthOpt = true;
            });

            //If drop length doesn't exist, re-trigger color
            if (!hasDropLengthOpt) {
                $(".color-swatches a.selected:first", optionsParent).click();
            }

            //Add by Gorilla
            if ($(".color-swatches a").length == 0 && $(".product-depth-option .depthChoicesList").length == 0) {
                setButton(parent, whichSizeOption);
            }

            var sizeChoiceActive = this;
            var id='';
            var colorArray = $("[data-size="+this.dataset.child+"]");

            for (var o = 0; o < colorArray.length; o++) {
                if(this.dataset.child == colorArray[o].dataset.size){
                    var id = colorArray[o].dataset.id;
                }
            }

            var Length = getLength();

    if(Length) {
        for (var o = 0; o < colorArray.length; o++) {
            if (Length.dataset.child == colorArray[o].dataset.child) {
                var id = colorArray[o].dataset.id;
            }
        }
    }

            if(colorArray.length < 1){
                if (typeof sizeChoiceActive == 'undefined') {
                    sizeChoiceActive = '00';
                }

                if (Length == null) {
                    Length = '00';
                }
               var selectColor = getColor();
                if (selectColor == null) {
                    sColor = '00';
                }else{
                    sColor= selectColor.dataset.child;
                }
                currentConfig = spConfig.config;
                var combination = sizeChoiceActive + '-' + Length + '-' + sColor;
                for(var key in currentConfig.childProducts) {
                    if(combination ==currentConfig.childProducts[key].combination){
                        var id = colorArray[o].dataset.id;
                        document.getElementById('product_id_gm').value = key
                    }
                }

            }
            run(id,this.dataset.key);
        });


        $(".notify-me").bind('click', function () {

            productAlertUrl = productAlertUrl_firsthalf + productAlertUrl_currentproductid + productAlertUrl_secondhalf;
            // alert(productAlertUrl);

            $.get(
                productAlertUrl,
                function (data) {
                    alert(data);
                }
            );

        });


        $(".color-swatches a").click(function () {
            var parent = $(this).parents('.product-option');

            $(".color-swatches a", parent).removeClass("selected");
            $(this).addClass("selected");
            currentMainImageSrc = $(this).attr('rel');
            var whichColorOption = $(this).children('img').attr('rel');
            var offset = $(this).offset();
            if(this.dataset.atr) {
                document.getElementById('attribute' + this.dataset.atr).value = this.dataset.child;
            }
            if(this.dataset.id)
            {
                if(document.getElementById('product_id_gm')){
                    document.getElementById('product_id_gm').value = this.dataset.id;
                }

            }

              setButton(parent, whichColorOption);

            run(this.dataset.key,this.dataset.id);
            selectedColor = this;
        });

        //Edit by Gorilla
        function setButton(parent, option) {


            var optionsParent;
            if ($("#byos-block").length == 0) {
                optionsParent = parent.parents('.product-main-info');
            } else {
                optionsParent = parent.parents('.byos-product');
            }

            //Check if product exists
            if ($('select option[value=' + option + ']', parent).length == 0) {
                //  $(".add-to-cart p:not(.no-hide), .add-to-cart button, .quantity button,.quantity p:not(.no-hide)", optionsParent).hide();
                $(".add-to-cart .not-available, .quantity .not-available", optionsParent).show();

            } else {
                $('select', parent).val(option).trigger("change");


                var currentConfig;
                if ($("#byos-block").length == 0) {

                    selectedProductId = $("#product_addtocart_form input[name=product]:first").val();


                    currentConfig = spConfig.config;
                } else {

                    selectedProductId = $(".hidden-selected-product", optionsParent).val();

                    currentConfig = spConfig[$(".hidden-spconfig-index", optionsParent).val()].config;
                }



                var currentProduct = currentConfig.childProducts[selectedProductId];
                if(typeof currentProduct === 'undefined'){
                    for(var key in currentConfig.childProducts) {
                        selectedProductId = key;
                        continue;
                    }
                }

                var availability = currentConfig.childProducts[selectedProductId].availability;

                //    $(".add-to-cart p:not(.no-hide), .add-to-cart button, .quantity button,.quantity p:not(.no-hide)", optionsParent).hide();
                if (availability == '0') {
                    //      $(".notify-me", optionsParent).attr("href", currentConfig.childProducts[selectedProductId].notifyLink);
                    productAlertUrl_currentproductid = selectedProductId;
                    $(".add-to-cart .out-of-stock, .quantity .out-of-stock", optionsParent).show();
                } else {
                    $(".add-to-cart .in-stock, .add-to-cart button, .quantity button.add-to-set", optionsParent).show();
                }

                if ($("#byos-block").length == 0) {
                    updateReviewSelectionText(parent.parents('.product-options'));
                } else {

                    if (currentProduct) {
                        if (currentProduct.lightboxImageUrl) {
                            $('.image-zoom-src', optionsParent).val(currentProduct.lightboxImageUrl);
                        }
                    }
                }


            }
            //Modified by CW starts
            /*	$('.color-swatches > a > img', parent).each(function(){
             if($('select option[value=' + $(this).attr('rel') + ']', parent).length == 0){
             $(this).parent('a').hide();
             } else {
             $(this).parent('a').show();
             }
             })
             */
            updateSwatch(optionsParent);
            //Modified by CW ends
        }

        //
        //Added by CW
        function updateSwatch(parentContainer) {
            if ($(".byos-product").length > 0) {
                currentSize = $('.sizeChoice.active', parentContainer).attr('rel');
                if (typeof currentSize === 'undefined') {
                    currentSize = '00';
                }
                currentDropLength = $('.dropLengthChoice.active', parentContainer).attr('rel');
                if (typeof currentDropLength === 'undefined') {
                    currentDropLength = '00';
                }
                $(".color-swatches a>img", parentContainer).each(function () {
                    swatch = currentSize + '-' + currentDropLength + '-' + $(this).attr('rel');
                    allProducts = spConfig[$(parentContainer).attr('id').replace("byos-product-", "")].config.childProducts;
                    for (var key in allProducts) {
                        if (allProducts.hasOwnProperty(key)) {
                            if (allProducts[key]["combination"] == swatch) {
                                if (allProducts[key]["availability"] == 0) {
                                    $(this).parent('a').fadeTo("slow", 0.5).css('border', '1px dashed #000');
                                } else {
                                    $(this).parent('a').fadeTo("slow", 1).css('border', '1px solid #ccc');
                                }
                            }
                        }
                    }
                });
                if (!$(".color-swatches a.selected > img", parentContainer).is(':visible')) {
                    $(".color-swatches a > img:visible:first", parentContainer).parent('a').click();
                }
            } else {
                currentSize = $('.sizeChoice.active').attr('rel');
                if (typeof currentSize === 'undefined') {
                    currentSize = '00';
                }
                currentDropLength = $('.dropLengthChoice.active').attr('rel');
                if (typeof currentDropLength === 'undefined') {
                    currentDropLength = '00';

                }
                $(".color-swatches a>img").each(function () {
                    swatch = currentSize + '-' + currentDropLength + '-' + $(this).attr('rel');
                    allProducts = spConfig.config.childProducts;
                    for (var key in allProducts) {
                        if (allProducts.hasOwnProperty(key)) {
                            if (allProducts[key]["combination"] == swatch) {
                                if (allProducts[key]["availability"] == 0) {
                                    $(this).parent('a').fadeTo("slow", 0.5).css('border', '1px dashed #000');
                                } else {
                                    $(this).parent('a').fadeTo("slow", 1).css('border', '1px solid #ccc');
                                }
                            }
                        }
                    }
                });
                if (!$(".color-swatches a.selected > img").is(':visible')) {
                    $(".color-swatches a > img:visible:first").parent('a').click();
                }
            }
        }

        // byos product image zoom popup
        $(".byos .byos-product .byos-product-image").click(function () {
            window.open($(".image-zoom-src", $(this).parent()).val(), "Window1", "menubar=no,width=720,height=720,toolbar=no");
            return false;
        });

        function updateReviewSelectionText(optionContainer) {
            var pDetails = "";
            $("option:selected", optionContainer).each(function () {
                pDetails += $(this).text() + "/";
            });
            pDetails = pDetails.slice(0, -1);

            $(".add-to-cart .review-selection").html(pDetails).fadeIn();
        }

        $(".color-swatches a").hover(
            function () {
                var parent = $(".byos-product").length == 0 ? $(this).parents(".product-essential") : $(this).parents('.byos-product');
                currentMainImageSrc = $(".main-product-image img", parent).attr('src');
                $(".main-product-image img", parent).attr('src', $(this).attr('rel'));
                $(".color-title", parent).html($("img", this).attr("title"));
            },
            function () {
                var parent = $(".byos-product").length == 0 ? $(this).parents(".product-essential") : $(this).parents('.byos-product');
                $(".main-product-image img", parent).attr('src', currentMainImageSrc);
                $(".color-title", parent).html('');
            }
        );

        $(".product-depth-option .text-choice").click(function () {

            var parent = $(this).parents('.product-depth-option');
            $('.active', parent).removeClass('active');
            $(this).addClass('active').parent().addClass('active');
            var whichDepthOption = $(this).attr('rel');
            $('select', parent).val(whichDepthOption).trigger("change");

            var selectedProductId;
            var currentConfig;
            var optionsParent;
            if ($("#byos-block").length == 0) {
                optionsParent = $(this).parents('.product-main-info');
                selectedProductId = $("#product_addtocart_form input[name=product]:first").val();
                currentConfig = spConfig.config;
            } else {
                optionsParent = $(this).parents('.byos-product');
                selectedProductId = $(".hidden-selected-product", optionsParent).val();
                currentConfig = spConfig[$(".hidden-spconfig-index", optionsParent).val()].config;
            }

            var currentProduct = currentConfig.childProducts[selectedProductId];
            if(typeof currentProduct === 'undefined'){
                for(var key in currentConfig.childProducts) {
                    selectedProductId = key;
                    continue;
                }
            }

            var availability = currentConfig.childProducts[selectedProductId].availability;
            $(".add-to-cart p:not(.no-hide), .add-to-cart button, .quantity button,.quantity p:not(.no-hide)", optionsParent).hide();
            if (availability == '0') {
                //   $(".notify-me", optionsParent).attr("href", currentConfig.childProducts[selectedProductId].notifyLink);
                productAlertUrl_currentproductid = selectedProductId;
                $(".add-to-cart .out-of-stock, .quantity .out-of-stock", optionsParent).show();
            } else {
                $(".add-to-cart .in-stock, .add-to-cart button, .quantity button.add-to-set", optionsParent).show();
            }

            if ($("#byos-block").length == 0) {
                updateReviewSelectionText(parent.parents('.product-options'));
            }


            /** ****/
            var arr = [];
            var sizeChoiceActive = '00';
            if (size= getSize()) {
                sizeChoiceActive = size.dataset.child;
            }
            //for (var x = 0; x < $$('div.product-depthChoice').length; x++) {
                var optins = spConfig.config.attributes[this.dataset.atr].options;
                for (var y = 0; y < optins.length; y++) {
                    for (var q = 0; q < optins[y].products.length; q++) {
                        if(optins[y].id == this.dataset.child) {
                            var com = spConfig.config.childProducts[optins[y].products[q]].combination.split('-')
                            if (com[0] == sizeChoiceActive) {
                                productId = optins[y].products[q];
                            }

                        }
                    }

                }
         //   }
            
            run(this.dataset.key,productId);

        });

        $(".product-drop-option .text-choice").click(function () {
            $(".add-to-cart .review-selection").html('');
            var optionsParent = $(this).parents('.product-options');
            var parent = $(this).parents('.product-drop-option');
            $('.active', parent).removeClass('active');
            $(this).addClass('active').parent().addClass('active');
            var whichDropOption = $(this).attr('rel');
            $('select', parent).val(whichDropOption).trigger('change');

            $(".color-swatches a.selected:first", optionsParent).click();

            //Add by Gorilla
            if ($(".color-swatches a").length == 0) {
                setButton(parent, whichDropOption);
            }

            /** ****/
            var size = getSize();
            var colorArray = $("[data-size="+size.dataset.child+"]");
            for (var o = 0; o < colorArray.length; o++) {
                if(this.dataset.child == colorArray[o].dataset.droplength){
                    run(colorArray[o].dataset.id,colorArray[o].dataset.key);
                }
            }

        });


        //quantity input toggle
        $(".toggle-large-qty").click(function () {
            var parent = $(this).parent();
            $("select", parent).toggle();
            $("input", parent).toggle().val($("select", parent).val());
            if ($("select", parent).is(':visible')) {
                $(this).text('Enter Large Quantity');
            } else {
                $(this).text('Select Quantity');
            }
            return false;
        });

        //quantity select change handler
        $(".product-option.quantity select").change(function () {
            $("input", $(this).parent()).val($(this).val());
        });

        $("#product_addtocart_form .product-essential .product-image-thumb").click(function () {
            $("#magic-zoom-trigger img:first").attr('src', $("input.main-view", this).val());
            $("#image-zoom-src").val($("input.zoom-view", this).val());
            $("#magic-zoom-trigger").attr("href", $("input.zoom-view", this).val());
            initMagicZoom();
            return false;
        });
        initMagicZoom();

        /* product detail image zoom */
        $("#product_addtocart_form #image-zoom").click(function () {
            window.open($("#image-zoom-src").val(), "Window1", "menubar=no,width=720,height=720,toolbar=no");
            return false;
        });

        /* size chart */
        $(".product-view .size-chart-link").click(function () {
            window.open("/mattress-size-chart", "", "menubar=no,width=280,height=530,toolbar=no");
            return false;
        });

        /* email friend */
        $(".product-view #email-to-friend").click(function () {
            window.open($(this).attr("href"), "", "menubar=no,width=550,height=670,toolbar=no,scrollbars=yes");
            return false;
        });

        /* PRODUCT DETAIL */
        $("#collateral-tabs dt").hover(
            function () {
                $(this).addClass("over");
            },
            function () {
                $(this).removeClass("over");
            }
        );

        $('.product-view .product-detail-wrapper').each(function (i) {
            if ($("#byos-block").length == 0) {
                var cssProperty = isIE6 ? 'height' : 'min-height';
                var height = $(this).height();
                var relatedHeight = $(".product-view related-products").height();

                if (relatedHeight > height) {
                    height = relatedHeight;
                }
                $(this).css(cssProperty, height + 'px');
                $(".product-view .related-products").css(cssProperty, height + 'px');
            }
        });

        /* rating form */
        var selectedRating = "";
        $("#review-form .form-star-ratings a").click(function () {
            var idx = $(this).attr("title") - 1;
            $("#review-form .form-star-ratings a").each(function (k, v) {
                if (k <= idx) $(this).addClass("active");
                else $(this).removeClass("active");
            });

            selectedRating = $(this).attr("id");
            $($(this).attr("rel")).trigger("click");
            return false;
        });

        $("#review-form .form-star-ratings a").hover(
            function () {
                var idx = $(this).attr("title") - 1;
                $("#review-form .form-star-ratings a").each(function (k, v) {
                    if (k < idx) $(this).addClass("active");
                    else $(this).removeClass("active");
                });
            }, function () {
                if (selectedRating.length > 0) $("#" + selectedRating).trigger('click');
                else $("#review-form .form-star-ratings a").removeClass("active");
            }
        );

        $(".review-form-trigger").click(function () {
            $(".review-form-wrapper").show();
        });

        if (window.location.hash == '#review-form') {
            $(".review-form-wrapper").show();
        }


        /* BYOS */
        $("#byos-block .byos-product-showDesc a").click(function () {
            var descContainer = $(this).parent().siblings(".byos-product-desc")
            if (descContainer.is(':visible')) {
                $(this).parent().siblings(".byos-product-desc").hide();
                $(this).text('More Information').removeClass("open");
            } else {
                $(this).parent().siblings(".byos-product-desc").show();
                $(this).text('Hide Information').addClass("open");
            }
            resizeByosIFrame();
            return false;
        });

        $("#byos-cart-items .not-empty").hide();

        $("#byos-block .product-option select").change(function () {
            $('.validation-advice', $(this).parent()).remove();
        });

        $('#byos-cart-items .product-remove a').live('click', function () {
            var setItems = $('#byos-cart-items');
            $(this).parent().parent().fadeOut('fast', function () {
                $(this).remove();

                var rowCount = $('tbody tr.not-empty', setItems).length;
                if (rowCount == 0) {
                    $('.not-empty', setItems).fadeOut(function () {
                        $('.empty-cart', setItems).fadeIn();
                    });
                } else {
                    decorateSetCart(setItems);
                }
            });

            return false;
        });

        $('#remove-items').click(function () {
            $('#byos-cart-items .not-empty').fadeOut('fast', function () {
                $('#byos-cart-items .empty-cart').fadeIn();
            });
            $('#byos-cart-items tbody tr.not-empty').remove();

            return false;
        });

        $("#byos-block .add-to-set").click(function () {
            var parent = $(this).parents('.byos-product');

            var isError = false;
            $('.product-option .validation-advice', parent).remove();
            $(".product-option select", parent).each(function (k, v) {
                if ($(this).val().length == 0) {
                    isError = true;
                    $(this).after('<div class="validation-advice">This is a required field.</div>');
                }
            });

            if (isError) {
                resizeByosIFrame();
                return false;
            } else {
                $('.added-to-set', parent).css('visibility', 'visible');
                setTimeout(function () {
                    $('.added-to-set').css('visibility', 'hidden');
                }, 2000);

                var setItems = $('#byos-cart-items');

                $('tbody tr.empty-cart', setItems).fadeOut(function () {
                    var $rowItem = $('.template', setItems).clone();
                    $rowItem.removeClass('template');
                    $('.product-name', $rowItem).html($('.byos-product-name', parent).html());
                    $('.item-number', $rowItem).html($('.byos-product-sku', parent).html());
                    $('.item-old-price', $rowItem).html($('.old-price', parent).html());

                    if ($('.was-old-price', parent).length > 0)
                        $('.item-new-price', $rowItem).html($('.was-old-price', parent).html());
                    else
                        $('.item-new-price', $rowItem).html($('.special-price', parent).html());

                    if (!$('.product-color-options select option[selected]', parent).text()) {
                        $('.product-color', $rowItem).html($('.product_color_simple', parent).val());
                    } else {
                        $('.product-color', $rowItem).html($('.product-color-options select option[selected]', parent).text());
                    }
                    $('.product-quantity', $rowItem).html($('.quantity input', parent).val());
                    $('.child-product-identifier', $rowItem).val($('.hidden-selected-product', parent).val());
                    if (!($('.hidden-selected-product', parent).val())) {
                        var spconfigIndex = $('.hidden-spconfig-index', parent).val();
                        $('.child-product-identifier', $rowItem).val(spConfig[spconfigIndex].config.productId);
                    }
                    $('.child-product-quantity', $rowItem).val($('.quantity input', parent).val());
                    $('.spconfig-index', $rowItem).val($('.hidden-spconfig-index', parent).val());


                    var size = $('.product-size-options select option[selected]', parent).text();
                    if ($('.product-depth-option', parent).length > 0) {
                        size += "<br />" + $('.product-depth-option select option[selected]', parent).text()
                    }

                    if ($('.product-drop-option', parent).length > 0) {
                        size += "<br />" + $('.product-drop-option select option[selected]', parent).text()
                    }
                    if (size) {
                        $('.product-size', $rowItem).html(size);
                    } else {
                        $('.product-size', $rowItem).html($('.product_size_simple', parent).val());
                    }

                    $rowItem.appendTo("tbody", setItems);


                    decorateSetCart(setItems);
                    $('.not-empty', setItems).fadeIn('fast', function () {
                        resizeByosIFrame();
                    });
                });
            }
        });

        $("#byos-block .btn-add-to-set").click(function () {
            var $setItems = $('#byos-cart-items');
            var $itemRows = $('tbody tr.not-empty', $setItems);

            if ($itemRows.length == 0) {
                $(this).after('<span class="validation-advice">Set is empty.</span>');
                setTimeout(function () {
                    $('.add-to-cart .validation-advice, .byos-instructions .validation-advice').hide().remove();
                }, 2000);
                return false;
            }

            $("button.btn-add-to-set").addClass("adding-to-set").attr('disabled', 'disabled');
            var items = new Array();
            $itemRows.each(function (k, v) {
                items[items.length] = $('.child-product-identifier', this).val() + "|" + $('.child-product-quantity', this).val();
            });

            $.post(
                "/byos/add/allcart", {'setItems': items.toString()}, function (data) {
                    if (data.error == '0') {
                        if ($(".content-frame", parent.document.body).length > 0)
                            parent.document.location.href = parent.document.location.href;
                        else
                            document.location.href = document.location.href;
                    } else {
                        $("button.btn-add-to-set").removeClass("adding-to-set").removeAttr('disabled', 'disabled');
                        window.location.hash = "your-set";

                        $("#byos-cart-items .add-set-error").fadeIn('fast', function () {
                            setTimeout(function () {
                                $("#byos-cart-items .add-set-error").fadeOut("fast");
                            }, 3500);
                        });
                    }
                }, 'json');

        });

        function decorateSetCart($parent) {
            var amountSaved = 0.00;
            var subTotal = 0.00;
            var grandTotal = 0.00;
            $('tbody tr:not(.template, .empty-cart)', $parent).each(function (k, v) {
                k % 2 == 0 ? $(this).attr("class", "not-empty even") : $(this).attr("class", "not-empty");

                var spconfigIndex = $('.spconfig-index', this).val();
                var childProductId = $('.child-product-identifier', this).val();
                var quantity = $('.child-product-quantity', this).val();
                if (!(spConfig[spconfigIndex] && spConfig[spconfigIndex].config.childProducts[childProductId])) {
                    var price = spConfig[spconfigIndex].config.basePrice;
                    if (price != null && price.length > 0) {
                        amountSaved += Math.round(((spConfig[spconfigIndex].config.oldPrice - price) * 100) * quantity) / 100;
                        grandTotal += Number(price * quantity);
                    } else {
                        grandTotal += Number(price * quantity);
                    }
                    subTotal += Number(price * quantity);
                } else {
                    var prices = spConfig[spconfigIndex].config.childProducts[childProductId];
                    if (prices.finalPrice != null && prices.finalPrice.length > 0) {
                        amountSaved += Math.round(((prices.price - prices.finalPrice) * 100) * quantity) / 100;
                        grandTotal += Number(prices.finalPrice * quantity);
                    } else {
                        grandTotal += Number(prices.price * quantity);
                    }
                    subTotal += Number(prices.price * quantity);
                }
            });

            if (amountSaved > 0) {
                $('.savings', $parent).show().children('span.orange').html("$" + amountSaved.toFixed(2));
                $('.discount', $parent).html("$" + amountSaved.toFixed(2));
            } else {
                $('.savings', $parent).hide();
                $('.discount', $parent).html("$0.00");
            }

            $(".subtotal", $parent).html("$" + subTotal.toFixed(2));
            $(".grand-total", $parent).html("$" + grandTotal.toFixed(2));
        }

        /* BYOS MODAL */
        if ($(".byos-modal").length > 0) {
            $(".byos-modal-header").css('display', 'block');
        }

        if ($("#byos-block").length > 0) {
            $("p.link-stock-alert").remove();
        }

        if($("#byos-overlay")){

            $("#byos-overlay-trigger").overlay({
                mask: {
                    color: '#333333',
                    loadSpeed: 200,
                    opacity: 0.6
                },
                closeOnClick: false,
                fixed: false,
                onBeforeLoad: function () {
                    this.getOverlay().addClass('loading');
                    var wrap = this.getOverlay().find(".content-frame");

                    if (wrap.attr("src") === undefined || wrap.attr("src").length == 0) {
                        wrap.attr("src", this.getTrigger().attr("href"));
                    }
                },
                onAfterLoad: function () {
                    if($('byos-block')){
                        var colorOptions = $$("div.product-color-options");
                        for (var o = 0; o < colorOptions.length; o++) {
                            run(o,'');
                        }
                    }
                    console.log($('byos-block'));
                }
            });
        }


        $("#byos-overlay .content-frame").load(function () {
            if (typeof($(this).attr('src')) != "undefined" && $(this).attr("src").length > 0) {
                $("#byos-overlay").removeClass("loading").children(".loader-grey").hide();
                $(this).show().animate({
                    height: $(this).contents().height()
                });
            }
        });


        //resizeByosIFrame();        
        function resizeByosIFrame() {
            var theFrame = $(".content-frame", parent.document.body);
            if (theFrame.length > 0 && typeof(theFrame.attr('src')) != "undefined") {
                theFrame.animate({
                    height: $(document.body).height() + 100
                }, 300);
            }
        }
    });

    $(window).load(function () {
        initProductDetail();
    });
})(jQuery);
