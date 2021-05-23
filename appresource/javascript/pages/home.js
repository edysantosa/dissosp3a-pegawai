import '../common/common';
import Sitebase from '../library/sitebase';

$(function(){
    $.ajax({
        url     : Sitebase.url + '/pegawai/home-info',
        type    : 'get',
        dataType: 'json',
    })
    .fail(function(xhr, status, statusText){
        var message = "Unknown error has occured";
        if( xhr.responseJSON ){
            message = xhr.responseJSON.message;
        }
    })
    .done(function(response){
        $(".total-pegawai").fadeOut(function() {
            $(this).html(response.totalPegawai).fadeIn();
        });
        $(".total-pegawai-pns").fadeOut(function() {
            $(this).html(response.totalPegawaiPNS).fadeIn();
        });
        $(".total-pegawai-kontrak").fadeOut(function() {
            $(this).html(response.totalPegawaiKontrak).fadeIn();
        });
        $(".pegawai-pensiun").fadeOut(function() {
            $(this).html(response.totalPegawaiPensiun).fadeIn();
        });
    });
});