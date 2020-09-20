(function ($) {
    'use strict';

    $(document).ready(function () {


        function input_field_swapping(inputClass, inputValue, targatedDiv, inverse) {

            var obtain_value = null;

            var inputType = $(inputClass).attr('type');


            //console.log(targatedDiv);
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

                if ( Array.isArray(inputValue)) {

                    inputValue.forEach(function (element) {

                        if (  $.inArray(obtain_value, inputValue) > -1  ) {
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


            $(document).on('change','#range_meta_boxes_pro',function (e) {
                div_calculate()
            });


                div_calculate()


        }/**/



        $.each(dependancy_meta,function(a,b){


            $(document.body).find('.repeater-segment ').each(function () {
                var inputClass = $(this).find(b.name+' select'),
                    tginputClass = $(this).find(b.target),
                    value = b.value;



                var inputType = $(inputClass).prop('type');
                if(inputType == 'radio' || inputType == 'multicheck'){
                    inputClass = $(inputClass).attr('name');
                    inputClass = 'input[name="'+inputClass+'"]';
                }
                if(IsJsonString(value)){
                    value = JSON.parse(value)
                }
                console.log(b);
                input_field_swapping(inputClass,value,tginputClass);
            })


        })


        function IsJsonString(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }

        $('.color-metabox').find('input').wpColorPicker();

        $('.gallery-image-metabox').each(function(){
            var frame,
                savedImages=[],
                generatedHtml = '',
                savedValue = '' ,
                numberOfImages,
                imgval = [] ,
                metaBox = $(this).children('div').eq(0), // Your meta box id here
                addImgLink = metaBox.find('.upload-custom-img'),
                delImgLink = metaBox.find( '.delete-custom-img'),
                imgContainer = metaBox.find( '.custom-img-container'),
                imgIdInput = metaBox.find( 'input' );
            if ( imgIdInput.val()){
                savedImages = JSON.parse(imgIdInput.val());
            }


            // ADD IMAGE LINK
            addImgLink .on( 'click', function( event ){

                event.preventDefault();

                // If the media frame already exists, reopen it.
                if ( frame ) {
                    frame.open();
                    return;
                }

                // Create a new media frame

                frame = wp.media.frames.file_frame = wp.media({
                    title: 'Select a image to upload',
                    button: {
                        text: 'Use this image',
                    },
                    multiple: true,
                    library: { type : 'image' },// Set to true to allow multiple files to be selected
                });

                frame.on('open',function() {

                    var selection = frame.state().get('selection');


                    savedImages.forEach(function(pf) {

                        var attachment = wp.media.attachment(pf.id);
                        attachment.fetch();

                        attachment.set({
                            'mipfLink': pf.link,
                            'mipfTitle': pf.title
                        });

                        selection.add( attachment ? [ attachment ] : [] );

                    });
                });


                // When an image is selected in the media frame...
                frame.on( 'select', function() {

                    // Get media attachment details from the frame state
                    var generatedHtml = '';
                    var attachment = frame.state().get('selection').toJSON();
                    numberOfImages = attachment.length;
                    attachment.forEach(function(object, index){
                        generatedHtml += '<div class="img-container" style="width:32%"><img src="'+object.url+'" alt="" /></div>';

                        imgval.push({
                            id: object.id,
                            url: object.url,
                            title: object.title
                        })
                        // Send the attachment id to our hidden input

                    })

                    imgContainer.empty().append( generatedHtml );

                    imgIdInput.val(JSON.stringify(imgval));
                    // Hide the add image link
                    addImgLink.addClass( 'hidden' );

                    // Unhide the remove image link
                    delImgLink.removeClass( 'hidden' );
                    var selection = frame.state().get('selection');
                    var selected = ''; // the id of the image

                    selection.add(wp.media.attachment(selected));


                });

                // Finally, open the modal on click
                frame.open();


            });


            // DELETE IMAGE LINK
            delImgLink.on( 'click', function( event ){

                event.preventDefault();

                // Clear out the preview image
                imgContainer.html( '' );

                // Un-hide the add image link
                addImgLink.removeClass( 'hidden' );

                // Hide the delete image link
                delImgLink.addClass( 'hidden' );

                // Delete the image id from the hidden input
                imgIdInput.val( '' );
                savedImages = [];
                imgval = [];


            });

            $(window).on('load',function () {

                numberOfImages = savedImages.length;
                if(numberOfImages>0){
                    // Un-hide the add image link
                    addImgLink.removeClass('hidden');

                    // Hide the delete image link
                    delImgLink.removeClass('hidden');

                }


                savedImages.forEach(function (object) {

                    savedValue += '<div class="img-container" style="width:32%" > <img  src="'+object.url+'" alt="" /></div>';
                })
                imgContainer.append( savedValue );
            })



        })// end of gallery

        var mi_meta_repeater = $('.ap-meta-repeater');



        mi_meta_repeater.each(
            function (a,b) {
                var self = $(b);
                var addButton = self.children('.repeater-add-button');
                var defaults = JSON.parse( addButton.attr('data-defaut-value') );

                self.repeater(
                    {

                        defaultValues: defaults,

                        show: function () {
                            $(this).slideDown();
                            $(this).find('label').attr('for', $(this).find('input').attr('id'));

                            $.each(dependancy_meta,function(a,b){


                                $(document.body).find('.repeater-segment ').each(function () {
                                    var inputClass = $(this).find(b.name+' select'),
                                        tginputClass = $(this).find(b.target),
                                        value = b.value;



                                    var inputType = $(inputClass).prop('type');
                                    if(inputType == 'radio' || inputType == 'multicheck'){
                                        inputClass = $(inputClass).attr('name');
                                        inputClass = 'input[name="'+inputClass+'"]';
                                    }
                                    if(IsJsonString(value)){
                                        value = JSON.parse(value)
                                    }
                                    console.log(b);
                                    input_field_swapping(inputClass,value,tginputClass);
                                })


                            })

                        },

                        hide: function (deleteElement) {

                            if (confirm('Are you sure you want to delete this element?')) {

                                var parent = $(this).parent().parent();

                                $(this).slideUp(function () {

                                    $(this).remove();


                                    /*get the Group name of reapeater value*/
                                    var repeaterGroupname = $(this).parent().data('repeater-list');
                                    /*repeater value array*/
                                    var repeaterArray = parent.repeaterVal();
                                    /*Deleted Item index*/
                                    var elementIndex = $(this).index();

                                    var hiddenField = parent.data('value-field');

                                    $('#'+hiddenField).val(JSON.stringify(repeaterArray));

                                });
                            }
                        },


                        isFirstItemUndeletable: true
                    }
                );

                self.delegate('input,select,textarea','change',function () {
                    var  parent =  self;
                    var hiddenField = parent.data('value-field');
                    var repeaterVal = parent.repeaterVal();
                    $('#'+hiddenField).val(JSON.stringify(repeaterVal));
                })

                self.delegate(addButton,'click',function () {
                    var  parent =  self;
                    var hiddenField = parent.data('value-field');
                    var repeaterVal = parent.repeaterVal();
                    $('#'+hiddenField).val(JSON.stringify(repeaterVal));
                })


            }
        )


    })// end of ready

})(jQuery)

