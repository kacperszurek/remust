<?php
/**
 * Syntax: <REMUST> - wyświetla raport zapoznania się 
 * 
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Kacper Szurek <kacperszurek@gmail.com>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_remust extends DokuWiki_Syntax_Plugin {
 
    function getInfo(){
        return array(
            'author' => 'Kacper Szurek',
            'email'  => 'kacperszurek@gmail.com',
            'date'   => '2012-04-14',
            'name'   => 'Remust',
            'desc'   => 'Read Must',
            'url'    => 'https://github.com/kacperszurek/remust',
        );
    }
 
   /**
    * Typ pluginu
    * W naszym wypadku zastępujemy nasz tag treścią
    * I nie może się w nim nic zawierać
    **/
    function getType(){
        return 'substition';
    }
 
    /**
     * Nasz plugin nie może być zawarty w innych tagach 
     */
    function getAllowedTypes() {
        return array();
    }
 
    /**
     * Blokowy schemat radzenia sobie z <p>
     * <p>tresc</p>
     * treść z pluginu
     * </p>dalsza część</p>
     **/
    function getPType(){
        return 'block';
    }
 
    
    /**
     * Numer listy sortujacej
     * Nie podmieniamy standardowych
     * @see http://www.dokuwiki.org/devel:parser:getsort_list
     **/
    function getSort(){
        return 999;
    }
 
    /**
     * Rejestrujemy wzór do wyszukiwania w treści podstron
     **/
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('\x3CREMUST\x3E', $mode, 'plugin_remust');
    }
  
    /**
     * Przygotowuje dane do parsowania
     * Ponieważ dane te są cachowanie, nie używamy tej funkcji
     **/
    function handle($match, $state, $pos, &$handler){
        switch ($state) {
          case DOKU_LEXER_ENTER : 
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
            break;
          case DOKU_LEXER_EXIT :
            break;
          case DOKU_LEXER_SPECIAL :
            break;
        }
        return array();
    }
    
    /**
     * Parsowanie znacznika
     **/
    function render($mode, &$renderer, $data) {
        global $ID;
        // Obsługujemy tylko HTML
        if($mode == 'xhtml') {
            $uniqId = uniqid();
            // Wyświetlamy skrypt, który w locie podmieni treść przez ajaxa
            $renderer->doc .= '<br /><div id="remust_syntax_'.$uniqId.'"></div><br />
                                <script type="text/javascript">
                                    jQuery(document).ready(function() {
                                        jQuery.get("'.wl($ID, 'do=remust,opt=syntax', false, '&').'", function(data) {
                                            jQuery("#remust_syntax_'.$uniqId.'").html(data);
                                            remustInitDataTable();
                                        });
                                    });
                                </script>
                              ';
        
            return true;
        }
        return false;
    }
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
