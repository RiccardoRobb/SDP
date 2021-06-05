<?php
// Creo sessione o recupero una sessione precedentemente creata
session_start();

//Unsetto le variabili di sessione	
$_SESSION = array();

// Restituisce 1 se i dati di sessione sono salvati lato client
if (ini_get("session.use_cookies")) {
	
	// Restituisce array contente le informazioni nel cookie
    $params = session_get_cookie_params();
	
	// Setto le info del cookie, resetto phpsessid, imposto il tempo di scadenza al corrente - 420000
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distruggo la sessione
session_destroy();

// Reindirizzo l'utente alla pagina di login
header("Location:index.php");
?>