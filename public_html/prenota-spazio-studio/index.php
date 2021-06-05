<?php
// Creo sessione o ne recupero una precedentemente creata
session_start();

// Se le variabili di sessione non sono tutte settate reindirizzo l'utente alla pagina di login
if(!isset($_SESSION['id16360004_id']) || !isset($_SESSION['id16360004_loggedin']) || !isset($_SESSION['id16360004_type'])) {
	header("Location:../index.php");
} elseif (isset($_SESSION['id16360004_id']) && $_SESSION['id16360004_loggedin'] == true) {
	// Se il tipo di utente che ha tentato di accedere alla pagina non è uno studente effettua il logout
	if ($_SESSION['id16360004_type'] != "Studente") {
		header("Location:../logout.php");
	}
}

$status = ""; 

// Se il metodo di richiesta è POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Controllo che i dati di sottomissione non siano vuoti
    if (empty($_POST["inputSpazioStudio"]) || empty($_POST["inputData"]) || empty($_POST["inputHours"])) {
		// La variabile $status conterrà un messaggio che confermerà l'avvenuta prenotazione o nel caso di errori indicherà il tipo di errore (errori di connessione o errori a livello di schema relazionale)
        $status = "Informazioni insufficienti per effettuare la prenotazione";
    } else {
		// Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
		require ('../db_connection.php');

		if (empty($connection_error)) {
			// Inserisco la prenotazione dello spazio studio con i dati sottomessi dall'utente
			$query = "INSERT INTO prenotazioni_spazi_studio (spazio_studio, persona, data, ora) VALUES ('" . $_POST["inputSpazioStudio"] ."', '" . $_SESSION['id16360004_id'] ."', '" . $_POST["inputData"] . "', '" . $_POST["inputHours"] . "');";
			$result = mysqli_query($conn, $query);

			if (!$result) {
				$status = "Errore schema database";
			} else { $status = "Prenotazione avvenuta con successo"; }

			mysqli_close($conn);
		} else {
			$status = $connection_error;
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
		
		<title>Prenota spazio studio</title>
		
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
					<a href="../gestisci-prenotazioni/">
						<div class="menu-item">
							Gestisci prenotazioni
						</div>
					</a>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 padding-0">
					<a href="#">
						<div class="menu-item" style="background-color: #660000!important;">
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
							<h4 class="text-center">Seleziona lo spazio studio tra quelli prenotabili, la data e l'orario in cui effettuare la prenotazione</h4>
						</div>
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<div class="form-group row">
								<label for="inputCorso" class="col-sm-2 col-form-label h5 text-md-center">Spazio studio</label>
								<div class="col-sm-9" id="studySpaces">
									<script type="text/javascript">
										var spaziStudio = document.getElementById("studySpaces");

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
											if (this.readyState == 4 && this.status == 200) {
												spaziStudio.insertAdjacentHTML("beforeend", this.responseText);
											}
										};

										// Creo richiesta di tipo POST nei confronti dello script
										xmlhttp.open("POST", "getStudySpaces.php", true);
										// Setto i valori dell'header della richiesta
										xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
										xmlhttp.send();
									</script>
								</div>
								<div class="col-sm-1"></div>
							</div>
							<div class="form-group row" style="display:none;" id="daysContainer">
								<label for="inputData" class="col-sm-2 col-form-label h5 text-md-center">Data</label>
								<div class="col-sm-9" id="days">
								</div>
								<div class="col-sm-1"></div>
							</div>
							<div class="form-group row" style="display:none;" id="hoursContainer">
								<label for="inputHours" class="col-sm-2 col-form-label h5 text-md-center">Ora</label>
								<div class="col-sm-9" id="hours">
								</div>
								<div class="col-sm-1"></div>
							</div>
							<div class="text-center mt-5 mb-2" style="display:none;" id="reservation">
								<button type="submit" class="btn my-button">Effetta prenotazione</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
		/* Funzione attivata al cambiamento del valore del select con id='inputSpazioStudio', utilizzata per ottenere i spazi studio prenotabili
		    e alla ricezione li inserisco all'interno del div con id='inputData' */
		function getDays() {

			var choice = document.getElementById("inputSpazioStudio");
			var spazioStudio = choice.value;

			document.getElementById("hours").innerHTML = ""; 
			document.getElementById("hoursContainer").style.display = "none";

			if (spazioStudio.length != 0)
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
						// Altrimenti significa che ho ottenuto almeno una data relativa a uno spazio studio 
						else { content = "<select class='form-control' id='inputData' name='inputData' onchange='getHours()' required>" + this.responseText + "</select>"; }
						
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
				xmlhttp.send("spazio_studio="+spazioStudio);
				// Se l'option scelto ha value == '' rimuovi i giorni caricati precedentemente, le ore e nascondi il div contenitore
			} else { 
				document.getElementById("days").innerHTML = ""; 
				document.getElementById("daysContainer").style.display = "none";

				document.getElementById("hours").innerHTML = ""; 
				document.getElementById("hoursContainer").style.display = "none";

				document.getElementById("reservation").style.display = "none";
			}
		}

		/* Funzione attivata al cambiamento del valore del select con id='inputData', utilizzata per ottenere gli orari disponibili per un dato
		   spazio studio in una determinata data, alla ricezione li inserisco all'interno del div con id='inputHours' */
		function getHours() {
			var spazioStudio = document.getElementById("inputSpazioStudio").value;
			var data = document.getElementById("inputData").value;

			if (spazioStudio.length != 0 && data.length != 0)
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
							content = "<select class='form-control' id='inputHours' disabled>" + this.responseText + "</select>";
						}
						// Altrimenti significa che ho ottenuto almeno un'orario relativo a un aula studio libera 
						else { content = "<select class='form-control' id='inputHours' name='inputHours' onchange='enableReservation()' required>" + this.responseText + "</select>"; }
						
						var actualHours = document.getElementById("hours");

						actualHours.innerHTML = "";
						actualHours.insertAdjacentHTML("beforeend", content);
						document.getElementById("hoursContainer").style.display = "";
					}
				};

				// Creo richiesta di tipo POST nei confronti dello script
				xmlhttp.open("POST", "getHours.php", true);
				// Setto i valori dell'header della richiesta
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				// Invio la richiesta specificandone i dati
				xmlhttp.send("spazio_studio="+spazioStudio+"&data="+data);
				// Se l'option scelto ha value == '' nascondi il div contenitore
			} else {
				document.getElementById("hours").innerHTML = ""; 
				document.getElementById("hoursContainer").style.display = "none";

				document.getElementById("reservation").style.display = "none";
			}

		}

		// Funzione attivata allo scatenarsi dell'evento on change nel select con id "inputHours", visualizza o nasconde il bottone per la sottomissione del form
		function enableReservation()
		{
			var hours = document.getElementById("inputHours").value;

			if (hours.length != 0)
			{
				document.getElementById("reservation").style.display = "";
			} else {
				document.getElementById("reservation").style.display = "none";
			} 
		}

		</script>

		<script type="text/javascript">
		<?php
			// Se è stato sottomesso il form, avremo la variabile $status settata, se lo è allora visualizzo l'alert con messagio = $status
			if (!empty($status))
			{
				echo "alert('" . $status . "')";
				unset($status);
			}
		?>
		</script>
		
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
		<script src="../bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>