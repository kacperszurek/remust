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
        
        // Chcemy dodać button tylko przy wyświetlaniu podstrony
        // Jeżeli takowa strona istnieje
        if ( strcmp($ACT, 'show') == 0 && page_exists($ID) ) {
            $event->data .= '<a href="?do=remust&id='.$ID.'">'.$this->getLang('remust_page_link').'</a>';
        }
    }

    /**
     * Sprawdzanie, czy mamy doczynienia z zadaniami remust
     **/
    public function hook_act(Doku_Event &$event, $param) {
        global $ID;
        global $auth;
        
        if ( is_array($event->data) || strcmp($event->data, 'remust') != 0 ) {
            return;
        }
        
        $this->_isRemust = true;

        // Przekazujemy sterowanie do głównej klasy remust
        require_once(DOKU_PLUGIN.'/remust/classes/remust.class.php');
        $this->_remust = new Remust($this, $ID, $auth);
        
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
