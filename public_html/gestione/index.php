<?php
// Creo sessione o ne recupero una precedentemente creata
session_start();

// Variabile di appoggio utilizzata per capire se dover settare il contenuto della variabile di sessione ($_SESSION["insegnamenti"])
$toFill = false;

// Se le variabili di sessione non sono tutte settate reindirizzo l'utente alla pagina di login
if(!isset($_SESSION['id16360004_id']) || !isset($_SESSION['id16360004_loggedin']) || !isset($_SESSION['id16360004_type'])) {
	header("Location:../index.php");
} elseif (isset($_SESSION['id16360004_id']) && $_SESSION['id16360004_loggedin'] == true) {
	// Se il tipo di utente che ha tentato di accedere alla pagina non è un Docente effettua il logout
	if ($_SESSION['id16360004_type'] != "Docente") {
		header("Location:../logout.php");
	} else {
		// Se la variabile di sessione degli insegnamenti tenuti da un docente non è settata la inzializzo e setto a true $toFill una variabile di appoggio 
		if (!isset($_SESSION["insegnamenti"])) { 
			$_SESSION["insegnamenti"] = array(); 
			$toFill = true; 
		}
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
		
		<title>Gestione</title>
		
	</head>
	<body>
		<!-- Per rendere la pagina responsive ho utilizzato il grid system fornitoci dal framework boostrap -->
		<div class="container-fluid">
			<div class="row text-center">
				<div class="col-md-12">
					<a href="#">
						<div class="menu-item" style="background-color: #660000!important;">
							Visualizza prenotazioni
						</div>
					</a>
				</div>
				
				<div class="col-md-12">
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
							<h4 class="text-center">Seleziona l'insegnamento, la data e l'ora di cui visualizzare le prenotazioni</h4>
						</div>
						<div class="form-group row">
							<label for="inputTeaching" class="col-sm-2 col-form-label h5 text-md-center">Insegnamento</label>
							<div class="col-sm-9" id="teachings">
								<!-- Ottengo dalla base di dati gli insegnamenti tenuti dal docente e se necessario li inserisco nella variabile di sessione $_SESSION["insegnamenti"] (cioè se $toFill == true) -->
								<?php
								require ('../db_connection.php');
								$response = "<select class='form-control' required disabled>";

								if (empty($connection_error)) {
									// Per ogni insegnamento tenuto dal docente ne prendo il codice, la denominazione e il nome del corso di cui fa parte
									$query = "Select insegnamenti.codice, insegnamenti.denominazione, corsi.denominazione from corsi, insegnamenti where docente = '" . $_SESSION['id16360004_id'] . "' and corsi.codice = insegnamenti.corso;";
									$result = mysqli_query($conn, $query);

									if ($result) {
										if (mysqli_num_rows($result) > 0) {
											// Se la query ha restituito almeno un risultato creo l'elemento select a cui associo, options contenenti le informazioni ottenute
											$response =  '<select class="form-control" id="inputTeaching" name="inputTeaching" required onchange="getDays()"><option value=""></option>';
											while ($row = mysqli_fetch_row($result))
											{
												if ($toFill) array_push($_SESSION["insegnamenti"], (int)$row[0]);
												$response .= "<option value='". $row[0] ."'>" . $row[0] . "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;" . $row[1] . "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;" . $row[2] . "</option>";
											}
											$response .= "</select>";
											// Se la query non ha restituito niente allora aggiungo un option in cui avverto l'utente
										} else { $response .= "<option value=''>Non risultano insegnamenti tenuti</option></select>"; }
										mysqli_free_result($result);

										// Se ci sono stati problemi di sottomissione della query allora inserisco un messaggio di errore
									} else { $response .= "<option value=''>Errore nello schema della base di dati</option></select>"; }
									
									$toFill = false;

									// Se ci sono stati problemi di connessione visualizzo un messaggio di errore
								} else $response .= "<option value=''>" . $connection_error . "</option></select>";

								echo $response;
								?>
								</select>
							</div>
							<div class="col-sm-1"></div>
						</div>
						<div class="form-group row" style="display:none;" id="daysContainer">
							<label for="inputData" class="col-sm-2 col-form-label h5 text-md-center">Data</label>
							<div class="col-sm-9" id="days">
							</div>
							<div class="col-sm-1"></div>
						</div>
						<div class="form-group mt-5" style="display:none;" id="reservationsContainer">
							<div id="numberOfReservations">
							</div>
							<div class="mt-3" style="overflow-x: auto;">
								<table class="table table-hover">
									<thead class="my-thead">
										<tr>
											<th>Id</th>
											<th>Studente</th>
											<th>Posto</th>
										</tr>
									</thead>
									<tbody id="reservations">
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
		/* Funzione attivata al cambiamento del valore del select con id='inputTeaching', utilizzata per ottenere i giorni di lezione di un insegnamento
			e alla ricezione li inserisco all'interno del div con id='daysContainer' */
		function getDays() {

			var choice = document.getElementById("inputTeaching");
			var insegnamento = choice.value;

			document.getElementById("reservations").innerHTML = ""; 
			document.getElementById("reservationsContainer").style.display = "none";

			if (insegnamento.length != 0)
			{
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
						var content = "";
						// Se il numero di option == 1
						if (this.response.split("value").length <= 2)
						{
							// Allora significa che è avvenuto un errore... disabilito il select e mostro il msg relativo all'errore
							content = "<select class='form-control' id='inputData' disabled>" + this.responseText + "</select>";
						}
						// Altrimenti significa che ho ottenuto almeno una data relativa all'insegnamento 
						else { content = "<select class='form-control' id='inputData' name='inputData' onchange='getReservations()' required>" + this.responseText + "</select>"; }
						
						var actualDays = document.getElementById("days");

						actualDays.innerHTML = "";
						actualDays.insertAdjacentHTML("beforeend", content);
						document.getElementById("daysContainer").style.display = "";
					}
				};

				// Creo richiesta di tipo POST nei confronti dello script
				xmlhttp.open("POST", "getDays.php", true);
				// Setto i valori dell'header della richiesta
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				// Invio la richiesta specificandone i dati
				xmlhttp.send("insegnamento="+insegnamento);
			// Se l'option scelto ha value == '' rimuovi i giorni caricati precedentemente e nascondi il div contenitore
			} else { 
				document.getElementById("days").innerHTML = ""; 
				document.getElementById("daysContainer").style.display = "none";
			}
		}

		// Funzione chiamata on change dal select con id = "inputData", setta o resetta la tabella contenente le prenotazioni
		function getReservations() {
			var insegnamento  = document.getElementById("inputTeaching").value;
			var dataAndHour  = document.getElementById("inputData").value;
			document.getElementById("numberOfReservations").innerHTML = ""; 
	
			if (insegnamento.length != 0 && dataAndHour.length != 0)
			{
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}

				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {

						var temp = this.responseText.split("/td");

						// Se è presente almeno una prenotazione mostra il numero di prenotazioni totali
						if (temp.length > 2)
						{
							document.getElementById("numberOfReservations").innerHTML = "Numero di prenotazioni: " + (this.responseText.split("/tr").length - 1);
						}

						document.getElementById("reservations").innerHTML = this.responseText;
						document.getElementById("reservationsContainer").style.display = "";
					}
				};

				xmlhttp.open("POST", "getReservations.php", true);
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xmlhttp.send("insegnamento="+insegnamento+"&data="+dataAndHour);
			} else {
				document.getElementById("reservations").innerHTML = ""; 
				document.getElementById("reservationsContainer").style.display = "none";
			}

		}

		</script>
		
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
		<script src="../bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>