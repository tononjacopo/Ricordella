<?php
session_start();

// Cancella i dati della sessione
$_SESSION = [];

// Cancella il cookie della sessione (opzionale ma consigliato)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"],
              $params["domain"], $params["secure"], $params["httponly"]);
}

// Distrugge la sessione
session_destroy();

// Redirect
header("Location: index.html");
exit;
