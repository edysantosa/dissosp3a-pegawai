import '../common/common';
import Sitebase from '../library/sitebase';
import * as helper from '../library/helper';
import '../limitless/js/plugins/forms/validation/validate.min.js';
import '../limitless/js/plugins/forms/styling/uniform.min.js';
import Notification from '../library/notification';

$(function(){

}).on('change' , '#excel' , function( ev ){


    if (ev.target.files[0].size > 1048576*5) {
       Notification.warning('File size too large.');
       ev.value = '';
       return;
    }

    let formData = new FormData($('#excel-form')[0]);
    $.ajax({
        url : Sitebase.url + '/tools/submit-import-pegawai',
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
    });

    return false;

});
