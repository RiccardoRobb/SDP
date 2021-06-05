<?php
// File invocato attraverso ajax dalla pagina index.html

// Creo sessione o ne recupero una precedentemente creata
session_start();

// Se le variabili di sessione sono tutte settate
if (isset($_SESSION['id16360004_id']) && isset($_SESSION['id16360004_loggedin']) && isset($_SESSION["insegnamenti"]))
{
    if (isset($_POST["insegnamento"]) && isset($_POST["data"]))
    {
        // Se l'insegnamento per cui è stata fatta richiesta è presente nella variabile di sessione contente gli insegnamenti tenuti dal docente
        if (in_array((int)$_POST["insegnamento"], $_SESSION["insegnamenti"]))
        {
            // Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
            require ('../db_connection.php');
            $response = "";

            if (empty($connection_error)) {
                /* Preso l'insegnamento, il giorno e la data di inizio della lezione
                   Ottengo le informazioni delle prenotazioni, quali id prenotazione, matricola e posto */

                // Array associativo creato per facilitare la ricerca della data associata a un giorno della settimana
                $days = array("Lunedì" => 'monday', "Martedì" => 'tuesday', "Mercoledì" => 'wednesday', "Giovedì" => 'thursday', "Venerdì" => 'friday', "Sabato" => 'saturday', "Domenica" => 'sunday');

                $insegnamento = $_POST["insegnamento"];
                $dataAndHour = explode(",", $_POST["data"]);
                $data = $dataAndHour[0];
                $ora = $dataAndHour[1];

                // Prendo il giorno di questa settimana nel formato Y-m-d corrispondente al giorno contenuto nella variabile $data tradotto in inglese
                $data = date('Y-m-d', strtotime($days[$data] . ' this week'));

                $query = "SELECT id, studente, posto FROM prenotazioni WHERE insegnamento = '" . $insegnamento . "' and data = '" . $data . "' and oraInizio = '" . $ora . "' order by id";
                $result = mysqli_query($conn, $query);
                
                if ($result) {
                    if (mysqli_num_rows($result) > 0) {                      
                        $seat = "";

                        // Andrò a popolare la tabella "reservations" con righe contenenti le informazioni prese dalla query
                        while ($row = mysqli_fetch_row($result)) {
                            $seat = explode(".", $row[2]);
                            $response .= "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>Fila " . $seat[0] . " - Posto " . $seat[1] . "</td></tr>";
                        }
                                             
                        // Se la query non ha ottenuto risultati, allora non ci sono studenti prenotati per la lezione e inserisco il messaggio di avvertimento nella tabella "reservations"
                    } else { $response = "<tr><td colspan='3'>Non risultano prenotazioni attive</td></tr>"; }
                    mysqli_free_result($result); 
                }
                
                mysqli_close($conn);

                // Se ci sono stati problemi di connessione, imposto un messaggio di errore da mostrare nella tabella "reservations"
            } else { $response = "<tr><td colspan='3'>" . $connection_error . "</td></tr>"; }

            echo $response;
        } 
    } 
}
    
?>