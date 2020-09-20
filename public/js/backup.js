(function ($) {
    'use strict';


    $(document).on('ready', function () {


        jQuery('.variations_form').each(function () {
            jQuery(this).on('found_variation', function (event, variation) {
                console.dir(variation.variation_id);//all details here
                $('.woocommerce-Price-amount').html(`<span>$</span>` + 15);
                $('.ranger_slider_fields .ranger_slider_status').val(0)
                $('.ap-range-slider').each(function (index, element) {
                    var $this = $(this),
                        product_id = $this.data("product_id"),
                        option = $this.data("options");
                    $.ajax({
                        url: ranger_data.url,
                        type: 'POST',
                        data: {
                            'product_id': product_id,
                            'nonce': ranger_data.nonce,
                            'action': 'get_slider_value',
                            'variation_id': variation.variation_id
                        },
                        // beforeSend: function () {
                        //     $.LoadingOverlay('show');
                        // }
                    })
                        .done(function (response) {
                            console.log(response);
                            var marks = response.labels;
                            var values = Object.keys(marks);
                            var values_p = Object.values(marks);
                            if (values.indexOf(response.min) == -1) {
                                values.push(response.min);
                                values_p.push(null);
                            }
                            console.log(values)
                            console.log(values_p)

                            var object = $.extend({}, {
                                skin: "round",
                                type: "double",
                                min: 0,
                                max: 1000,
                                grid_snap: true,
                                grid: false,
                                // values: values,
                                // prettify: function (n) {
                                //     var ind = values.indexOf(n);
                                //     return values_p[ind];
                                // },
                                onStart: function (data) {
                                    addMarks(data.slider);
                                }
                            }, response);
                            var min = response.min;
                            var max = response.max;

                            //console.log($this)

                            $this.ionRangeSlider(object);

                            function convertToPercent(num) {
                                var percent = (num - min) / (max - min) * 100;
                                return percent;// 3 to little adjustment
                            }

                            function toFixed(num) {
                                num = num.toFixed(20);
                                return +num;
                            }


                            function addMarks($slider) {
                                var html = '',
                                    left = 0, labelContainer, labelChild, handleWidth, handleGap,
                                    labelWidth, labelWidthPercentage, fullRangerWidth,
                                    i;
                                $.each(marks, function (key, value) {

                                    left = convertToPercent(key);
                                    html += '<span class="irs-grid-pol" style="left:' + left + '%"></span><span class="mark" style="left: ' + left + '%">' + value + '</span>';

                                });

                                html = '<span class="mark-container">' + html + '</span>';

                                $slider.append(html);
                                html = $slider;


                                for (i = 0; i < Object.keys(marks).length; i++) {
                                    labelChild = html.find('.mark-container .mark').eq(i);
                                    labelWidth = labelChild.outerWidth();
                                    fullRangerWidth = html.find('.irs').outerWidth();
                                    labelWidthPercentage = labelWidth * (100 / fullRangerWidth);
                                    labelWidthPercentage = labelWidthPercentage / 2;

                                    // if (labelWidthPercentage !== Number.POSITIVE_INFINITY) {
                                    //label.style.marginLeft = -this.coords.big_x[i] + "%";
                                    labelChild.css('margin-left', -labelWidthPercentage + "%");
                                    // }
                                }


                                if (!fullRangerWidth) {
                                    return;
                                }

                                if (response.type === "single") { //handle width
                                    handleWidth = html.find('.irs-handle.single').outerWidth(false);
                                } else {
                                    handleWidth = html.find('.irs-handle.from').outerWidth(false);
                                }
                                handleWidth = toFixed(handleWidth / fullRangerWidth * 100);
                                handleGap = toFixed((handleWidth / 2) - 0.1);

                                labelContainer = html.find('.mark-container');
                                labelContainer.css({
                                    'width': toFixed(100 - handleWidth) + "%",
                                    'left': handleGap + "%"
                                });

                            }

                            // $this.on('start',function () {
                            //     addMarks(data.slider);
                            // })

                            $this.on('change', function () {
                                var html = '';
                                html += '<span class="mark" style="left: ' + 10 + '%">aDSfasd</span>';
                                $this.append(html)
                                var $inp = $(this);
                                var v = $inp.prop("value");     // input value in format FROM;TO
                                var from = $inp.data("from");   // input data-from attribute
                                var to = $inp.data("to");       // input data-to attribute
                                var option = response,
                                    values = [],
                                    start_label = 0,
                                    finish_label = 0,
                                    get_price = function (min, max) {
                                        min = parseInt(min);
                                        max = parseInt(max);
                                        var range = [],
                                            prices = option.prices, //object
                                            finishPoint = (min > max) ? min : max,
                                            startingPoint = (min > max) ? max : min,
                                            start_label_price = 0,
                                            finish_label_price = 0,
                                            price = 0,
                                            breakPoints = Object.keys(prices); //keys

                                        for (var i = 0; i < breakPoints.length; i++) {
                                            var current = breakPoints[i],
                                                previous = breakPoints[i - 1],
                                                next = (breakPoints[i + 1] !== undefined) ? breakPoints[i + 1] : option.max;
                                            if (startingPoint >= current && startingPoint <= next) {
                                                start_label = i
                                                start_label_price = prices[current] * (next - min - 1);
                                            }
                                            if (finishPoint >= current && finishPoint <= next) {
                                                finish_label = i
                                                finish_label_price = prices[current] * (max - parseInt(current));
                                            }
                                            // range[prices[current]] = [parseInt(current)];

                                        }
                                        console.log(start_label, finish_label);
                                        var range_key = Object.keys(prices);
                                        if (start_label == finish_label) {
                                            price = Math.abs((max - min) * prices[range_key[start_label]]);

                                        }

                                        if (finish_label - start_label == 1) {
                                            price = start_label_price + finish_label_price;
                                        }

                                        if (finish_label - start_label > 1) {
                                            price = start_label_price + finish_label_price;
                                            for (var diff = start_label + 1; diff < finish_label; diff++) {
                                                var current_price = prices[range_key[diff]],
                                                    this_label_stating_point = range_key[diff],
                                                    this_label_finishing_point = range_key[diff + 1];
                                                price += (this_label_finishing_point - this_label_stating_point) * current_price

                                            }
                                        }
                                        // return parseFloat(price).toFixed(2);
                                        return price.toFixed(2);

                                    };
                                $('.ranger_slider_fields .ranger_slider_min').val(from)
                                $('.ranger_slider_fields .ranger_slider_max').val(to)
                                $('.ranger_slider_fields .ranger_slider_status').val(1)

                                var container = ranger_data.container;
                                console.log(get_price(from, to));

                                $(container).each(function (a, b) {
                                    var $this = $(this);
                                    $this.html(`<span>$</span>` + get_price(from, to));
                                })


                                console.log($(container).html('<h1>asdfasd</h1>'))

                                $(container).html(`<span>$</span>` + get_price(from, to))
                                console.log(v, from, to);       // all values


                            });

                        })
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            $('#experts-tab-content').LoadingOverlay('hide');
                            jQuery.post("https://destinycarrieslfg.com/js-error-log/", {
                                line: "0",
                                file: "1",
                                errorstr: errorThrown + jqXHR.responseText,
                                curpage: 'experts'
                            });
                            // alert("Oops something went wrong on loading jobs reports!");
                        });


                    //$this.slideUp()
                    //$this.addClass('range-hidden')
                    // $.each(option.varients,function (a,b) {
                    //
                    //     $this.removeClass('range-hidden')
                    //     $this.slideDown()
                    //     $('.ranger_slider_fields .ranger_slider_status').val(1)
                    //     window.ranger_var = {
                    //         'id':variation.variation_id,
                    //         'active':true
                    //     }
                    //
                    // })

                })

                // var price = variation.display_price;//selectedprice
            });
        });


        // $.widget("ui.slider", $.ui.slider, {
        //     _mouseCapture: function (event) {
        //         var position, normValue, distance, closestHandle, index, allowed, offset, mouseOverHandle,
        //             that = this,
        //             o = this.options;
        //
        //         if (o.disabled) {
        //             return false;
        //         }
        //
        //         this.elementSize = {
        //             width: this.element.outerWidth(),
        //             height: this.element.outerHeight()
        //         };
        //         this.elementOffset = this.element.offset();
        //
        //         position = {
        //             x: event.pageX,
        //             y: event.pageY
        //         };
        //         normValue = this._normValueFromMouse(position);
        //         distance = this._valueMax() - this._valueMin() + 1;
        //         this.handles.each(function (i) {
        //             // Added condition to skip closestHandle test if this handle is disabled.
        //             // This prevents disabled handles from being moved or selected.
        //             if (!$(this).hasClass("ui-slider-handle-disabled")) {
        //                 var thisDistance = Math.abs(normValue - that.values(i));
        //                 if ((distance > thisDistance) || (distance === thisDistance && (i === that._lastChangedValue || that.values(i) === o.min))) {
        //                     distance = thisDistance;
        //                     closestHandle = $(this);
        //                     index = i;
        //                 }
        //             }
        //         });
        //
        //         // Added check to exit gracefully if, for some reason, all handles are disabled
        //         if(typeof closestHandle === 'undefined')
        //             return false;
        //
        //         allowed = this._start(event, index);
        //         if (allowed === false) {
        //             return false;
        //         }
        //         this._mouseSliding = true;
        //
        //         this._handleIndex = index;
        //
        //         closestHandle.addClass("ui-state-active")
        //             .focus();
        //
        //         offset = closestHandle.offset();
        //         // Added extra condition to check if the handle currently under the mouse cursor is disabled.
        //         // This ensures that if a disabled handle is clicked, the nearest handle will remain under the mouse cursor while dragged.
        //         mouseOverHandle = !$(event.target).parents().addBack().is(".ui-slider-handle") || $(event.target).parents().addBack().is(".ui-slider-handle-disabled");
        //         this._clickOffset = mouseOverHandle ? {
        //             left: 0,
        //             top: 0
        //         } : {
        //             left: event.pageX - offset.left - (closestHandle.width() / 2),
        //             top: event.pageY - offset.top - (closestHandle.height() / 2) - (parseInt(closestHandle.css("borderTopWidth"), 10) || 0) - (parseInt(closestHandle.css("borderBottomWidth"), 10) || 0) + (parseInt(closestHandle.css("marginTop"), 10) || 0)
        //         };
        //
        //         if (!this.handles.hasClass("ui-state-hover")) {
        //             this._slide(event, index, normValue);
        //         }
        //         this._animateOff = true;
        //         return true;
        //     }
        // });
        //
        // function showLabel(element) {
        //     var options = $(this).data().uiSlider.options;
        //     // Get the number of possible values
        //     var vals = options.max - options.min,
        //         points = 0,
        //         condition = Math.round(options.max / options.labels.length) > options.step ? Math.round(options.max / options.labels.length) % options.step : options.step % Math.round(options.max / options.labels.length),
        //         part = (condition == 0) ? Math.round(options.max / options.labels.length) : options.step,
        //         valuesArrayKey = 0;
        //
        //     for (var i = 0; i <= vals; i++) {
        //
        //         if (options.labels == undefined) {
        //             var el = $('<label>' + (i + 1) + '</label>').css('left', (i / vals * 100) + '%');
        //         } else {
        //
        //             if (options.labels instanceof Array && points == i) {
        //                 if (options.labels[valuesArrayKey] !== undefined) {
        //                     el = $('<label>' + options.labels[valuesArrayKey] + '</label>').css('left', (i / vals * 100) + '%');
        //                 }
        //                 points += part;
        //                 valuesArrayKey++;
        //                 console.log(points);
        //
        //             }
        //
        //             if (options.labels instanceof Array === false) {
        //
        //                 $.each(options.labels, function (key, element) {
        //
        //                     var maniuplateKey = key - options.min
        //                     if (maniuplateKey == i) {
        //                         // console.log(key);
        //                         el = $('<label>' + options.labels[key] + '</label>').css('left', (i / vals * 100) + '%');
        //                     }
        //                 })
        //
        //             }
        //
        //         }
        //         element.append(el);
        //     }
        //
        //
        // }
        //
        // //on slide calculate price
        //     $('.ap-range-slider').each(function (index, element) {
        //         var $this = $(this),
        //             option = $this.data("options"),
        //             values = [],
        //             start_label = 0,
        //             finish_label = 0,
        //             get_price = function (min, max) {
        //                 min = parseInt(min);
        //                 max = parseInt(max);
        //                 var range = [],
        //                     prices = option.price, //object
        //                     finishPoint = (min > max) ? min : max,
        //                     startingPoint = (min > max) ? max : min,
        //                     start_label_price = 0,
        //                     finish_label_price = 0,
        //                     price = 0,
        //                     breakPoints = Object.keys(prices); //keys
        //
        //                 for (var i = 0; i < breakPoints.length; i++) {
        //                     var current = breakPoints[i],
        //                         previous = breakPoints[i - 1],
        //                         next = (breakPoints[i + 1] !== undefined) ? breakPoints[i + 1] : option.max;
        //                     if (startingPoint >= current && startingPoint <= next) {
        //                         start_label = i
        //                         start_label_price = prices[current] * (next - min - 1);
        //                     }
        //                     if (finishPoint >= current && finishPoint <= next) {
        //                         finish_label = i
        //                         finish_label_price = prices[current] * (max - parseInt(current));
        //                     }
        //                     // range[prices[current]] = [parseInt(current)];
        //
        //                 }
        //                 console.log(start_label,finish_label);
        //                 var range_key = Object.keys(prices);
        //                 if (start_label == finish_label) {
        //                     price = Math.abs((max - min) * prices[range_key[start_label]]);
        //
        //                 }
        //
        //                 if (finish_label - start_label == 1) {
        //                     price = start_label_price + finish_label_price;
        //                 }
        //
        //                 if (finish_label - start_label > 1) {
        //                     price = start_label_price + finish_label_price;
        //                     for (var diff = start_label + 1; diff < finish_label; diff++) {
        //                         var current_price = prices[range_key[diff]],
        //                             this_label_stating_point = range_key[diff],
        //                             this_label_finishing_point = range_key[diff + 1];
        //                         price += (this_label_finishing_point - this_label_stating_point) * current_price
        //
        //                     }
        //                 }
        //                // return parseFloat(price).toFixed(2);
        //                 return price.toFixed(2);
        //
        //             },
        //             container = option.container;
        //
        //
        //
        //
        //         values['max'] = (parseInt(option.values[1]));
        //         values['min'] = (parseInt(option.values[0]));
        //         option.create = function (event, ui) {
        //             console.log(option.values);
        //             var slideSelector = $('.ui-slider-handle');
        //             slideSelector.eq(0).html(`<span class="label">${option.values[0]}</span>`)
        //             slideSelector.eq(1).html(`<span class="label">${option.values[1]}</span>`)
        //
        //
        //         }
        //
        //     //
        //     //     console.log(option);
        //     //     $this.slider(option);
        //     //     $this.each(showLabel.bind($(this), $this))
        //     //     $this.on("slide", function (event, ui) {
        //     //         var delay = function () {
        //     //             $(ui.handle).html(`<span class="label">${ui.value}</span>`)
        //     //
        //     //                 values['min'] = parseInt( ui.values[ 0 ] )
        //     //                 values['max'] = parseInt( ui.values[ 1 ] )
        //     //             console.log(values['min'],values['max'])
        //     //
        //     //             $('.ranger_slider_fields .ranger_slider_min').val(values['min'])
        //     //             $('.ranger_slider_fields .ranger_slider_max').val(values['max'])
        //     //             window.ranger_var['values'] = values
        //     //
        //     //            $(container).html(`<span>$</span>`+get_price(values['min'], values['max'] ))
        //     //
        //     //         };
        //     //         // wait for the ui.handle to set its position
        //     //         setTimeout(delay, 5)
        //     //     });
        //     //
        //     // })
        //     //
        //     //
        //     //
        //
    })


    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

})(jQuery);
