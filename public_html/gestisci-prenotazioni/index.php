<?php
// Creo sessione o ne recupero una precedentemente creata
session_start();

// Controllo che le variabili di sessione siano già settate
if(!isset($_SESSION['id16360004_id']) || !isset($_SESSION['id16360004_loggedin']) || !isset($_SESSION['id16360004_type'])) {
	header("Location:../index.php");
} elseif (isset($_SESSION['id16360004_id']) && $_SESSION['id16360004_loggedin'] == true) {
	// Se il tipo di utente che ha tentato di accedere alla pagina non è uno studente effettua il logout
	if ($_SESSION['id16360004_type'] != "Studente") {
		header("Location:../logout.php");
	}
}
?>

<!DOCTYPE html>
<html lang="it">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<meta name="author" content="Simone Ruberto, Riccardo Ruberto">

		<!-- Questi tag consentono alla favicon di essere supportata su tutti i browser e su tutte le piattaforme, 
			generati attraverso l'ausilio del sito https://realfavicongenerator.net/ -->		
		<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="../media/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="../media/favicon/favicon-16x16.png">
		<link rel="manifest" href="../media/favicon/site.webmanifest">
		<link rel="mask-icon" href="../media/favicon/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="theme-color" content="#ffffff">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="../bootstrap-4.5.3-dist/css/bootstrap.min.css">
		<!-- CSS Personale -->
		<link rel="stylesheet" href="../my_style.css">
		
		<title>Gestisci prenotazioni</title>

		<script>
		/* Funzione riachiamata on click sul simbolo X delle righe della tabella riguardanti le prenotazioni, preso l'id, contenente il tipo di prenotazione e il codice della prenotazione
		   Controllo che sia possibile l'eliminazione tramite l'invocazione attraverso ajax del file "deleteReservation.php" tramite metodo post
		   Se l'utente conferma di voler cancellare una prenotazione e la prenotazione non riguarda un aula studio/lezione tenuta oggi, allora elimino la riga dalla tabella */
		function myConfirm(id) {
			// Chiedo conferma all'utente di voler effettuare la cancellazione della prenotazione
		  	var outcome = confirm("Sei sicuro di voler annullare la prenotazione?");
		  
			// Se l'utente ha confermato di voler cancellare la prenotazione
		  	if (outcome) {
				// Controllo se la funzionalità XMLHttpRequest sia supportata dal browser
				if (window.XMLHttpRequest) {
					// Codice eseguito da i browser più moderni
					xmlhttp = new XMLHttpRequest();
				} else {
					// Codice eseguito tipicamente dal browser IE di versione 9 o precedente
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}

				// Alla ricezione della risposta dal server
				xmlhttp.onreadystatechange = function() {
					// Se la richiesta è andata a buon fine e ho ricevuto una risposta
					if (this.readyState == 4 && this.status == 200) {
						console.log(this.responseText);
						// In responseText avrò true se la prenotazione è stata eliminata con successo, altrimenti verrà visualizzato un alert di errore
						if (this.responseText == "true")
						{
							location.reload();
						}
						else { window.alert("Non è stato possibile annullare la prenotazione."); }
					}
				};

				xmlhttp.open("POST", "deleteReservation.php", true);
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				console.log(id);
				xmlhttp.send("id="+id);
		 	}
		}
		</script>
	</head>
	<body>
		<!-- Per rendere la pagina responsive ho utilizzato il grid system fornitoci dal framework boostrap -->
		<div class="container-fluid">
			<div class="row text-center">
				<div class="col-md-12 padding-0">
					<a href="../home/">
						<div class="menu-item">
							Home
						</div>
					</a>
				</div>
				
				<div class="col-xs-12 col-sm-6 col-md-3 padding-0">
					<a href="../effettua-prenotazioni/">
						<div class="menu-item">
							Effettua prenotazioni
						</div>
					</a>
				</div>
				
				<div class="col-xs-12 col-sm-6 col-md-3 padding-0">
					<a href="#">
						<div class="menu-item" style="background-color: #660000!important;">
							Gestisci prenotazioni
						</div>
					</a>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 padding-0">
					<a href="../prenota-spazio-studio/">
						<div class="menu-item">
							Prenota uno spazio studio
						</div>
					</a>
				</div>
				
				<div class="col-xs-12 col-sm-6 col-md-3 padding-0">
					<a href="../logout.php">
						<div class="menu-item">
							Logout
						</div>
					</a>
				</div>
			</div>
			<div class="row">
				<div class="offset-md-1 col-md-10 offset-md-1 main-panel">
					<div class="alert" style="background-color: #f2f2f2;">
						<div class="mt-3 mb-5">
							<h4 class="text-center">Visualizza, annulla e scarica la ricevuta delle tue prenotazioni attive</h4>
						</div>
						<div style="overflow-x: auto;">
							<!-- Tabella che conterrà i dati relativi alle prenotazioni attive per aule studio e lezioni
								 Lo studente potrà visualizzarle, scaricare le ricevute e cancellarle -->
							<table class="table table-hover">
								<thead class="my-thead">
									<tr>
										<th>Data</th>
										<th>Orario</th>
										<th>Insegnamento</th>
										<th>Aula</th>
										<th>Edificio</th>
										<th>Posto</th>
										<th>Ricevuta</th>
										<th>Annulla</th>
									</tr>
								</thead>
								<tbody>
								<?php		
									// Importo 'forzatamente' il file per instaurare la connessione con il database... se non presente emette un errore fatale							
									require ('../db_connection.php');
									$error = "";
									$noReservations = 0;
									// Controllo che la vaiabile $connection_error (utilizzata per segnalare la presenza di errori in fase di apertura di connessione con il database) sia vuota
									if (empty($connection_error)) {
										
										#$date = date("Y-m-d", strtotime("2021-03-30"));
										$date = date("Y-m-d");
										
										$query = "select * from prenotazioni where studente = '" . $_SESSION['id16360004_id'] . "' and data >= '" . $date ."' order by data, oraInizio;";
										// Eseguo la query, $conn variabile presente in db_connection.php (detiene la connessione con il db)
										$result = mysqli_query($conn, $query);
										
										if ($result) {
											if (mysqli_num_rows($result) > 0) {
												
												// Se ci sono prenotazioni attive per lezioni relative a date >= della data odierna
												while ($row = mysqli_fetch_row($result)) {
													
													$get_course_name = "select denominazione from insegnamenti where codice = '" . $row[8] . "';";
													$course_name = mysqli_fetch_row(mysqli_query($conn, $get_course_name))[0];
													
													$get_building_name = "select indirizzo from edifici where codice = '" . $row[2] . "';";
													$building_name = mysqli_fetch_row(mysqli_query($conn, $get_building_name))[0];
													
													$seat = explode(".", $row[7]);
													
													// Prese le informazioni degli insegnamenti e delle aule in cui si tengono le lezioni, costruisco la tabella relativa alle prenotazioni attive per lo studente
													echo "<tr><th>" . $row[6] . "</th><td>" . $row[4] . " - " . $row[5] . "</td><td>" . $course_name . "</td><td>" . $row[1] . "</td><td>" . $row[2] . " - " . $building_name . "</td><td>Fila " . $seat[0] . " - Posto " . $seat[1] . "</td><td><a href='../media/sample.pdf' download><img src='../media/download.png'></a></td><td><a onclick='myConfirm(" . $row[0] . "0)'><img src='../media/cancella.png'></a></td></tr>";
												}
																								
											} else { 
												// Incremento $noReservations in modo da indicare che non sono state trovate prenotazioni per lezioni attive per lo studente
												$noReservations++;
											}
										}
										
										mysqli_free_result($result); 

										// Prendo anche le prenotazioni attive dello studente per le aule studio
										$query = "select * from prenotazioni_spazi_studio where persona = '" . $_SESSION['id16360004_id'] . "' and data >= '" . $date ."' order by data;";
										// Eseguo la query, $conn variabile presente in db_connection.php (detiene la connessione con il db)
										$result = mysqli_query($conn, $query);
										
										if ($result) {
											if (mysqli_num_rows($result) > 0) {
												
												// Se ci sono prenotazioni associate a una lezione, aggiungo una riga di distacco tra le prenotazioni relative alle lezioni e quelle relative alle aule studio
												if ($noReservations == 0) { echo "<tr style='background-color: rgb(128, 0, 0);'><td colspan='8'></td></tr>"; }

												while ($row = mysqli_fetch_row($result)) {
																			
													// Prendo le informazioni relative all'ora di inizio e di fine della prenotazione dell'aula studio
													$timestamp = strtotime(substr("0" . $row[4], -5)) + 60*60;
													$endHour = date('H:i', $timestamp);

													if ($endHour[0] == 0) $endHour = substr($endHour, 1);

													// Prendo le informazioni relative all'aula studio con relativo edificio di appartenenza
													$temp = explode(" - ", $row[1]);
													$building = substr($temp[0], 9);
													$temp = explode(" -- ", array_slice($temp, 1)[0]);
													$classroom = $temp[0];

													// Scrivo nella tabella le informazioni relative alle prenotazioni per le aule studio effettuate dallo studente
													echo "<tr><th>" . $row[3] . "</th><td>" . $row[4] . " - " . $endHour . "</td><td>" . "SPAZIO STUDIO" . "</td><td>" . $classroom . "</td><td>" . $building . "</td><td>-</td><td><a href='../media/sample.pdf' download><img src='../media/download.png'></a></td><td><a onclick='myConfirm(" . $row[0] . "1)'><img src='../media/cancella.png'></a></td></tr>";
												}
																								
											} else { 
												// Incremento $noReservations in modo da indicare che non sono state trovate prenotazioni per aule studio attive per lo studente
												$noReservations++;
											}
											mysqli_free_result($result); 
										}

										mysqli_close($conn);
									} else $error = $connection_error;
									
									// Se è avvenuto un errore di connessione inserisco una riga in cui descivo l'errore
									if (!empty($error)) {
										echo "<tr><td colspan='8'><span style='color: #cc0000;'>" . $error ."</span></td></tr>";
									}  
									else if ($noReservations == 2) { 
										// Se non ci sono prenotazioni attive di nessun tipo allora inserisco una riga in cui avverto lo studente
										echo "<tr><td colspan='8'><span style='color: #cc0000;'>Non risultano prenotazioni attive</span></td></tr>"; 
									}
									
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
		<script src="../bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>