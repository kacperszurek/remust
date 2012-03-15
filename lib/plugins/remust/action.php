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
     * Zarejestrowanie eventów w dokuwiki
     **/
    public function register(Doku_Event_Handler &$controller) {
        // Dodanie linku do zarządzania remust
       $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'hook_link_add', array());
   
    }

    /**
     * Wyświetlenie linku do zarządania remust na każdej postronie
     **/
    public function hook_link_add(Doku_Event &$event, $param) {
        global $lang;
        global $ID;
        
        $event->data .= '<a href="?do=remust&id='.$ID.'">'.$this->getLang('remust_page_link').'</a>';

    }

}

// vim:ts=4:sw=4:et:
