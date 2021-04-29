import 'jquery';
import 'pace-progress';
import store from 'store';
import sitebase from '../library/sitebase';
// import Cart from '../library/cart';

// Load limitless template global resource
import '../limitless/css/icons/icomoon/styles.css';
import '../limitless/css/icons/material/icons.css';
import '../limitless/css/bootstrap.min.css';
import '../limitless/css/bootstrap_limitless.min.css';
import '../limitless/css/layout.min.css';
import '../limitless/css/components.min.css';
import '../limitless/css/colors.min.css';
import '../../style/main.css';
// import '../limitless/js/main/jquery.min.js';
import '../limitless/js/main/bootstrap.bundle.min.js';
import '../limitless/js/plugins/loaders/blockui.min.js';
import '../limitless/js/plugins/ui/ripple.min.js';
import '../limitless/js/app.js';

import '../limitless/images/placeholders/placeholder.jpg';
import '../limitless/images/logo_light.png';
import '../limitless/images/favicons/favicon-32x32.png';
import '../limitless/images/favicons/favicon-96x96.png';
import '../limitless/images/favicons/favicon-16x16.png';


import bootbox from '../limitless/js/plugins/notifications/bootbox.min.js';
import Modal from '../library/modal';
import '../library/handlebars-helper';
import '../limitless/js/plugins/forms/validation/validate.min.js';
import * as Rupiah from '../library/rupiah';
import * as helper from '../library/helper';

// sitebase.cart       = new Cart(sitebase.cartField);

if( sitebase.versionstamp != store.get('vstamp' , null) ){
    store.clearAll();
    store.set('vstamp' , sitebase.versionstamp);
}


/**
 *  FUNGSI FUNGSI TEMPLATE LIMITLESS
 */

// Set status sidebar, 1 = open, 2 = closed
if (store.get('sidebar' , null) == 2) {
    $('body').addClass('sidebar-xs');
}


// On sidebar width change
$(document).on('click', '.sidebar-control, .navbar-toggler', function() {
    setTimeout(function () {
        // Resize ulang datatable waktu sidebar dicollapse
        if ($().DataTable) {
            var table = $('.datatable-resize').DataTable();
            table.columns.adjust().responsive.recalc();
        }

        // Set status sidebar, 1 = open, 2 = closed
        if ($('body').hasClass('sidebar-xs')) {
            store.set('sidebar' , 2);
        } else {
            store.set('sidebar' , 1);
        }
    }, 0);
});


/**
 *  END FUNGSI FUNGSI TEMPLATE LIMITLESS
 */

$(function(){

})
.on('hidden.bs.modal', '.modal', function () {
    // FIX MULTIPLE MODAL BUG
    $('.modal:visible').length && $(document.body).addClass('modal-open');
})
.on('click' , '.signout' , function( ev ){
    bootbox.confirm({
        title: 'Logout',
        message: 'Are you sure to logout?',
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
                location.href = sitebase.url + '/authentication/signout';
            }
        }
    });
});
