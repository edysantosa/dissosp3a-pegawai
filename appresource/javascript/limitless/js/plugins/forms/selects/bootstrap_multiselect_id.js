!(function ($, defaults) {
    defaults = $.extend(defaults, {
        allSelectedText  : 'Semua terpilih',
        filterPlaceholder: 'Cari',
        nSelectedText    : 'terpilih',
        nonSelectedText  : 'Semua',
        selectAllText    : 'Pilih semua'
    });
}(jQuery, jQuery.fn.multiselect.Constructor.prototype.defaults));