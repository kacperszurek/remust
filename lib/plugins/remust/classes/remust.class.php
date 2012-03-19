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
            
            // Jeśli chcemy zaktualizować użytkowników
            if ( $_POST && checkSecurityToken() ) {

                //@todo sprawdzanie blokady strony
                //checklock('remust'.$this->_id);

                // Blokujemy stronę
                lock('remust'.$this->_id);

                // Nazwa zalogowanego użytkownika
                $currentUserLogin = $_SERVER['REMOTE_USER'];

                // Sprawdzamy, czy dodano tu już użytkowników
                $isPageExist = page_exists('remust:'.$this->_id);

                if ($isPageExist) {
                    //@todo
                } else {
                    // Przygotowujemy dane do zapisu
                    if ( !isset($_POST['users']) ) {
                         throw new Exception('REMUST Error: 1');
                    }

                    $usersToAdd = explode(",", $_POST['users']);

                    // Dodajemy datę i dodającego
                    $pageContent = array();
                    foreach ($usersToAdd as $val) {
                        // @todo sprawdzenie czy użytkownik istnieje
                        $pageContent[] = $val.'|'.date("d-m-Y H:i:s").'|'.$currentUserLogin;
                    }

                    $pageContent = implode("\n", $pageContent);
                    
                    //Tworzymy nową stronę w przestrzeni remust
                    saveWikiText('remust:'.$this->_id, $pageContent, $this->_id, true);
                }

                // Odblokowujemy stronę
                unlock('remust'.$this->_id);
            }

            $usersArray = array();
            
            foreach ($users as $key => $val) {
                $usersArray[$key] = array('id' => $key, 'name' => $val['name']);
            }

            $curentUsersArray = array();

            if ( page_exists('remust:'.$this->_id) ) {
                // Pobieramy stronę
                $rawPage = rawWiki('remust:'.$this->_id);

                //Przetwarzamy istniejące tam dane
                $explodedPage = explode("\n", $rawPage);
                foreach ($explodedPage as $val) {
                    list($userLogin,,) = explode('|', $val);

                    // Czy taki user istnieje
                    if ( isset($usersArray[$userLogin]) ) {
                        $currentUsersArray[] = array('id' => $userLogin, 'name' => $usersArray[$userLogin]['name']);
                    }
                }
            }
            
            // Listę umieszczamy przy użyciu jquery data
            $this->_return .= "
                                <script type='text/javascript'>
                                    jQuery.data( document.body, 'users', ".json_encode(array_values($usersArray))." );
                                    ".(isset($currentUsersArray[0]) ? "jQuery.data( document.body, 'current_users', ".json_encode($currentUsersArray)." );" : '')."
                                </script>
                              "; 
            
            $this->_return .= '
                                <form action="" method="POST">
                                    <input type="hidden" name="sectok" value="'.getSecurityToken().'" />
                                    <input type="text" id="remust-select-users" name="users" />
                                    <input type="submit" value="'.$this->_doku->getLang('remust_select_users').'" />
                                </form>
                              ';
    }
}
