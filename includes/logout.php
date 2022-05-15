<?php



foreach($_COOKIE as $cookieKey => $cookieValue) {
    if(strpos($cookieKey,'wp-postpass_') === 0) {
        // remove the cookie
        setcookie($cookieKey, null, -1);
        unset($_COOKIE[$cookieKey]);
    }
}

var_dump($_COOKIE);
?>