/* DOKUWIKI:include jquery.tokeninput.compress.js */
/* DOKUWIKI:include jquery.dataTables.compress.js */

function remustInitDataTable() {
    jQuery('.remust-grid').dataTable(
        {
            "oLanguage": {
                "oAria": {
                    "sSortAscending": LANG.plugins.remust['remust_asc'],
                    "sSortDescending": LANG.plugins.remust['remust_desc']
                },
                "oPaginate": {
                    "sFirst": LANG.plugins.remust['remust_first'],
                    "sLast": LANG.plugins.remust['remust_last'],
                    "sPrevious": LANG.plugins.remust['remust_prev'],
                    "sNext": LANG.plugins.remust['remust_next']
                },
                "sEmptyTable": LANG.plugins.remust['remust_nodata'],
                "sInfo": LANG.plugins.remust['remust_rinfo'],
                "sInfoEmpty": LANG.plugins.remust['remust_nodata'],
                "sLoadingRecords": LANG.plugins.remust['remust_loading'],
                "sSearch": LANG.plugins.remust['remust_search'],
                "sLengthMenu": LANG.plugins.remust['remust_records']

            },
            "bDestroy": true
        }    
    );
}

jQuery(document).ready(function() {
    jQuery("#remust-select-users").tokenInput(
    	DOKU_BASE+'doku.php?do=remust&opt=users', {
        prePopulate: jQuery.data(document.body, 'current_users'),
        hintText: LANG.plugins.remust['remust_type'],
        noResultsText: LANG.plugins.remust['remust_notfound'],
        searchingText: LANG.plugins.remust['remust_searching'],
        searchDelay: 100,
        preventDuplicates: true
    });

    remustInitDataTable();
    
});

