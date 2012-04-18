<?php

// Tworzenie losowych kont testowych
$users = '';
for ($i=0; $i<1000; ++$i) {
    $md5 = md5(uniqid(true));
    $users .= substr(md5(uniqid()), 0, 6).':'.$md5.':'.substr($md5, 0, 6).' '.substr($md5, 7, 14).':'.substr(md5(uniqid()), 0, 6).'@niepodam.pl:user'."\n";
}

file_put_contents('../conf/users.auth.php', $users, FILE_APPEND);
echo 'OK';
