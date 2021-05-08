import '../common/common';
import Sitebase from '../library/sitebase';
import * as helper from '../library/helper';
import '../limitless/js/plugins/forms/validation/validate.min.js';
import '../limitless/js/plugins/forms/validation/localization/messages_id.js';
import Notification from '../library/notification';
import '../limitless/js/plugins/forms/styling/uniform.min.js';

import daterangepicker from 'daterangepicker';
import '../../../node_modules/daterangepicker/daterangepicker.css';

import moment from 'moment';
import Bootbox from '../limitless/js/plugins/notifications/bootbox.min.js';

// Preview gambar sebelum upload
var reader = new FileReader();
reader.onload = function (e) {
    $('#pegawai-image-preview').attr('src', e.target.result);
};
function readURL(input) {
    if (input.files && input.files[0]) {
        reader.readAsDataURL(input.files[0]);
    }
}

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
    $('.form-input-styled').uniform({
        fileButtonClass: 'action btn bg-pink-400',
        fileButtonHtml: 'Pilih File',
        fileDefaultHtml: 'Belum ada file yang dipilih'
    });


    // Basic initialization
    initDatePicker($('.daterange'));
}).on('click' , '#test' , function( ev ){
    $(".daterange").each(function(){
        let elemName =  $(this).data('variable');
        // let tgl = '';
        let tgl = $(this).val();
        if ($(this).val() != '') {
            tgl =  $(this).data('daterangepicker').startDate.format('YYYY-MM-DD');
        }
            console.log(`${elemName}  : ${tgl}`);
    });
    // $('.gbk-tgl-mulai').each(function(index) {
    //     // let date = moment($(this).val(), 'DD-MM-YYYY').format('YYYY-MM-DD');
    //     let date = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD');
    //     console.log(date);
    // });
}).on('submit' , '#pegawai-edit-form' , function( ev ){
    ev.preventDefault();

    let frm = $(this);
    if( !frm.valid() ){
        return;
    }
    // var serial = frm.serializeArray();
    // console.log(serial);
    // return;
    let formData = new FormData($(this)[0]);


    // let tglLahir = '';
    // if($('.cpns-tgl-bkn').val()!=""){
    //     tglLahir = $('.tgl-lahir').data('daterangepicker').startDate.format('YYYY-MM-DD');
    // }    
    // formData.append('tglLahir', tglLahir);
    // let cpnsTglBKN = '';
    // if($('.cpns-tgl-bkn').val()!=""){
    //     cpnsTglBKN = $('.cpns-tgl-bkn').data('daterangepicker').startDate.format('YYYY-MM-DD');
    // }    
    // formData.append('cpnsTglBKN', cpnsTglBKN);
    // let cpnsTglSK = '';
    // if($('.cpns-tgl-bkn').val()!=""){
    //     cpnsTglSK = $('.cpns-tgl-sk').data('daterangepicker').startDate.format('YYYY-MM-DD');
    // }    
    // formData.append('cpnsTglSK', cpnsTglSK);
    // let cpnsTMT = '';
    // if($('.cpns-tgl-bkn').val()!=""){
    //     cpnsTMT = $('.cpns-tmt').data('daterangepicker').startDate.format('YYYY-MM-DD');
    // }    
    // formData.append('cpnsTMT', cpnsTMT);
    // let pnsTglSK = '';
    // if($('.pns-tgl-bkn').val()!=""){
    //     pnsTglSK = $('.pns-tgl-sk').data('daterangepicker').startDate.format('YYYY-MM-DD');
    // }    
    // formData.append('pnsTglSK', pnsTglSK);
    // let pnsTMT = '';
    // if($('.pns-tgl-bkn').val()!=""){
    //     pnsTMT = $('.pns-tmt').data('daterangepicker').startDate.format('YYYY-MM-DD');
    // }    
    // formData.append('pnsTMT', pnsTMT);


    $(".daterange").each(function(){
        let varName =  $(this).data('variable');

        let tgl = $(this).val();
        if ($(this).val() != '') {
            tgl =  $(this).data('daterangepicker').startDate.format('YYYY-MM-DD');
        }
        formData.append(varName, tgl);
    });

    $.ajax({
        url : Sitebase.url + '/pegawai/submit',
        type : 'post',
        data : formData,
        cache: false,
        contentType: false,
        processData: false,
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
        frm.find('fieldset').prop('disabled' , false);
    });

    return false;


}).on('click' , '.upload-image' , function( ev ){
    $("#pegawai-image").trigger('click');
}).on('change' , '#pegawai-image' , function( ev ){
    if (ev.target.files[0].size > 1048576*2) {
       Notification.warning('Ukuran gambar terlalu besar, maksimum 2MB.');
       ev.value = '';
       return;
    }

    readURL(this);
}).on('click' , '#add-gaji-berkala' , function( ev ){
    var $elem = $('#table-gaji-berkala tbody tr:first').clone();
    $elem.show();
    $elem.find('input').val('');
    $elem.find('.gbk-id').val(0);
    initDatePicker($elem.find('.daterange'));
    $('#table-gaji-berkala tbody').append($elem);
}).on('click' , '.remove-gaji-berkala' , function( ev ){
    let $elem = $(this).parents('tr');
    Bootbox.confirm({
        title: 'Hapus Data Gaji Berkala',
        message: 'Apakah anda yakin akan menghapus data gaji berkala yang dipilih?',
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-primary'
            },
            cancel: {
                label: 'Cancel',
                className: 'btn-light'
            }
        },
        callback: function (result) {
            if (result) {
                $elem.find('.gbk-delete').val(1);
                $elem.hide('slow');
            }
        }
    });
});

function initDatePicker($elem) {
    $elem.daterangepicker({
        autoUpdateInput: false,
        singleDatePicker: true,
        orientation: "right",        
        applyClass: 'bg-slate-600',
        cancelClass: 'btn-light',
        showDropdowns: true,
        // startDate: moment(),
        minYear: 1901,
        maxYear: parseInt(moment().format('YYYY'),10),
        locale: {
            format: 'DD-MM-YYYY',
            cancelLabel: 'Clear'
        }            
    });    
    $elem.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY'));
    });
    $elem.on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });    
}