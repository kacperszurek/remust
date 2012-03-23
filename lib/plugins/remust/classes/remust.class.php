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

    /**
     * Dostęp do globalnej zmiennej INFO
     **/
    private $_info = null;

    private $_return = '';
    
    public function __construct($doku, $id, $auth, $info) {
        $this->_doku = $doku;
        $this->_id = $id;
        $this->_auth = $auth;
        $this->_info = $info;

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

            $usersArray = array();
            
            foreach ($users as $key => $val) {
                $usersArray[$key] = array('id' => $key, 'name' => $val['name']);
            }


            //@todo sprawdzanie blokady strony
            //checklock('remust'.$this->_id);

            // Blokujemy stronę
            lock('remust:'.$this->_id);

            $curentUsersArray = array();

            // Sprawdzamy, czy dodano tu już użytkowników
            $isPageExist = page_exists('remust:'.$this->_id);

            if ( $isPageExist ) {
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
            if ( isset($_POST['users']) ) {
                $usersToAdd = explode(",", $_POST['users']);
                
                $currentUsersArray = array();

                foreach ($usersToAdd as $val) {
                    if ( isset($usersArray[$val]) ) {
                        $currentUsersArray[] = array('id' => $val, 'name' => $usersArray[$val]['name']);
                    }
                }
            }


            // Jeśli chcemy zaktualizować użytkowników
            if ( $_POST && checkSecurityToken() && $this->_info['perm'] >= AUTH_EDIT ) {

                // Nazwa zalogowanego użytkownika
                $currentUserLogin = $_SERVER['REMOTE_USER'];

                // Przygotowujemy dane do zapisu
                if ( !isset($_POST['users']) ) {
                     throw new Exception('REMUST Error: 1');
                }

                $usersToAdd = explode(",", $_POST['users']);

                if ($isPageExist) {
                    // Ustawiamy userów którzy istnieją
                    $newUsersArray = array();
                    foreach ($usersToAdd as $val) {
                        $newUsersArray[$val] = 1;
                    }

                    // Tworzymy page content, usuwając usuniętych
                    $pageContent = array();
                    foreach ($explodedPage as $val) {
                        $val = explode('|', $val);
                        if ( isset($newUsersArray[$val[0]]) ) {
                            $pageContent[] = $val[0].'|'.$val[1].'|'.$val[2].( isset($val[3]) ? '|'.$val[3] : '' );
                            unset($newUsersArray[$val[0]]);
                        }
                    }
                    // Na samym końcu dodajemy nowo dodanych
                    foreach ($newUsersArray as $key => $val) {
                        $pageContent[] = $key.'|'.date("d-m-Y H:i:s").'|'.$currentUserLogin;
                    }
                } else {
                    // Dodajemy datę i dodającego
                    $pageContent = array();
                    foreach ($usersToAdd as $val) {
                        // @todo sprawdzenie czy użytkownik istnieje
                        $pageContent[] = $val.'|'.date("d-m-Y H:i:s").'|'.$currentUserLogin;
                    }
                }
                $explodedPage = $pageContent;
                $pageContent = implode("\n", $pageContent);
                
                //Zapisujemy stronę w przestrzeni remust
                saveWikiText('remust:'.$this->_id, $pageContent, $this->_id, true);
                
                // Wyświetlamy wiadomość
                msg($this->_doku->getLang('remust_save_success'), 1);
            }

            // Odblokowujemy stronę
            unlock('remust:'.$this->_id);

            if ( $this->_info['perm'] >= AUTH_EDIT ) {
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

            // Wyświetlamy tabele użytkowników którzy mają potwierdzić przeczytanie
            $this->_return .= '<br /><br /><br />
                               <table cellpadding="0" cellspacing="0" border="0" class="display" id="remust-grid">
                               <thead>
                                    <tr>
                                        <th>Użytkownik</th>
                                        <th>Data poproszenia</th>
                                        <th>Proszący</th>
                                        <th>Data potwierdzenia</th>
                                    </tr>
                               </thead>
                               <tbody>';

            foreach ($explodedPage as $val) {
                $val = explode("|", $val);
                $this->_return .= '<tr>
                                        <td>'.$val[0].'</td>
                                        <td>'.$val[1].'</td>
                                        <td>'.$val[2].'</td>
                                        <td>'.( isset($val[3]) ? $val[3] : '').'</td>
                                   </tr>';
            }

            $this->_return .= '</tbody></table>';
    }
}
