<?php
// File invocato attraverso ajax dalla pagina index.php

// Creo sessione o ne recupero una precedentemente creata
session_start();

// Se le variabili di sessione sono tutte settate
if (isset($_SESSION['id16360004_id']) && isset($_SESSION['id16360004_loggedin']) && isset($_SESSION["insegnamenti"]))
{
    if (isset($_POST["insegnamento"]))
    {
        // Se l'insegnamento per cui è stata fatta richiesta è presente nella variabile di sessione contente gli insegnamenti tenuti dal docente
        if (in_array((int)$_POST["insegnamento"], $_SESSION["insegnamenti"]))
        {
            // Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
            require ('../db_connection.php');

            if (empty($connection_error)) {
                // A partire dall'insegnamento per cui è stata fatta richiesta ottengo i giorni e le relative fasce orarie in cui si svolge tale insegnamento
                $query = "SELECT giorno, oraInizio, oraFine from assegnazioni where insegnamento = '" . $_POST["insegnamento"] . "' ORDER by giorno";
                $result = mysqli_query($conn, $query);
                
                if ($result) {
                    if (mysqli_num_rows($result) > 0) {
                        
                        $response = "<option value=''></option>";
                        
                        // Per ogni insegnamento inserisco in select avente id="days" i giorni con le relative le fasce orarie in cui si tengono le lezioni
                        while ($row = mysqli_fetch_row($result)) {
                            $response .= "<option value='" . $row[0] . "," . $row[1] . "'>" . $row[0] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $row[1] . " - " . $row[2] . "</option>";
                        }
                                                                       
                        // Se la query non ha ottenuto risultati, allora non sono presenti lezioni da visualizzare e inserisco il messaggio di avvertimento nel select avente id="days"
                    } else { $response = "<option value=''>Non risultano lezioni assegnate all'insegnamento</option>"; }
                    mysqli_free_result($result); 
                }
                
                mysqli_close($conn);

                // Se ci sono stati problemi di connessione, imposto un messaggio di errore da mostrare nel select avente id="days"
            } else { $response = "<option value=''>" . $connection_error . "</option>"; }

            echo $response;
        } 
    } 
}
?>