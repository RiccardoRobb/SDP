<?php
	// File invocato attraverso in index.html

	// Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
	require ("../db_connection.php");

	// Se ci sono errori di connessione inserisco in $options un messaggio di errore da visualizzare
	if(!empty($connection_error)){
		die("Connection failed: " . $connection_error);
	}else{
		/* Preso il codice dell'insegnamento e la matricola
           Prendo i giorni e l'ora di inizio in cui si terrà l'insegnamento */
		if(isset($_POST["inputCorso"]) && isset($_POST["studente"])){
			// Prendo le informazioni relative all'aula in cui si terrà l'insegnamento selezionato, con relativa ora di inizio e fine, andando a evitare le lezioni per cui esiste una prenotazione attiva effettuata dall'utente
			$query = "(select aula, edificio, giorno, oraInizio, oraFine from assegnazioni where insegnamento=".$_POST["inputCorso"].") except (select aula, edificio, giorno, oraInizio, oraFine from prenotazioni where studente=".$_POST["studente"]." and DATEDIFF(prenotazioni.data, CURRENT_DATE)>=0)";
			$result = $conn->query($query);
					
			if (!$result) trigger_error('Invalid query: ' . $conn->error);

			$options = "-";
			$splitter = false;
			if ($result->num_rows > 0) {
				$options = "";
				while($row = mysqli_fetch_assoc($result)) {
					if(!$splitter) $splitter = true;
					else $options .= ",";
					$options .= $row["aula"]."#".$row["edificio"]."@".$row["giorno"]." ".$row["oraInizio"]."-".$row["oraFine"];
				}
			}

			// Come output avremo per ogni giorno "aula"#"edificio"@"giorno" "ora inizio"-"ora fine"
			echo $options;
			$options = "";
			$splitter = false;
		}
	}

?>