// Setup module
// ------------------------------

var BookingPage = function() {


    //
    // Setup module components
    //

    // Daterange picker
    var _componentDaterange = function() {
        if (!$().daterangepicker) {
            console.warn('Warning - daterangepicker.js is not loaded.');
            return;
        }

        // Basic initialization
        $('.daterange-basic').daterangepicker({
            startDate: moment().subtract(29, 'days'),
            endDate: moment(),
            minDate: '01/01/2014',
            maxDate: '31/12/2019',
            dateLimit: { days: 60 },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens: 'left',            
            applyClass: 'bg-slate-600',
            cancelClass: 'btn-light',
            locale: {
                format: 'MM/DD/YYYY'
            }            
        });
    };

    // Default file input style
    var _componentMultiselect = function() {
        if (!$().multiselect) {
            console.warn('Warning - bootstrap-multiselect.js is not loaded.');
            return;
        }

        // Basic examples
        // ------------------------------
        // Enable filtering
        $('.multiselect-filtering').multiselect({
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true
        });
    };

    // Datatable
    var _componentDatatable = function() {
        if (!$().DataTable) {
            console.warn('Warning - datatables.min.js is not loaded.');
            return;
        }

        // Initialize
        $('.booking-table').DataTable({
            autoWidth: false,
            responsive: true,
            columnDefs: [{ 
                orderable: false,
                width: 100,
                targets: [ 10 ]
            }],            
            order: [[ 0, 'desc' ]],
            dom: '<"datatable-header"fl><"datatable-scroll-lg"t><"datatable-footer"ip>',
            language: {
                search: '<span>Filter:</span> _INPUT_',
                searchPlaceholder: 'Type to filter...',
                lengthMenu: '<span>Show:</span> _MENU_',
                paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
            },
            lengthMenu: [ 25, 50, 75, 100 ],
            displayLength: 25,
            drawCallback: function ( settings ) {
                var api = this.api();
                var rows = api.rows( {page:'current'} ).nodes();
                var last=null;
            }
        });
    };



    //
    // Return objects assigned to module
    //

    return {
        init: function() {
            _componentDatatable();
            _componentMultiselect();
            _componentDaterange();
        }
    }
}();


// Initialize module
// ------------------------------

document.addEventListener('DOMContentLoaded', function() {
    BookingPage.init();
});
