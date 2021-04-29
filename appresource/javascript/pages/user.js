import '../common/common';
import Sitebase from '../library/sitebase';
import * as helper from '../library/helper';
import Notification from '../library/notification';
import moment from 'moment';
import URI from 'urijs';
import Modal from '../library/modal';
import bootbox from '../limitless/js/plugins/notifications/bootbox.min.js';

require( 'datatables.net-bs4' )( window, $ );
require( 'datatables.net-responsive-bs4' )( window, $ );
require( 'datatables.net-select-bs4' )( window, $ );
require( 'datatables.net-buttons-bs4' )( window, $ );
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';

var datatable;

$(function(){
    let currentURI = new URI();
    let uris = URI.parseQuery(currentURI.query());

    // Initialize data table
    datatable = $('#user-table').DataTable({
        autoWidth: false,
        responsive: true,
        columnDefs: [{ 
            orderable: false,
            width: 100,
            targets: [ 4 ]
        }],            
        order: [[ 2, 'asc' ]],
        dom: '<"datatable-header"fl><"datatable-scroll-lg"t>r<"datatable-footer"ip>',
        language: {
            processing: '<div><i class="icon-spinner2 spinner"></i>  <span class="font-weight-semibold">Loading Data...</span></div>',
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
        displayLength: 25,
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
            url : Sitebase.url+'/user/load-data',
            data : function(data){
                // Ambil satu kolom saja dari sort, untuk mempermudah query server side
                let columnSort = data.columns[data.order[0].column];
                data.order = columnSort.name + '-' + data.order[0].dir;
                if (typeof uris.order !== 'undefined' && uris.order !== '' && typeof data.order !== 'string') {
                    data.order = uris.order;
                }
                delete data.columns;

                // uris = [];
                for (let name in data) {
                    if(name == 'search'){
                        uris[name] = data[name].value;
                    }else{
                        uris[name] = data[name];
                    }
                }
                delete uris.draw; //Tidak perlu ini di url bar
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
                window.history.pushState({}, 'User', currentURI.toString());
            }
        },
        rowCallback : function(row, data){
            $(row).attr('id', data.userId);
        },
        columns : [
            {name:'sequence', data:'sequence', sortable:false},
            {name:'email', data:'email', className:'col-email'},
            {name:'name', data:'name', className:'col-name'},
            {name:'position', data:'group.groupName', className:'col-position', sortable:false},
            {name:'action', data:null, sortable:false, render:buildActionButton}
        ]
    });

}).on("user-select.dt", function (e, dt, type, cell, originalEvent) {
    var $elem = $(originalEvent.target); // get element clicked on
    var tag = $elem[0].nodeName.toLowerCase(); // get element's tag name

    if (!$elem.closest("div.dropdown").length) {
      return; // ignore any element not in the dropdown
    }

    if (tag === "i" || tag === "a" || tag === "button") {
      return false; // cancel the select event for the row
    }
}).on('click', '.page-trigger', function(e){
    let userIds = helper.getSelectedDataTable(datatable, 'userId');
    let type = $(this).data('type');
    if(!userIds.length && type == 'bulk'){
        bootbox.alert({
            title: 'Bulk trigger',
            message: 'Please select at least user.'
        });
        return;
    }

    switch($(this).data('trigger')) {
      case 'new-user':
        location.href = Sitebase.url + '/user/add';
        break;
    }
}).on('click', '.trigger', function(e){

    let id = $(this).parents('tr').attr('id');

    switch($(this).data('trigger')) {
      case 'delete':
        deleteUser([id]);
        break;
      case 'edit':
        location.href = Sitebase.url + '/user/edit/' + id;
        break;
    }

});


/**
 * Helper Datatables
 */
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

function deleteUser(userIds){
    bootbox.confirm({
        title: 'Delete user',
        message: 'Are you sure to delete selected user(s)?',
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
                    url : Sitebase.url + '/user/submit',
                    type : 'post',
                    dataType : 'json',
                    data : {ids:userIds,task:'delete'},
                    beforeSend: function (){
                    }
                }).fail(function(xhr, status, statusText){
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
