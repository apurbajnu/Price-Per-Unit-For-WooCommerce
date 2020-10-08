(function ($) {
    'use strict';


    $(document).on('ready', function () {


        function afterAjaxCompleted(data) {


            $('.ap-range-slider').each(function (parentEachCalled) {
                var $this = $(this),
                    totalPoint = 0,
                    findDiveder = function (maxNumber, step) { // find the ranger point which module is 0
                        maxNumber = parseInt(maxNumber);
                        step = parseInt(step);
                        var divider = maxNumber / 3;
                        while ((divider % step) !== 0) {
                            maxNumber++;
                            divider = maxNumber / 3;
                        }
                        return parseInt(divider)

                    },
                    defaultRangerObject = {
                        skin: "square",
                        type: "single",
                        min: 0,
                        max: 0,
                        from: 0,
                        step: 1,
                        grid_snap: true,
                        grid: false,

                    },
                    buildObject = function (givenObject) {
                        var marks = {};
                        var defaultObject = Object.assign({}, defaultRangerObject);
                        var numberLength = givenObject.max;
                        numberLength = numberLength.toString().length;
                        if (numberLength < 4) {
                            defaultObject.grid = true;
                        } else {
                            var divider = findDiveder(givenObject.max, givenObject.step),
                                dividerDouble = divider * 2;

                            marks[givenObject.min] = givenObject.min
                            marks[divider] = divider
                            marks[dividerDouble] = dividerDouble
                            marks[givenObject.max] = givenObject.max
                            defaultObject.grid = false;
                            defaultObject.onStart = function (data) {
                                addMarks(data.slider, marks);
                            }

                        }

                        if(data['range_slider']['unit'] !== null){
                            defaultObject['postfix'] = data['range_slider']['unit']
                        }


                        var object = $.extend(defaultObject, {
                            min: givenObject.min,
                            max: givenObject.max,
                            from: givenObject.min,
                            step: givenObject.step,
                        });

                        return object;

                    },
                    container = ranger_data.container;

                $('.ranger_slider_total_point').val(totalPoint);

                //response == rangslider value


                if (data.range_slider == 'Slider Not Available' || data == 'Slider Not Available') {

                    if ($this.hasClass('irs-hidden-input')) {
                        var created_range = $this.data("ionRangeSlider");
                        created_range.destroy();
                        $('.range-slider-title').text('')
                        $('.ranger_slider_fields .ranger_slider_min_x').val(0);
                        $('.ranger_slider_fields .ranger_slider_max_x').val(0);
                        $('.ranger_slider_total_point').val(totalPoint);

                    }
                    $('.price-per-unit-details').css('display','none');
                    $('#tab-title-price_per_unit_tab').css('visibility', 'hidden')
                    $('#tab-price_per_unit_tab').css('visibility', 'hidden')
                    return;
                }


                //reset
                $('#tab-title-price_per_unit_tab').css('visibility', 'visible')
                $('#tab-price_per_unit_tab').css('visibility', 'visible')
                $('.price-per-unit-details').css('display','block');


                var response = data['range_slider'];
                var dimension = response.dimension,
                    primitive_price = 0; //parseFloat(response.primitive_price);

                //update woocommerce price tab
                var price_list = response.prices;
                var priceRow = '';
                var totalUnit = '';
                var minPrice = 0;

                if(response.unit !=''){
                    switch(response.measurement){

                        case 'a':
                            totalUnit = response.unit+ "<sup>2</sup>"
                            break;
                        case 'v':
                            totalUnit = response.unit+ "<sup>3</sup>"
                            break
                        default:
                            totalUnit = response.unit
                            break
                    }

                }

                var prevKey = undefined;
                var displayMinPrice = undefined;
                var i = 0;


                $.each(price_list,function (key,value) {
                    var isLastElement = i == Object.keys(price_list).length -1;
                    if(prevKey === undefined){
                        priceRow += `<tr><td>Up To - ${key}${totalUnit}</td><td>${value}/${totalUnit}</td></tr>`;
                    }else{
                        priceRow += `<tr><td>${prevKey+1}${totalUnit} - ${key}${totalUnit}</td><td>${value}/${totalUnit}</td></tr>`;
                    }
                    prevKey = parseFloat(key);

                    if(isLastElement){
                        displayMinPrice = value
                    }
                    i++;

                })
                $('#tab-price_per_unit_tab').html(  `<table><th>${ranger_data.unit}</th><th>${ranger_data.price}</th>${priceRow}</table>`)
                //update display price
                // if(displayMinPrice !== undefined){
                // $(container).html( ranger_data.min_price +' ' +response.woo_price.replace('given_price', displayMinPrice+'/'+totalUnit));
                //}



                if ($this.hasClass('irs-hidden-input')) {
                    created_range = $this.data("ionRangeSlider");
                    created_range.reset();
                    created_range.destroy();
                    $('.ranger_slider_fields .ranger_slider_min_x').val(0);
                    $('.ranger_slider_fields .ranger_slider_max_x').val(0);
                    $('.ranger_slider_total_point').val(totalPoint);
                    $('.ranger_slider_max_z').val('');
                }

                //delete title and reset height first
                if(parentEachCalled<1) {
                    $('.x-axis').css('height', '0px')
                    $('.range-slider-title').text('')
                    $('.ppu-total-area').html(0 + totalUnit)
                    $('.ppu-total-cost').html(response.woo_price.replace('given_price', 0))
                }




                if ($this.parent().hasClass('x-axis') && response.x_dimension.min !== undefined) {
                    var obtainObject = buildObject(response.x_dimension),
                        min = response.x_dimension.min,
                        max = response.x_dimension.max;
                    $('.x-axis .range-slider-title').text(response.x_dimension.title)
                    $('.x-axis').css('height','100px');
                    //initialize
                    $this.ionRangeSlider(obtainObject);

                }



                function convertToPercent(num) {
                    var percent = (num - min) / (max - min) * 100;
                    return percent;// 3 to little adjustment
                }

                function toFixed(num) {
                    num = num.toFixed(20);
                    return +num;
                }


                function get_price_single(dimension) {


                    var prices = response.prices, //object
                        price = 0,
                        currentPoint = 0,
                        cachePoint = 0,
                        previousPoint = null,
                        breakPoints = Object.keys(prices), //keys
                        x_min = $('.ranger_slider_fields .ranger_slider_min_x').val(),
                        x_max = $('.ranger_slider_fields .ranger_slider_max_x').val(),
                        max = 0,
                        min = 0;

                    if (dimension == 'one_dimension') {
                        min = x_max;
                        max = x_max;
                    }
                    currentPoint = max;


                    for (var i = 0; i < breakPoints.length; i++) {

                        var starting_price = parseFloat(prices[breakPoints[i]]), //1
                            starting_point = parseInt(breakPoints[i]), //10
                            next_point = null;


                        if (i !== breakPoints.length - 1) {
                            next_point = parseInt(breakPoints[i + 1]); // 10
                        }


                        if (currentPoint > starting_point && next_point !== null) {
                            continue
                        } else {
                            price = currentPoint * starting_price;
                            break
                        }
                        if (currentPoint <= starting_point) {
                            price = currentPoint * starting_price;
                            break;
                        }

                    }


                    price = price + primitive_price;
                    return [price.toFixed(2), currentPoint]

                }

                function addMarks($slider, marks) {

                    var html = '',
                        updatePrice = 0,
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
                        labelChild.css('margin-left', -labelWidthPercentage + "%");

                    }

                    if (!fullRangerWidth) {
                        return;
                    }


                    handleWidth = html.find('.irs-handle.single').outerWidth(false);

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

                    // var html = '';
                    // html += '<span class="mark" style="left: ' + 10 + '%"></span>';
                    // $this.append(html)

                    var $inp = $(this),
                        updatePrice = 0;

                    var priceUpdate = function () {
                        var from = min,  // input data-from attribute
                            to = $inp.data("from")        // input data-to attribute
                        to = (to === undefined) ? $this.val() : to; // for input box when slider not initialized

                        if ($this.parent().hasClass('x-axis')) {
                            $('.ranger_slider_fields .ranger_slider_min_x').val(from)
                            $('.ranger_slider_fields .ranger_slider_max_x').val(to)
                        }

                        // console.log(dimension);

                        updatePrice = get_price_single(dimension);
                        totalPoint = updatePrice[1];
                        updatePrice = updatePrice[0];


                        $('.ppu-total-area').html(totalPoint + totalUnit)
                        $('.ppu-total-cost').html(response.woo_price.replace('given_price', updatePrice))
                        $('.ranger_slider_total_point').val(totalPoint);

                    };
                    // wait for the ui.handle to set its position
                    setTimeout(priceUpdate, 500); //after complete moving
                    // return;
                });

            })


        }


        // variable product
        jQuery('.variations_form').each(function () {
            jQuery(this).on('found_variation', function (event, variation) {
                $('.ranger_slider_fields .ranger_slider_status').val(0)
                $('.ap-range-slider-container').each(function () {
                    var $this = $(this),
                        product_id = $this.data("product_id");
                    $.ajax({
                        url: ranger_data.url,
                        type: 'POST',
                        data: {
                            'product_id': product_id,
                            'nonce': ranger_data.nonce,
                            'action': 'get_slider_value',
                            'variation_id': variation.variation_id
                        },
                    })
                        .done(function (response) {
                            afterAjaxCompleted(response)
                        })

                        .fail(function (jqXHR, textStatus, errorThrown) {
                            console.log('Ajax Failed');
                            // alert("Oops something went wrong on loading jobs reports!");
                        });


                });

            });
        });

        // simple product
        $(window).on('load', function () {
            $('.ap-range-slider-container').each(function (a, b) {
                var $this = $(this),
                    product_id = $this.data("product_id");
                $.ajax({
                    url: ranger_data.url,
                    type: 'POST',
                    data: {
                        'product_id': product_id,
                        'nonce': ranger_data.nonce,
                        'action': 'get_slider_value_for_simple_product',
                    },
                })
                    .done(function (response) {
                        afterAjaxCompleted(response)
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                    console.log('Ajax Failed');
                    // alert("Oops something went wrong on loading jobs reports!");
                });


            });

        })

        $('body').on('click', '.reset_variations', function (e) {
            e.preventDefault();

            $('.ap-range-slider').each(function (index, element) {
                var $this = $(this);
                if ($this.hasClass('irs-hidden-input')) {
                    var created_range = $this.data("ionRangeSlider");
                    created_range.destroy();
                    $('.range-slider-title').text('');
                    $('.range-slider-description').html('');
                    $('.ranger_slider_fields .ranger_slider_min').val('')
                    $('.ranger_slider_fields .ranger_slider_max').val('')
                    $('.ranger_slider_fields .ranger_slider_type').val('')
                    $('.ranger_slider_fields .ranger_slider_status').val(0);

                }
            });

        });


    })


})(jQuery);
