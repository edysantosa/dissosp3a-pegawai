import '../common/common';
import Sitebase from '../library/sitebase';
import * as helper from '../library/helper';
import Notification from '../library/notification';
import URI from 'urijs';
import Modal from '../library/modal';

// import '../limitless/js/plugins/pickers/daterangepicker.js';
import daterangepicker from 'daterangepicker';
import '../../../node_modules/daterangepicker/daterangepicker.css';

import '../limitless/js/plugins/forms/selects/bootstrap_multiselect.js';
import '../limitless/js/plugins/forms/selects/bootstrap_multiselect_id.js';
import '../limitless/js/plugins/forms/inputs/duallistbox/duallistbox.min.js';
import Bootbox from '../limitless/js/plugins/notifications/bootbox.min.js';
import moment from 'moment';
import Store from 'store';
import '../limitless/js/plugins/forms/styling/uniform.min.js';

require( 'datatables.net-bs4' )( window, $ );
require( 'datatables.net-responsive-bs4' )( window, $ );
require( 'datatables.net-select-bs4' )( window, $ );
require( 'datatables.net-buttons-bs4' )( window, $ );
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';

var datatable;

$(function(){
    let currentURI = new URI();
    let uris = URI.parseQuery(currentURI.query());
    for(let name in uris){
        console.log($('[name="'+name+'"]').prop("type"));
        if ($('[name="'+name+'"]').prop("type") == 'select-multiple') {
            if(Array.isArray(uris[name])){
                for(let i in uris[name]){
                    $('select#'+name+'  option[value="'+uris[name][i]+'"]').prop('selected', true);
                }
            }
        } else if($('[name="'+name+'"]').prop("type") == 'select-one') {
            $('select#'+name+'  option[value="'+uris[name]+'"]').prop('selected', true);
        } else if($('input[name="'+name+'"]').length) {
            $('input[name="'+name+'"]').val(uris[name]);
            delete uris[name];
        }
    }
    // Initialize multi select
    $('.multiselect-filtering').multiselect({
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true
    });

    // Initialize date range picker
    // Basic initialization
    $('.daterange-basic').daterangepicker({
        autoUpdateInput: false,
        applyClass: 'bg-slate-600',
        cancelClass: 'btn-light',
        showDropdowns: true,
        startDate: moment('1990-01-01'),
        endDate: moment('1990-01-01'),
        locale: {
            format: 'DD-MM-YYYY',
            cancelLabel: 'Clear'
        }            
    });    
    $('input.daterange-basic').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') +' - '+ picker.endDate.format('DD-MM-YYYY'));
    });
    $('input.daterange-basic').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Initialize data table
    var sortCol = 1;
    var sortDir = 'DESC';
    if (Store.get('bookingPageSort', null) != null) {
        sortCol = Store.get('bookingPageSort').column;
        sortDir = Store.get('bookingPageSort').dir;
    }
    datatable = $('#pegawai-table').DataTable({
        autoWidth: false,
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: 3 },
            { defaultContent: ""}
        ],
        order: [[sortCol, sortDir]],
        dom: '<"datatable-header"fl><"datatable-scroll-lg"t>r<"datatable-footer"ip>',
        language: {
            processing: '<div><i class="icon-spinner2 spinner"></i>  <span class="font-weight-semibold">Memuat Data</span></div>',
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> <select class="custom-select">'+
              '<option value="25">25</option>'+
              '<option value="50">50</option>'+
              '<option value="75">75</option>'+
              '<option value="100">100</option>'+
              '<option value="-1">All</option>'+
              '</select>',
            paginate: { 'first': 'First', 'last': 'Last', 'next': 'Next', 'previous': 'Prev' },          
        },
        lengthMenu: [ 25, 50, 75, 100 ],
        displayLength: 100,
        buttons: {
            dom: {
                button: {
                    tag: 'button',
                    className: ''
                }
            },
            buttons: [{
                extend: 'selectAll',
                className: 'btn btn-xs btn-light legitRipple',
                titleAttr: 'Select all rows.',
                text: 'Select all'
            },{
                extend: 'selectNone',
                className: 'btn btn-xs btn-light legitRipple',
                titleAttr: 'Deselet rows.',
                text: 'Select none'
            }]
        },
        select : {
            style : 'multi+shift'
        },
        processing: true,
        serverSide: true,
        ajax : {
            url : Sitebase.url+'/pegawai/load-data',
            data : function(data){
                // Ambil satu kolom saja dari sort, untuk mempermudah query server side
                let columnSort = data.columns[data.order[0].column];
                data.order = columnSort.name + '-' + data.order[0].dir;
                if (typeof uris.order !== 'undefined' && uris.order !== '' && typeof data.order !== 'string') {
                    data.order = uris.order;
                }
                delete data.columns;

                let delFromUri = [];
                // Masukkan isi form ke data                
                $('form#form-pegawai-filter').find('.form-control').each(function(index, element){
                    let elm = $(element),
                        type = elm.attr('type'),
                        name = elm.attr('name') || "",
                        value = elm.val() || "";

                    if ((name.length > 0 && value.length > 0) && value != 0) {
                        data[name] = value;
                        if (name == 'tgl-lahir') {
                            data['tglLahirFrom'] = elm.data('daterangepicker').startDate.format('YYYY-MM-DD');
                            data['tglLahirTo'] = elm.data('daterangepicker').endDate.format('YYYY-MM-DD');
                        }
                    } else {
                        delFromUri.push(name);
                        delete uris[name];
                    }
                });
                $('form#form-pegawai-filter').find('input[type="checkbox"]').each(function(index, element){
                    let elm = $(element),
                        name = elm.attr('name') || "",
                        value = elm.val() || "";
                    if (elm.prop('checked') == true) {
                        data[name] = true;
                    } else {
                        delFromUri.push(name);
                        delete uris[name];
                    }
                });
                // uris = [];
                for (let name in data) {
                    if(name == 'search'){
                        uris[name] = data[name].value;
                    }else{
                        uris[name] = data[name];
                    }
                }
                delete uris.draw; //Tidak perlu ini di url bar
                currentURI.removeQuery(delFromUri);
                currentURI.setQuery(uris);
            }
        },
        drawCallback: function ( settings ) {
            let api = this.api();
            let rows = api.rows( {page:'current'} ).nodes();
            let last=null;
            let data = api.data();
            let params = api.ajax.params();

            if(params != undefined){
                window.history.pushState({}, 'Pegawai', currentURI.toString());
            }
        },
        rowCallback : function(row, data){
            $(row).attr('id', data.pegawaiId);
        },
        columns : [
            {name:'sequence', data:'sequence', sortable:false},
            {name:'nama', data:'nama', className:'col-nama'},
            {name:'nip', data:'nip', className:'col-nip'},
            {name:'ttl', data: null, className:'col-ttl', render:buildTtlCol},
            {name:'status-kepeg', data: 'statusKepeg.jenisKepegawaian', className:'col-status-kepeg'},
            {name:'pangkat', data: null, className:'col-pangkat', render:buildPangkatCol},
            {name:'jabatan', data: null , className:'col-jabatan', render:buildJabatanCol},
            {name:'action', data:null, sortable:false, className:'text-center', render:buildActionButton}
        ]
    });

    // Uncollapese filter kalau ada valuenya
    if (!helper.isFormEmpty('#form-pegawai-filter')) {
        $('#pegawai-filter-collapse').trigger('click');
    }



}).on("user-select.dt", function (e, dt, type, cell, originalEvent) {
    var $elem = $(originalEvent.target); // get element clicked on
    var tag = $elem[0].nodeName.toLowerCase(); // get element's tag name

    if (!$elem.closest("div.dropdown").length) {
      return; // ignore any element not in the dropdown
    }

    if (tag === "i" || tag === "a" || tag === "button") {
      return false; // cancel the select event for the row
    }
}).on('submit', 'form#form-pegawai-filter', function(e){
    e.preventDefault();
    datatable.draw();
}).on('click', '.page-trigger', function(e){
    let pegawaiIds = helper.getSelectedDataTable(datatable, 'pegawaiId');
    let type = $(this).data('type');
    if(!pegawaiIds.length && type == 'bulk'){
        bootbox.alert({
            title: 'Bulk trigger',
            message: 'Please select at least one pegawai.'
        });
        return;
    }
    switch($(this).data('trigger')) {
      case 'export-pdf':
        exportPdf();
        break;
      case 'print-pdf':
        printPdf();
        break;
      case 'export-excel':
        exportModal();
        break;
      case 'new-pegawai':
        location.href = Sitebase.url + '/pegawai/add';
        break;
    }
}).on('click', '.trigger', function(e){

    let id = $(this).parents('tr').attr('id');

    switch($(this).data('trigger')) {
      case 'delete':
        deletePegawai([id]);
        break;
      case 'edit':
        location.href = Sitebase.url + '/pegawai/edit/' + id;
        break;
    }

});


/**
 * Helper Datatables
 */
function buildTtlCol(cellData, type, rowData){
    return rowData.tempatLahir + ", " + moment(rowData.tglLahir, 'YYYY-MM-DD').format('DD-MM-YYYY');
}
function buildPangkatCol(cellData, type, rowData){
    if (rowData.pangkatTerakhir) {
        return rowData.pangkatTerakhir.pangkat.pangkat + ", " + rowData.pangkatTerakhir.pangkat.golonganRuang;
    } else {
        return '-';
    }
}
function buildJabatanCol(cellData, type, rowData){
    if (rowData.jabatanTerakhir) {
        return rowData.jabatanTerakhir.namaJabatan;
    } else {
        return '-';
    }
}
function buildActionButton(cellData, type, rowData){
    return `<div class="list-icons">
                <div class="dropdown">
                    <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item trigger" data-trigger="edit"><i class="icon-file-upload"></i> Edit</a>
                        <a class="dropdown-item trigger" data-trigger="delete"><i class="icon-cross2"></i> Delete</a>                   
                    </div>
                </div>
            </div>`;
}


/**
 * Fungsi2 trigger
 */

function deletePegawai(pegawaiIds){
    bootbox.confirm({
        title: 'Hapus Pegawai',
        message: 'Apakah anda yakin akan menghapus data pegawai yang dipilih?',
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
                $.ajax({
                    url : Sitebase.url + '/pegawai/submit',
                    type : 'post',
                    dataType : 'json',
                    data : {ids:pegawaiIds,task:'delete'}
                }).fail(function(xhr, status, statusText){
                    if (xhr.status == 401) {
                        window.location.replace(Sitebase.url + '/authentication?redirected=true');
                    }
                    var message = "Unknown error has occured";
                    if( xhr.responseJSON ){
                        message = xhr.responseJSON.message;
                    }

                    Notification.error(message);
                }).done(function(response){
                    Notification.success(response.message);
                    datatable.draw();
                });
            }
        }
    });
}

function exportPdf(){
    window.open(Sitebase.url + '/pegawai/pdf?' + $.param(datatable.ajax.params()) + '&download=1', '_blank');
}
function printPdf(){

    let pdfUrl = Sitebase.url + '/pegawai/pdf?' + $.param(datatable.ajax.params());

    Modal.dialog({
        title       : "Print PDF",
        size        : 'full',
        backdrop    : 'static',
        keyboard    : false,
        message     : '<iframe width="100%" src="' + pdfUrl +'" id="print_frame"></iframe>'
    }).show(function(){
        var me = this;
        let iframehght =  $(document).height() - 400;
        me.element.find('iframe').height(iframehght)
    });
}
function exportExcel(){
    window.open(Sitebase.url + '/pegawai/excel?' + $.param(datatable.ajax.params()) + '&download=1', '_blank');
}

function exportModal(){
    Modal.dialog({
        title       : "Export data ke excel",
        size        : 'medium',
        backdrop    : 'static',
        keyboard    : false,
        message     : '<div class="text-center pb-5"><i class="icon-spinner2 spinner"></i> Loading</div>'
    }).show(function(){
        var me = this;


        me.element.find('.modal-body').html(`
            <form id="form-agent-set-tier" action="#">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="font-weight-semibold">Pilih kolom-kolom yang ingin disertakan pada file Excel</label>
                                <select id="export-select-column" multiple="multiple" class="form-control listbox" autocomplete="off" data-fouc>
                                    <option value="1" data-column="1">Nik</option>
                                    <option value="1" data-column="1">NIP</option>
                                    <option value="3" data-column="3">Nama</option>
                                    <option value="3" data-column="3">Tempat Lahir</option>
                                    <option value="3" data-column="3">Tanggal Lahir</option>
                                    <option value="3" data-column="3">Jabatan</option>
                                    <option value="3" data-column="3">Pangkat/Golongan Ruang</option>
                                    <option value="3" data-column="3">Alamat</option>
                                    <option value="3" data-column="3">Nomor Telpon</option>
                                    <option value="3" data-column="3">Pendidikan Terakhir</option>
                                </select>
                        </div>             
                    </div>
                </div>
            </form>
        `);

        let footer = '<button type="button" class="btn btn-light" id="export-modal-cancel" data-dismiss="modal">Batal</button>';
            footer += '<button type="button" class="btn btn-primary" id="export-modal-save">Download</button>';
        me.element.find('.modal-footer').html(footer);

        me.element.find('.listbox').bootstrapDualListbox();

        me.element.find('#export-modal-save').on('click', function(e){
            let ticketDetail = {
                routeId       : parseInt(me.element.find('#booking-ticket').val()),
                date          : me.element.find('#booking-ticket-date').val(),
                schedule      : parseInt(me.element.find('#booking-ticket-schedule').val()),
                car           : parseInt(me.element.find('.booking-ticket-car:checked').val()),
                isPickup      : me.element.find('#booking-ticket-pickup').prop("checked") ? 1 : 0,
                pickupId      : parseInt(me.element.find('#booking-ticket-area').val()),
                pickupLocation: me.element.find('#booking-ticket-location').val(),
                routeName     : me.element.find('#booking-ticket').find(':selected').html(),
                scheduleName  : me.element.find('#booking-ticket-schedule').find(':selected').html(),
                pickupPrice   : Rupiah.getUnformattedValue(me.element.find('#booking-ticket-pickup-price'))
                // extraCharge   : me.element.find('#booking-ticket-extra-charge').val()
            }
            deferred.resolve(ticketDetail);
            me.element.modal('hide');
        });

        me.element.find('#export-modal-cancel').on('click', function(e){
            deferred.reject('Modal closed');
            me.element.modal('hide');
        });
    });
}