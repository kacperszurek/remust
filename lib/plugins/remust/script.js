/* DOKUWIKI:include jquery.tokeninput.compress.js */
/* DOKUWIKI:include jquery.dataTables.compress.js */

jQuery(document).ready(function() {
    jQuery("#remust-select-users").tokenInput(
        // Pobieramy dane wcześniej ustawione
        jQuery.data(document.body, 'users'), {
        prePopulate: jQuery.data(document.body, 'current_users'),
        hintText: "Wpisz szukaną osobę",
        noResultsText: "Nie znaleziono",
        searchingText: "Szukam ...",
        searchDelay: 100,
        preventDuplicates: true
    });

    jQuery('#remust-grid').dataTable(
        {
            "oLanguage": {
                "oAria": {
                    "sSortAscending": "Rosnąco",
                    "sSortDescending": "Malejąco"
                },
                "oPaginate": {
                    "sFirst": "Pierwsza",
                    "sLast": "Ostatnia",
                    "sPrevious": "Poprzednia",
                    "sNext": "Następna"
                },
                "sEmptyTable": "Brak danych do wyświetlenia",
                "sInfo": "Wyświetlono rekordy  _START_ - _END_ z _TOTAL_",
                "sInfoEmpty": "Brak danych do wyświetlenia",
                "sLoadingRecords": "Trwa ładowanie, prosze czekać",
                "sSearch": "Szukaj",
                "sLengthMenu": "Pokaż po _MENU_"

            }
        }    
    );
});

