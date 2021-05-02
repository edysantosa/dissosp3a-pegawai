import '../common/common';
import Sitebase from '../library/sitebase';
import * as helper from '../library/helper';
import '../limitless/js/plugins/forms/validation/validate.min.js';
import Notification from '../library/notification';
import '../limitless/js/plugins/forms/styling/uniform.min.js';

$(function(){
    // Initialize
    var validator = $('#pegawai-edit-form').validate({
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
        }
    });
    
    $('.form-check-input-styled').uniform();
}).on('submit' , '#pegawai-edit-form' , function( ev ){
    ev.preventDefault();

    let frm = $(this);
    if( !frm.valid() ){
        return;
    }

    var serial = frm.serialize();

    $.ajax({
        url : Sitebase.url + '/pegawai/submit',
        type : 'post',
        dataType : 'json',
        data : serial,
        beforeSend: function (){
            frm.find('fieldset').prop('disabled' , true);
        }
    })
    .fail(function(xhr, status, statusText){
        if (xhr.status == 401) {
            window.location.replace(Sitebase.url + '/authentication?redirected=true');
        }
        var message = "Unknown error has occured";
        if( xhr.responseJSON ){
            message = xhr.responseJSON.message;
        }

        Notification.error(message);
    })
    .done(function( response ){
        location.href = Sitebase.url + '/pegawai';
    })
    .always(function(){
        // frm.find('fieldset').prop('disabled' , false);
    });

    return false;
});


/**
 * Helper Datatables
 */

