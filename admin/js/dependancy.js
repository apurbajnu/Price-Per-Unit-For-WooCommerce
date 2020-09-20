/**
 * polly fill
 */



(function ($) {
    'use strict';

    $(document).ready(function () {


        function input_field_swapping(inputClass, inputValue, targatedDiv, inverse) {

            var obtain_value;

            var inputType = $(inputClass).attr('type');


            console.log(targatedDiv);
            switch(inputType) {
                case 'checkbox':
                    obtain_value =  $(inputClass).is(':checked')
                    break;
                case 'radio':
                    obtain_value = $(inputClass+':checked').val()
                    break;
                default:
                    obtain_value = $(inputClass).val()
            }

            var $tgDiv = $(targatedDiv);

            var valueTypeArray = Array.isArray(inputValue);

            $tgDiv.hide();

            if (inverse && obtain_value !== '') {
                $tgDiv.show();
            }

            function div_calculate() {


                 switch(inputType) {
                    case 'checkbox':
                        obtain_value =  $(inputClass).is(':checked')
                        break;
                    case 'radio':
                        obtain_value = $(inputClass+':checked').val()
                        break;
                    default:
                        obtain_value = $(inputClass).val()
                }
                


                $tgDiv.hide();

                if (inverse) {
                    $tgDiv.show();
                }

                if (valueTypeArray) {

                    inputValue.forEach(function (element) {

                        if (obtain_value == element  ) {
                            if ($tgDiv.is(':hidden')) {
                                $tgDiv.show();
                            }

                            if (inverse) {
                                $tgDiv.hide();
                            }


                        }

                    })

                } else {

                    if (obtain_value == inputValue    ) {
                        $tgDiv.show();
                        if (inverse) {
                            $tgDiv.hide();
                        }
                    }

                }


            }


            $(inputClass).on('change', function () {
                div_calculate()
            });

            $(window).on('load', function () {
                div_calculate()

            })

        }/**/

        $.each(dependancy,function(a,b){
           var inputClass = b.name ;
           var tginputClass = "."+$( b.target).closest('tr').attr('class'); ;


           var inputType = $(inputClass).prop('type');
           if(inputType == 'radio' || inputType == 'multicheck'){
               inputClass = $(inputClass).attr('name');
               inputClass = 'input[name="'+inputClass+'"]';
           }
            console.log(b);
             input_field_swapping(inputClass,b.value,tginputClass);

        })



    })

})(jQuery)