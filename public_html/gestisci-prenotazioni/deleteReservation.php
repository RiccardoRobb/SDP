<?php
    // File invocato attraverso index.html

    // Creo sessione o ne recupero una precedentemente creata
    session_start();

    $response = "false";
    // Controllo che le variabili di sessione siano già settate
    if (isset($_SESSION['id16360004_id']) && isset($_SESSION['id16360004_loggedin']))
    {
		/* Preso il codice della prenotazione e il tipo di prenotazione
           Controllo che esista una prenotazione attiva di un determinato tipo per l'utente
           Controllo che ci sia il codice identificativo della prenotazione, se esiste e la prenotazione non è riferita a oggi allora elimino la prenotazione */
        if (isset($_POST["id"]))
        {
            // Importo 'forzatamente' il file per instaurare la connessione con il database... se non presente emette un errore fatale
            require ('../db_connection.php');
			// Controllo che la vaiabile $connection_error (utilizzata per segnalare la presenza di errori in fase di apertura di connessione con il database) sia vuota
            if (empty($connection_error)) {

                // Estraggo il tipo di prenotazione, se relativa a un aula studio o a una lezione
                $type = (int)$_POST["id"][-1];
                $id = substr($_POST["id"], 0, -1);

                // Se il $type == 0 allora $id è relativo a una prenotazione attiva per una lezione
                if ($type == 0)
                {
                    // Controllo che esista una prenotazione attiva per la lezione 
                    $query = "SELECT data FROM prenotazioni WHERE id = '" . $id . "' and studente = '" . $_SESSION['id16360004_id'] . "';";
                    // Eseguo la query, $conn variabile presente in db_connection.php (detiene la connessione con il db)
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) == 1)
                    {
                        if ($row = mysqli_fetch_row($result))
                        {
                            // Permetto l'eliminazione di una prenotazione per una lezione se e solo se non riguarda una prenotazione attiva per lo stesso giorno
                            if ($row[0] > date("Y-m-d"))
                            {
                                mysqli_free_result($result);
                                $query = "DELETE FROM prenotazioni WHERE id = '" . $id . "' and studente = '" . $_SESSION['id16360004_id'] . "';";
                                $result = mysqli_query($conn, $query);
                                
                                if ($result) {
                                    $response = "true";
                                }
                            }
                        }
                    }
                } else {
                    // Se il $type == 1 allora $id è relativo a una prenotazione attiva per un' aula studio

                    // Controllo che esista una prenotazione attiva per l'aula studio
                    $query = "SELECT data FROM prenotazioni_spazi_studio WHERE id = '" . $id . "' and persona = '" . $_SESSION['id16360004_id'] . "';";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) == 1)
                    {
                        if ($row = mysqli_fetch_row($result))
                        {
                            // Permetto l'eliminazione di una prenotazione di un aula studio se e solo se non riguarda una prenotazione attiva per lo stesso giorno
                            if ($row[0] > date("Y-m-d"))
                            {
                                mysqli_free_result($result);

                                $query = "DELETE FROM prenotazioni_spazi_studio WHERE id = '" . $id . "' and persona = '" . $_SESSION['id16360004_id'] . "';";
                                $result = mysqli_query($conn, $query);
                                
                                if ($result) {
                                    $response = "true";
                                }
                            }
                        }
                    }
                } 
                
                mysqli_close($conn);
            }

            // La variabile $response è utilizzata per sapere se una cancellazione è andata a buon fine
            echo $response;
        } 
    }
    
?>