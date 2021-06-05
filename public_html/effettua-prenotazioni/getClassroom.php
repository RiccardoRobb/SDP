<?php
    // File invocato attraverso ajax in index.html

    // Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
	require ("../db_connection.php");

    // Se ci sono errori di connessione termino termino lo script php con un messaggio di errore
	if(!empty($connection_error)){
		die("Connection failed: " . $connection_error);
	}else{
        /* Presa la denominazione dell'aula, l'edificio in cui si trova, il giorno e l'ora della lezione
           Prendo il numero di file e di posti per fila dell'aula */
        if(isset($_POST["aula"]) && isset($_POST["edificio"]) && isset($_POST["giorno"]) && isset($_POST["ora"])){
                // Prendo le informazioni relative al numero di file e i posti per fila per l'aula selezionata
                $query = "select numeroDiFile, numeroDiPostiPerFila from aule where denominazione='".$_POST["aula"]."' and ubicazione='".$_POST["edificio"]."'";
                $result = $conn->query($query);
                        
                if (!$result) trigger_error('Invalid query: ' . $conn->error);
            
                // Per l'aula selezionata prendo "numero di file"#"numero di posti per fila"
                $options = "";
                $splitter = false;
                if ($result->num_rows > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        if(!$splitter) $splitter = true;
                        else $options .= ",";
                        $options .= $row["numeroDiFile"]."#".$row["numeroDiPostiPerFila"];
                    }
                }

                $data = date("Y-m-d");
                $shortDay = "";
                switch($_POST["giorno"]){
                    case "Lunedì": $shortDay = "Mon";
                        break;
                    case "Martedì": $shortDay = "Tue";
                        break;
                    case "Mercoledì": $shortDay = "Wed";
                        break;
                    case "Giovedì": $shortDay = "Thu";
                        break;
                    case "Venerdì": $shortDay = "Fri";
                        break;
                }

                // Prendo la data relativa al giorno della settimana selezionato
                $incrDays = 1;
                while(date('D', strtotime($data)) != $shortDay){
                    $data = date('Y-m-d', strtotime("+".$incrDays." day"));
                    $incrDays++;
                }
            
                // Prendo anche i posti occupati per la lezione che si terrà nell'aula nel determinato giorno
                $query = "select posto from prenotazioni where giorno='".$_POST["giorno"]."' and oraInizio='".$_POST["ora"]."' and data='".$data."'";
                $result = $conn->query($query);
                        
                if (!$result) trigger_error('Invalid query: ' . $conn->error);
            
                $options .= "@";
                $splitter = false;
                if ($result->num_rows > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        if(!$splitter) $splitter = true;
                        else $options .= ",";
                        $options .= $row["posto"];
                    }
                }

                // Come output avremo "numero di file"#"numero di posti per fila"@"lista di posti occupati"
                echo $options;
                $options = "";
                $splitter = false;
            }
        }
?>