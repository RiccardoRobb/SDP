<?php
// File invocato attraverso ajax dalla pagina index.php

// Creo sessione o ne recupero una precedentemente creata
session_start();

// Se le variabili di sessione sono settate
if (isset($_SESSION['id16360004_id']) && isset($_SESSION['id16360004_loggedin']))
{
    // Se i dati richiesti per eseguire lo script sono settati
    if (isset($_POST["spazio_studio"]) && isset($_POST["data"]))
    {
        // Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
        require ('../db_connection.php');
        $response = "";

        if (empty($connection_error)) {
            
            $hours = array("08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00");
            $now = date('Y-m-d');

            $index = 0;

            // Prendo gli orari >= dell'orario selezionato
            if ($now == $_POST["data"])
            {
                foreach ($hours as $hour) {
                    if ($hour < $now)
                    {
                        unset($hours[$index]);
                    }
                    $index++;
                }
            }

            // Seleziono l'orario di ogni prenotazione per stazi studio che non hanno posti disponibili
            $query = "SELECT prenotazioni_spazi_studio.ora from prenotazioni_spazi_studio where spazio_studio = '" . $_POST["spazio_studio"] . "' and data = '" . $_POST["data"] . "' group by spazio_studio, prenotazioni_spazi_studio.data, prenotazioni_spazi_studio.ora having count(*) = (select capienza from spazi_studio where spazi_studio.denominazione = '" . $_POST["spazio_studio"] . "');";

            $result = mysqli_query($conn, $query);
            
            if ($result) {     
                $response .= "<option value=''></option>";
                
                if (mysqli_num_rows($result) > 0) {
                    $hoursNotAvailable = array();

                    while ($row = mysqli_fetch_row($result)) {
                        if (strlen($row[0]) != 5)
                        {
                            array_push($hoursNotAvailable, "0" . $row[0]);
                        } else {
                            array_push($hoursNotAvailable, $row[0]);
                        } 
                    }

                    // Dagli orari precedentemente presi tolgo gli orari per cui risultano spazi studio concapienza = 0 (non possono essere prenotati)
                    $hours = array_diff_key($hours, $hoursNotAvailable);
                }
                mysqli_free_result($result); 
            } 
            
            mysqli_close($conn);

            // Per ogni orario, creo elementi option da aggiungere al select "inputHours"
            foreach ($hours as $hour) {
                if ($hour[0] == '0') { $hour = substr($hour, 1); }
                $response .= "<option value='" . $hour . "'>" . $hour . "</option>";
            }

            // Se ci sono stati problemi di connessione, imposto un messaggio di errore da mostrare nel select "inputHours"
        } else { $response = "<option value=''>" . $connection_error . "</option>"; }

        echo $response; 
        
    } 
}
?>