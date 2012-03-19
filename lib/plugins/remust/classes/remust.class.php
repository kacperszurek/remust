<?php

class remust
{
    /**
     * Wskaźnik do dokuwiki
     **/
    private $_doku = null;
    
    /**
     * ID strony
     **/
    private $_id = null;

    /**
     * Dostęp do klasy AUTH
     **/
    private $_auth = null;

    private $_return = '';
    
    public function __construct($doku, $id, $auth) {
        $this->_doku = $doku;
        $this->_id = $id;
        $this->_auth = $auth;

        switch ($_GET['opt']) {
            default:
                // Dodawanie strony do listy do przeczytania 
                $this->_addTo();            
        }
    }
    
    /**
     * Zwraca odpowiedni widok HTML
     **/
    public function getHtml() {
        return $this->_return;
    }

    /**
     * Dodawanie strony do listy do przeczytania
     **/
    private function _addTo() {
            // Należy sprawdzić, czy taka podstrona istnieje
            if ( !page_exists($this->_id) ) {
                throw new Exception(sprintf($this->_doku->getLang('remust_page_not_exist'), html_wikilink($this->_id)));    
            }

            // Pobieramy listę użytkowników
            $users = $this->_auth->retrieveUsers();
            
            // @todo zaznaczanie już wybranych użytkowników

            $usersArray = array();
            
            foreach ($users as $key => $val) {
                $usersArray[] = array('id' => $key, 'name' => $val['name']);
            }

            // Listę umieszczamy przy użyciu jquery data
            $this->_return .= "<script type='text/javascript'>jQuery.data( document.body, 'users', ".json_encode($usersArray)." );</script>"; 
            
            $this->_return .= '<input type="text" id="remust-select-users" />';


    }
}
