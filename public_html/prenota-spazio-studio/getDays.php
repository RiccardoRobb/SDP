<?php
// File invocato attraverso ajax dalla pagina index.html

// Creo sessione o ne recupero una precedentemente creata
session_start();

if (isset($_SESSION['id16360004_id']) && isset($_SESSION['id16360004_loggedin']))
{
    if (isset($_POST["spazio_studio"]))
    {
        // Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
        require ('../db_connection.php');
        $response = "";

        if (empty($connection_error)) {

            // Associo alla data il giorno della settimana relativo
            $days = array(date('Y-m-d', strtotime('monday this week')) => "Lunedì", 
            date('Y-m-d', strtotime('tuesday this week')) => "Martedì", 
            date('Y-m-d', strtotime('wednesday this week')) => "Mercoledì", 
            date('Y-m-d', strtotime('thursday this week')) => "Giovedì", 
            date('Y-m-d', strtotime('friday this week')) => "Venerdì",
            date('Y-m-d', strtotime('monday next week')) => "Lunedì", 
            date('Y-m-d', strtotime('tuesday next week')) => "Martedì", 
            date('Y-m-d', strtotime('wednesday next week')) => "Mercoledì", 
            date('Y-m-d', strtotime('thursday next week')) => "Giovedì", 
            date('Y-m-d', strtotime('friday next week')) => "Venerdì");

            $today = date('Y-m-d');

            foreach ($days as $key => $value) {
                if ($key <= $today)
                {
                    unset($days[$key]);
                }
            }

            $spazio_studio = $_POST["spazio_studio"];

            // Prendo le date, a partire da oggi, associate alle aule studio che hanno posti liberi
            $query = "SELECT prenotazioni_spazi_studio.data from prenotazioni_spazi_studio where spazio_studio = '" . $spazio_studio ."' and data >= CURRENT_DATE group by spazio_studio, prenotazioni_spazi_studio.data having count(*) = (select capienza * 12 from spazi_studio where spazi_studio.denominazione = '" . $spazio_studio ."');";

            $result = mysqli_query($conn, $query);
            
            if ($result) {     
                $response .= "<option value=''></option>";
                
                if (mysqli_num_rows($result) > 0) {

                    $daysNotAvailable = array();

                    while ($row = mysqli_fetch_row($result)) {
                        $daysNotAvailable[$row[0]] = "";
                    }
                    
                    // Per ogni giorno della settimana vedo se è possibile prenotare (posti occupati < posti totali)
                    $days = array_diff_key($days, $daysNotAvailable);
                }
                mysqli_free_result($result); 
            } 
            
            mysqli_close($conn);

            // Mi prendo i soli dati relativi ai 5 giorni successivi (oggi compreso)
            $days = array_slice($days, 0, 5);
        
            // Per ogni giorno creo options da inserire nel select "inputData"
            foreach ($days as $key => $value) {
                $response .= "<option value='" . $key . "'>" . $days[$key] . "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;" . $key . "</option>";
            }

            // Se ci sono stati problemi di connessione, imposto un messaggio di errore da visualizzare come option del select "inputData"
        } else { $response = "<option value=''>" . $connection_error . "</option>"; }

        echo $response; 
    } 
}
?>