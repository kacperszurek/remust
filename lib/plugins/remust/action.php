<?php
/**
 * DokuWiki Plugin remust (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Kacper Szurek <kacperszurek@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_remust extends DokuWiki_Action_Plugin {
    
    /**
     * Czy jest to zdarzenie remust
     **/
    private $_isRemust = false;

    /**
     * Wskaźnik do głównej klasy remust
     **/
    private $remust = null;

    /**
     * Zarejestrowanie eventów w dokuwiki
     **/
    public function register(Doku_Event_Handler &$controller) {
        // Dodanie linku do zarządzania remust
        $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'hook_link_add', array());

        // Router obsługujący zdarzenia remust
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'hook_act', array());
        
        // Obsługa widoku zdarzeń remust
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'hook_tpl');
   
    }

    /**
     * Wyświetlenie linku do zarządania remust na każdej postronie
     **/
    public function hook_link_add(Doku_Event &$event, $param) {
        global $lang;
        global $ID;
        global $ACT;
        global $auth;
        global $INFO;

        // Chcemy dodać button tylko przy wyświetlaniu podstrony
        // Jeżeli takowa strona istnieje
        // I nie jest to w przestrzeni remust
        $substr = explode(":", $ID);
        if (count($substr) >= 2 && $substr[0] == 'remust') {
            return;
        }
        
        if ( strcmp($ACT, 'show') != 0 || !page_exists($ID) ) {
            return;
        }

        // Link do dodawania pojawia się tylko gdy ma uprawnienia do edycji
        if ( $INFO['perm'] >= AUTH_EDIT ) {
            $event->data .= '<a href="?do=remust&id='.$ID.'">'.$this->getLang('remust_page_link').'</a>';
        }

        // Jeżeli użytkownik jest zalogowany, sprawdzamy czy nie miał przeczytać tej strony
        if ( !empty($_SERVER['REMOTE_USER']) ) {
            // Czy istnieje strona w przestrzeni dokuwiki
            if ( page_exists('remust:'.$ID) ) {
                lock('remust:'.$ID);
                //Pobieramy tą stronę i sprawdzamy, czy nie ma tam usera
                $pageContent = rawWiki('remust:'.$ID);
                $exploded = explode("\n", $pageContent);

                foreach ($exploded as $key => $val) {
                    $val = explode("|", $val);
                    // Szukamy, do momentu gdy znajdziemy użytkownika
                    if ( strcmp($val[0], $_SERVER['REMOTE_USER']) == 0 ) {
                        // Czy już potwierdził?
                        if ( empty($val[3])  ) {
                            // Jeżeli użytkownik chce teraz potwierdzić przeczytanie podstrony
                            if ( isset($_POST['confirm']) && checkSecurityToken() ) {
                                // Zapisujemy date przeczytania
                                $exploded[$key] = $val[0].'|'.$val[1].'|'.$val[2].'|'.date("d-m-Y H:i:s");

                                // Zapisujemy zmodyfikowaną stronę          
                                $pageContent = implode("\n", $exploded);
                                 saveWikiText('remust:'.$ID, $pageContent, $ID, true);
                                 msg($this->getLang('remust_confirmed_read'), 1);

                            } else {                                
                                // Wyświetlamy formularz potwierdzenia
                                $event->data .= '<br /><br />
                                                '.sprintf($this->getLang('remust_before_read_text'), $val[2]).'
                                                <form action="" method="POST">
                                                <input type="hidden" name="sectok" value="'.getSecurityToken().'" />
                                                <input type="submit" name="confirm" value="'.$this->getLang('remust_confirm_read').'" /> 
                                                </form>
                                                ';
                            }
                        }
                        break;
                    }
                }

                unlock('remust:'.$ID);

            }
        }
    }

    /**
     * Sprawdzanie, czy mamy doczynienia z zadaniami remust
     **/
    public function hook_act(Doku_Event &$event, $param) {
        global $ID;
        global $auth;
        global $INFO;
        global $conf;

        // Zabezpieczamy strony w przestrzeni remust przed edycją
        if ( strcmp(substr($ID, 0, 6), 'remust') == 0 ) {
            // Dzięki temu, nawet admin nie może ich edytować
            $INFO['editable'] = 0;
            $INFO['writable'] = 0;

            // Sprawdzamy uprawnienia użytkownika do normalnej strony
            $perms = auth_quickaclcheck(substr($ID, 7));
            
            if ( $perms >= AUTH_READ ) {
                $INFO['perm'] = AUTH_READ;
            } else {
                $INFO['perm'] = AUTH_NONE;
            }
        }

        if ( is_array($event->data) || strcmp($event->data, 'remust') != 0 ) {
            return;
        }
        
        $this->_isRemust = true;

        // Przekazujemy sterowanie do głównej klasy remust
        require_once(DOKU_PLUGIN.'/remust/classes/remust.class.php');
        $this->_remust = new Remust($this, $ID, $auth, $INFO, $conf);
        
        $event->preventDefault();
        return true;
    }

    public function hook_tpl(Doku_Event &$event, $param) {
        if ( !$this->_isRemust ) {
            return;
        }
        
        // Wyświetlenie wyników na stronie
        echo $this->_remust->getHtml();
        
        $event->preventDefault();
        return true;

    }

}

// vim:ts=4:sw=4:et:
