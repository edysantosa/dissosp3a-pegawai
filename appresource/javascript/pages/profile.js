import '../common/common';
import Sitebase from '../library/sitebase';
import * as helper from '../library/helper';
import '../limitless/js/plugins/forms/validation/validate.min.js';
import '../limitless/js/plugins/forms/styling/uniform.min.js';
import Notification from '../library/notification';

$(function(){
    // Initialize
    var validator = $('#user-edit-form').validate({
        ignore: 'input[type=hidden], .select2-search__field', // ignore hidden fields
        errorClass: 'validation-invalid-label',
        successClass: 'validation-valid-label',
        validClass: 'validation-valid-label',
        highlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        rules: {
            password: {
                minlength: 6
            },
            password2: {
                equalTo: '#password'
            }
        }
    });
}).on('submit' , '#user-edit-form' , function( ev ){
    ev.preventDefault();

    let frm = $(this);
    if( !frm.valid() ){
        return;
    }

    var serial = frm.serialize();
    frm.find('fieldset').prop('disabled' , true);

    $.ajax({
        url : Sitebase.url + '/profile/submit',
        type : 'post',
        dataType : 'json',
        data : serial
    })
    
    .fail(function(xhr){
        var message = "Unknown error has occured";
        if( xhr.responseJSON ){
            message = xhr.responseJSON.message;
        }

        Notification.error(message);
    })
    
    .done(function( response ){
        Notification.info(response.message);
    })
    
    .always(function(){
        frm.find('fieldset').prop('disabled' , false);
    });

    return false;
}).on('click' , '.upload-image' , function( ev ){
    $("#image").trigger('click');
}).on('change' , '#image' , function( ev ){


    if (ev.target.files[0].size > 1048576*5) {
       Notification.warning('File size too large.');
       ev.value = '';
       return;
    }

    let formData = new FormData($('#image-form')[0])
    formData.append('task', 'image');

    $.ajax({
        url : Sitebase.url + '/profile/submit',
        type : 'post',
        data : formData,
        cache: false,
        contentType: false,
        processData: false
    })
    
    .fail(function(xhr){
        var message = "Unknown error has occured";
        if( xhr.responseJSON ){
            message = xhr.responseJSON.message;
        }

        Notification.error(message);
    })
    
    .done(function( response ){
        Notification.info(response.message);
        $(".profile-image").attr("src", response.image);
    });

    return false;

});


/**
 * Helper Datatables
 */

