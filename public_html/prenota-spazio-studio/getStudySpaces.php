<?php
// File invocato attraverso ajax dalla pagina index.php

// Creo sessione o ne recupero una precedentemente creata
session_start();

if (isset($_SESSION['id16360004_id']) && isset($_SESSION['id16360004_loggedin']))
{
    // Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
    require ('../db_connection.php');
    $response = "";

    if (empty($connection_error)) {
        // Prendo la denominazione di tutti i spazi studio che sono prenotabili
        $query = "Select denominazione from spazi_studio where denominazione not in (Select denominazione from spazi_studio where (capienza * 5 * 12) < (Select count(*) from prenotazioni_spazi_studio where data > CURRENT_DATE and spazio_studio = denominazione group by spazio_studio));";
        $result = mysqli_query($conn, $query);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                // Se ho trovato spazi studio prenotabili creo il select "inputSpazioStudio" e per ogni spazio studio trovato aggiungo elementi option
                $response .= "<select class='form-control' id='inputSpazioStudio' name='inputSpazioStudio' required onchange='getDays()'>";
                $response .= "<option value=''></option>";
                while ($row = mysqli_fetch_row($result)) {
                    $response .= "<option value='" . $row[0] . "'>" . $row[0] . "</option>";
                }
                $response .= "</select>";
                
                // Se la query non ha ottenuto risultati, allora non sono presenti spazi studio prenotabili e inserisco un messaggio di avvertimento
            } else { $response = "Non risultano spazi studio attualmente prenotabili"; }
            mysqli_free_result($result); 
        }
        
        mysqli_close($conn);

    } else $error = $connection_error;
    
    // Se ci sono stati problemi di connessione, imposto un messaggio di errore da mostrare in un select appositamente creato
    if (!empty($error)) {
        $response = "<select class='form-control' id='inputSpazioStudio' required disabled><option>" . $error . "</option></select>";
    }
    
    echo $response; 
}  
?>