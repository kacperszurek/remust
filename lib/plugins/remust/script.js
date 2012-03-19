/* DOKUWIKI:include jquery.tokeninput.compress.js */
jQuery(document).ready(function() {
    jQuery("#remust-select-users").tokenInput(
        // Pobieramy dane wcześniej ustawione
        jQuery.data(document.body, 'users'), {
        prePopulate: [

        ],
        hintText: "Wpisz szukaną osobę",
        noResultsText: "Nie znaleziono",
        searchingText: "Szukam ...",
        searchDelay: 100,
        preventDuplicates: true
    });
});

