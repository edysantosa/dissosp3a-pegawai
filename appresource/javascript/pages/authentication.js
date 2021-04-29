import '../common/common';
// import 'jquery';
import Sitebase from '../library/sitebase';
import Notification from '../library/notification';
import '../limitless/js/plugins/forms/validation/validate.min.js';
import '../limitless/images/logo.png';

var validator;

$(function(){
    // Initialize
    validator = $('.login-form, .reset-form').validate({
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
        // success: function(label) {
        //     label.addClass('validation-valid-label').text('Success.'); // remove to hide Success message
        // },
        // Different components require proper error label placement
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        rules: {
            password: {
                minlength: 6
            },
            email: {
                email: true
            }
        }
    });

    $('.reset-form').attr('action', Sitebase.url + '/authentication/submit-reset').attr("method", "post");

}).on('submit' , '.login-form' , function( ev ){
    ev.preventDefault();

    let frm = $(this);
    if( !frm.valid() ){
        return;
    }

    var serial = frm.serialize();
    frm.find('fieldset').prop('disabled' , true);

    $.ajax({
        url : Sitebase.url + '/authentication/signin',
        type : 'post',
        dataType : 'json',
        data : serial
    })
    
    .fail(function(xhr, status, statusText){
        var message = "Unknown error has occured";
        if( xhr.responseJSON ){
            message = xhr.responseJSON.message;
        }

        Notification.error(message);
    })
    
    .done(function( response ){
        location.href = Sitebase.url;
    })
    
    .always(function(){
        frm.find('fieldset').prop('disabled' , false);
    });

    return false;
}).on('submit' , '.reset-form' , function( ev ){
    // ev.preventDefault();

    // let frm = $(this);
    // if( !frm.valid() ){
    //     return;
    // }

    
});