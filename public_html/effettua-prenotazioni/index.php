<?php
// Creo sessione o ne recupero una precedentemente creata
session_start();

// Se le variabili di sessione non sono tutte settate reindirizzo l'utente alla pagina di login
if(!isset($_SESSION['id16360004_id']) || !isset($_SESSION['id16360004_loggedin']) || !isset($_SESSION['id16360004_type'])) {
	header("Location:../index.php");
	// Altrimenti controllo se per la variabile $_SESSION['id16360004_loggedin'] il valore sia opportunamente settato e che siano settate le altre variabili
} elseif (isset($_SESSION['id16360004_id']) && $_SESSION['id16360004_loggedin'] == true && isset($_SESSION['id16360004_reservations_enable'])) {
	// Se l'utente non è di tipo Studente ed ha provato ad accedere a una pagina non di sua competenza, distruggo la sessione 
	if ($_SESSION['id16360004_type'] != "Studente") {
		header("Location:../logout.php");
	}
	// Controllo che l'utente abbia il permesso di effettuare prenotazioni
	if ($_SESSION['id16360004_reservations_enable'] == false) {
		header("Location:../home/");
	}
}

// Importo forzatamente il file per instaurare la connessione con il database... se non presente emette un errore fatale
require ("../db_connection.php");

// Se ci sono errori di connessione inserisco in $options un messaggio di errore da visualizzare
if(!empty($connection_error)){
	$options = array("errore"=>"Errore di connessione");
}else{
	// Prendo le informazioni relative agli insegnamenti prenotabili per il corso frequentato dallo studente
	$query = "select codice,denominazione from insegnamenti where codice in (select insegnamento from assegnazioni)";
	$result = $conn->query($query);

	$options = array();
	if ($result->num_rows > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			// La ridondanza di informazioni è voluta ([chiave]: valore-chiave), in quanto questa ridondanza facilita l'utilizzo e il reperimento di tutti i dati necessari alle varie funzioni js e php
			$options[$row["codice"]] = $row["codice"]." - ".$row["denominazione"];
		}
	}

	// In $options avremo per ogni insegnamento "codice insegnamento"-"denominazione"
}

/* Se è stato sottomesso il form con metodo=POST estraggo i valori necessari per eseguire la query
   In questa fase vengono effettuate operazioni di 'splitting' complesse, quindi se ci fossero dei dati corrotti, verrà restituito un errore
   Difatti un possibile attaccante non conosce il formato di concatenazione richiesto dal server e quindi non sarà in grado di passare tramite POST dati malevoli o errati al database */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$day = explode("$", $_POST["inputData"]);
	$classRoom = explode("#", $day[1]);
	$day = explode(" ", $day[0]);
	$building = $classRoom[1];
	$classRoom = $classRoom[0];
	$hours = explode("-", $day[1]);
	$day = $day[0];
	$startHour = $hours[0];
	$finishHour = $hours[1];
	$idStud = $_SESSION['id16360004_id'];

	$data = date("Y-m-d");
	$shortDay = "";
	switch($day){
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
	
	// Inserisco la nuova prenotazione 
	$query = "insert into prenotazioni (aula, edificio, giorno, oraInizio, oraFine, data, posto, insegnamento, studente) values('".$classRoom."','".$building."','".$day."','".$startHour."','".$finishHour."','".$data."',".$_POST["posto"].",".$_POST["inputCorso"].",".$idStud.")";
	$result = $conn->query($query);

	echo "<script type='text/javascript'>alert('Prenotazione effettuata con successo');</script>";
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
		
		<title>Effettua prenotazioni</title>
		
		<style>

		/* Ho lasciato i css incorporati all'interno di questa pagina perchè a uso escusivo di essa.
		   Questi css permettono la visualizzazione di una scroll bar (se necessaria), per facilitare l'utilizzo della mappa dell'aula  */

		.showScroll::-webkit-scrollbar {
		height: 3px;
		}

		.showScroll::-webkit-scrollbar-track {
		background: #f1f1f1; 
		}
		
		.showScroll::-webkit-scrollbar-thumb {
		background: #888; 
		}
		
		</style>
		<script type="text/javascript">
			// Semplice funzione che disabilita elementi di tipo select
			function disable(id){ document.getElementById(id).disabled = true; }

			/* Funzione invocata onChange per il select "inputCorso"
			   Ogni volta che viene invocata resetto/nascondo il select "inputData", il canvas, lo script e il bottone per effettuare il submit del form */
			function getDays(course){
				var row = document.getElementById("controlRow").className = "form-group row";
				document.getElementById("posto").value = "";
				// Se viene selezionato un corso nullo, nascondo le parti relative al controlRow e al submitRow 
				if(course == ""){
					row = document.getElementById("controlRow").className = "d-none";
					document.getElementById("submitRow").className = "text-center";
				}else{
					document.getElementById("submitRow").className = "text-center mt-5 mb-2";
				}
				var select = document.getElementById("inputData");
				select.innerHTML = "";
				// Viene effettuato il reset descritto in precedenza
				document.getElementById("selection").style = "font-weight:500; display: none;";
				document.getElementById("canvas").style = "display: none;";
				document.getElementById("reservation").style = "display: none;";
				document.getElementById("map").remove();
				var script = document.createElement('script');
				script.id = "map";
				document.body.appendChild(script);

				/* Preso il nome codice dell' insegnamento e la matricola
				   Prendo le date prenotabili */
				$.ajax({
					type: "POST",
					url: "getDays.php",
					data: {inputCorso:course, studente:<?php echo $_SESSION['id16360004_id'] ?>},
					success: function(_days){
						/* Avremo in days "aula"#"edificio"@"giorno" "ora inizio"-"ora fine"
						   Costruisco gli elementi option del select "inputData":
						  		<option value="aula#edificio">"giorno" "ora inizio"-"ora fine"</option> */
						var select = document.getElementById("inputData");
						if(_days != "-"){ 
							var days = _days.split(',');
							select.add(document.createElement("option"));
							for (i in days) {
								var out = days[i].split('@');
								var option = document.createElement("option");
								option.text = out[1];
								option.value = out[0];
								select.add(option);
							}
							if(select.disabled) select.disabled = false;
						}else{
							/* Se in days avremo "-", allora risultano prenotazioni attive effettuate dallo studente per tutti i giorni in cui si tiene l'insegnamento
							   Disabilito il select "inputData" */
							var option = document.createElement("option");
							option.text = "Hai già prenotato le lezioni";
							select.disabled = true;
							select.add(option);
						}
					}
				});
			}

			/* Funzione invocata onChange per il select "inputData"
			   Ogni volta che viene invocata resetto/nascondo il testo "Seleziona il posto in aula tra quelli disponibili", il canvas e il bottone di sottomissione form, se è stato selezionato l'option con value=""
			   Ogni volta che viene invocata setto/visualizzo il testo "Seleziona il posto in aula tra quelli disponibili", il canvas e il bottone di sottomissione form, se è stato selezionato l'option con value!="" */
			function getClassroom(classroom){
				document.getElementById("posto").value = "";
				if(classroom == ""){
					document.getElementById("selection").style = "display: none;";
					document.getElementById("canvas").style = "display: none;";
					document.getElementById("reservation").style = "display: none;";
					document.getElementById("paintRow").className = "d-none";
					return;
				}else{
					document.getElementById("map").remove();
					document.getElementById("selection").style = "font-weight:500;";
					document.getElementById("canvas").style = "margin: auto;";
					document.getElementById("reservation").style = "";
					document.getElementById("paintRow").className = "form-group row";
				}

				var out = classroom.split('#');
				var lines = "";
				var seats = "";
				var select = document.getElementById("inputData");
				var dayTime = select.options[select.selectedIndex].text;
				dayTime = dayTime.split(" ");
				var day = dayTime[0];
				var hour = dayTime[1].split("-")[0];
				var taken = "";
				
				/* Preso il giorno selezionato, l'orario di inizio lezione in una determinata aula di un determinato edificio
				   Prendo il numero di file e i posti per fila */
				$.ajax({
					type: "POST",
					url: "getClassroom.php",
					data: {aula:out[0], edificio:out[1], giorno:day, ora:hour},
					success: function(_dati){
						var dati = _dati.split('@');
						var out = dati[0].split('#');
						lines = out[0];
						seats = out[1];
						taken = dati[1];

						// Visualizzo il canvas e invoco lo script js
						document.getElementById("canvas").style = "display: block; margin: auto;";
						var script = document.createElement('script');
						script.src = "../js/selezione-posto.js";
						script.id = "map";
						script.setAttribute("lines", lines);
						script.setAttribute("seats", seats);
						if(taken == "") taken = "-";
						script.setAttribute("taken", taken);

						// Invoco lo script "../js/selezione-posto.js" per disegnare sul canvas le file e i posti per fila disponibili passando come argomenti lines(file), seats(posti per fila) e taken(posti occupati)
						document.body.appendChild(script); 
					}
				});
			}

			/* Funzione invocata quando si sottomette il form
			   Controlla che sia stato selezionato un posto in aula */
			function myRequired(){
				if(document.getElementById("posto").value == ""){
					alert("Seleziona un posto in aula!");
					return false;
				}
				/* Non vengono controllati i valori selezionati, infatti lato database (vedere POST inizio pagina) vengono effettuate operazioni complesse di 'splitting'.
				   Prima di sottomettere il form imposto il value dell'option del select "inputData" come "'giorno' 'ora inizio'-'ora fine"$"'aula'#'edificio'"
				   Così facendo avrò tutte le informazioni necessarie */
				var classroomData = document.getElementById("inputData");
				var select = classroomData.options[classroomData.selectedIndex];
				classroomData.options[classroomData.selectedIndex].value = select.text + "$" + select.value;
				return true;
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
					<a href="#">
						<div class="menu-item" style="background-color: #660000!important;">
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
				<div class="offset-md-2 col-md-8 offset-md-2 main-panel mb-5">
					<div class="alert" style="background-color: #f2f2f2;">
						<div class="my-3">
							<h4 class="text-center">Seleziona l'insegnamento da prenotare, la data e il posto in aula</h4>
						</div>
						<!-- Controllo che i dati inseriti siano validi tramite la funzione "myRequired" 
							 Ho specificato l'action in questo modo per poter evitare attachi di tipo Cross-site scripting (XSS) -->
						<form onsubmit="return myRequired();" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
							<div class="form-group row" id="initialRow">
								<label for="inputCorso" class="col-sm-3 col-form-label h5 text-md-center">Insegnamento</label>
								<div class="col-sm-6">
									<!-- Nel select "inputCorso" avremo come option le informazioni relative agli insegnamenti prenotabili dallo studente 
										 Associo una funzione "getDays(valore option selezionato)" che aggiorna il select "inputData" ogni volta che viene selezionato un option diverso -->
									<select class="form-control" name="inputCorso" id="inputCorso" onchange="getDays(this.value)" required>
										<?php 
											// Controllo che non ci siano errori di connessione, se ci sono disabilito il select
											// Altrimenti nella variabile $options avremo le informazioni relative ai corsi 
											if(!array_key_exists("errore", $options)){
												echo '<option value=""></option>';
											}else echo '<script type="text/javascript"> disable("inputCorso"); </script>';

											// Se non ci sono errori prendo il contenuto di $options e per ogni corso creo elementi option per il select "inputCorso"
											// In $options avremo elementi chiave-valore del tipo: ["codice insegnamento"]:{"codice insegnamento"-"denominazione"}
											foreach ($options as $key => $value) {
												echo "<option value='".$key."''>".$value."</option>";
											}
										?>
									</select>
								</div>
								<div class="col-sm-3"></div>
							</div>
							<div class="d-none" id="controlRow">
								<label for="inputData" class="col-sm-3 col-form-label h5 text-md-center">Data</label>
								<div class="col-sm-6">
									<!-- Nel select "inputData" avremo come option le informazioni relative ai giorni e all'orario in cui si tiene un insegnamento 
										 Associo una funzione "getClassroom(valore option selezionato)" che aggiorna il canvas invocando lo script "../js/selezione-posto.js" ogni volta che viene selezionato un option diverso -->
									<select class="form-control" name="inputData" id="inputData" onchange="getClassroom(this.value)" required>
										<option value=""></option>
									</select>
								</div>
								<div class="col-sm-3"></div>
							</div>
							<p id="selection" class="text-center mt-5" style="font-weight:500; display: none;">Seleziona il posto in aula tra quelli disponibili</p>
							<div class="d-none" id="paintRow">
								<div class="showScroll" style="width: 100%; height: 100%; overflow-x: scroll; display: block; text-align: center;">
									<!-- Area di disegno utilizzata dallo script "../js/selezione-posto.js" -->
									<canvas id="canvas" width="0px" height="0px"></canvas>
									<!-- Script vuoto che verrà utilizzato per invocare lo script "../js/selezione-posto.js" -->
									<script id="map"></script>
								</div>
								<!-- Elemento per tenere traccia del posto selezionato nell'aula, tramite il click sul canvas -->
								<input type="hidden" id="posto" name="posto" value="">
							</div>
							<div class="text-center" id="submitRow">
								<button type="submit" id="reservation" class="btn my-button" style="display: none;">Effetta prenotazione</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="../bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>