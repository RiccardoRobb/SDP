<?php
/*	DATI DI ACCESSO ALLA BASE DI DATI 

	reperibile al seguente link: https://databases.000webhost.com/
	utilizzando come nome utente $username
	e come password $db_password

*/
$servername = "localhost";
$db_name = "id1636---_sdppia";
$username = "id163---rsa";
$db_password = "]A-----D[^<Fm";

// Variabile contente informazioni relative agli errori in fase di apertura della connessione
$connection_error = "";

/* Per questioni di sicurezza ho anteposto la @ al comando mysqli_connect per non emettere 
a video warning che consentono di ottenere informazioni relative all'accesso alla base di dati 

$conn variabile detentrice della connessione aperta con il db

*/
$conn = @mysqli_connect($servername, $username, $db_password, $db_name);

if (!$conn) {
	$connection_error = "Connessione al database fallita";
} 						
		
?>
