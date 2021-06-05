<?php
// Creo sessione o ne recupero una precedentemente creata
session_start();

// Se le variabili di sessione non sono tutte settate reindirizzo l'utente alla pagina di login
if(!isset($_SESSION['id16360004_id']) || !isset($_SESSION['id16360004_loggedin']) || !isset($_SESSION['id16360004_type'])) {
	header("Location:../index.php");
	// Altrimenti controllo se per la variabile $_SESSION['id16360004_loggedin'] il valore sia opportunamente settato
} elseif ($_SESSION['id16360004_loggedin'] == true) {
	// Se l'utente non è di tipo Studente ed ha provato ad accedere a una pagina non di sua competenza, distruggo la sessione 
	if ($_SESSION['id16360004_type'] != "Studente") {
		header("Location:../logout.php");
	}
}

$matricola = $_SESSION['id16360004_id'];
?>

<!DOCTYPE html>
<html lang="it">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Non ho inserito i metatag con name title, descriptions, keywords perchè questa pagina non dovrà apparire nei risultati di ricerca.
			metatag con name="robots" utilizzato per dire ai bot crawler di non proseguire l'esplorazione verso i link presenti in 
			questa pagina e di non indicizzare questa pagina -->
		<meta name="robots" content="noindex, nofollow">
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
		
		<title>Home</title>

	</head>
	<body>
		<!-- Per rendere la pagina responsive ho utilizzato il grid system fornitoci dal framework boostrap -->
		<div class="container-fluid">
			<div class="row text-center">
				<div class="col-md-12 padding-0">
					<a href="#">
						<div class="menu-item" style="background-color: #660000!important;">
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
				<div class="offset-md-2 col-md-8 offset-md-2 main-panel" id="status">
					<script type="text/javascript">
					// Stampa l'oggetto date passato in input nel fromato dd/mm/yyyy
					function printDate(date)
					{
						return ("0" + date.getDate()).slice(-2) + "/" +  ("0" + (date.getMonth() + 1)).slice(-2) + "/" + date.getYear().toString().slice(-2);
					}
					
					// Prendo gli ultimi due caratteri della matricola
					var id = parseInt('<?php echo $matricola;?>'.slice(-2));
					
					var today = new Date();
					
					// Ottengo a partire dalla data di oggi il numero di questa settimana
					var weekNumber = Math.ceil((today.getDate()+1) / 7);
					// Vedo se la settimana attuale è pari o dispari
					var even = weekNumber % 2 == 0 ? true : false;
					var range = even ? "00-49" : "50-99";
								
					// Ottengo la data di lunedì di questa settimana
					var monday = today.getDate() + (8 - today.getDay());
					var mondayDate = new Date();
					mondayDate.setDate(monday);

					var saturdayDate = new Date();
					// Ottengo la data di sabato di questa settimana a partire da quella di lunedì 
					saturdayDate.setDate(mondayDate.getDate() + 5);
					saturdayDate.setMonth(mondayDate.getMonth());
					
					var endDate = new Date();
					// Ottengo la data di fine di apertura delle prenotazioni (per la settimana seguente)
					endDate.setDate(mondayDate.getDate() - 2);
					endDate.setMonth(mondayDate.getMonth());
					
					var startDate = new Date();
					// Ottengo la data di inizio di apertura delle prenotazioni a partire da quella di fine
					startDate.setDate(endDate.getDate() - 5);

					var content = "";

					// Condizioni necessarie per poter prenotare 
					if (today >= startDate && today <= endDate && (id < 50 && even || id >= 50 && !even))
					{
						content = '<div class="alert alert-success" role="alert"><h4 class="alert-heading">Sei abilitato ad effettuare prenotazioni.';
					} else {
						content = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">Non sei abilitato ad effettuare prenotazioni dei tuoi corsi.';
					}
					
					content += '</h4><p>Le prenotazioni delle lezioni in aula nella settimana ' + printDate(mondayDate) + " - " + printDate(saturdayDate) + ' saranno disponibili dal ' + printDate(startDate) + " al " + printDate(endDate) + '<hr><p class="mb-0">Saranno attive per i numeri di matricola con le ultime due cifre comprese tra ' + range + '</p></div>';
					document.getElementById("status").insertAdjacentHTML("beforeend", content);
					</script>
				
					
					<!--
					Messaggio informativo preso da prodigit (attraverso il quale abbiamo compreso i criteri per essere abilitati ad effettuare prenotazioni)

					Le prenotazioni delle lezioni in aula nella settimana 22/03/2021 - 27/03/2021 saranno disponibili dal 17/03/2021 al 20/03/2021
					Saranno attive per i numeri di matricola con le ultime due cifre comprese tra 50-99
					
					<div class="alert alert-success" role="alert">
						<h4 class="alert-heading">Sei abilitato ad effettuare prenotazioni.</h4>
						<p>Le prenotazioni delle lezioni in aula nella settimana 15/03/2021 - 20/03/2021 saranno disponibili dal 10/03/2021 al 13/03/2021.</p>
						<hr>
						<p class="mb-0">Saranno attive per i numeri di matricola con le ultime due cifre comprese tra 00-49</p>
					</div>
					-->
				</div>
			</div>
		</div>
		
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
		<script src="../bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>