<?php
// Creo sessione o ne recupero una precedentemente creata
session_start();

// Controllo che le variabili di sessione siano già settate
if (isset($_SESSION['id16360004_id']) && $_SESSION['id16360004_loggedin'] == true) {
	// Se la variabile di sessione type possiede il valore Studente 
	if ($_SESSION['id16360004_type'] == "Studente") {
		// Reindirizzo lo studente alla pagina /home/index.php
		header("Location:/home/");
	} elseif ($_SESSION['id16360004_type'] == "Docente") {
		//Reindirizzo il docente alla pagina /gestione/index.php
		header("Location:/gestione/");
		// La variabile di sessione contiene un valore inatteso, procedo al logout
	} else { header("Location:logout.php"); }
}

$matricola = $password = "";
$error = "";

// Se il form è stato sottomesso attraverso il metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Controllo che il valore non sia vuoto
    if (empty($_POST["inputMatricola"])) {
        $error = "* La matricola non può essere vuota";
    } else {
		// 'Pulisco' il valore ottenuto dal form...rimuovo gli spazi, i backslashes e converto le parentesi angolari '< >' in entità html
        $matricola = check_input($_POST["inputMatricola"]);
		// Se il valore supera le 9 cifre o se contiene caratteri non numerici 
        if (!preg_match("/^\d{1,9}\z/", $matricola)) {
            $error = "* Matricola invailda";
        } else {
            if (empty($_POST["inputPassword"])) {
                $error = "* La password non può essere vuota";
            } else {
                $password = check_input($_POST["inputPassword"]);
				// Importo 'forzatamente' il file per instaurare la connessione con il database... se non presente emette un errore fatale
                require ('db_connection.php');
				// Controllo che la vaiabile $connection_error (utilizzata per segnalare la presenza di errori in fase di apertura di connessione con il database) sia vuota
                if (empty($connection_error)) {
                    $query = "SELECT password, ruolo FROM utenti WHERE matricola = '" . $matricola . "'";
					// Eseguo la query, $conn variabile presente in db_connection.php (detiene la connessione con il db)
                    $result = mysqli_query($conn, $query);
                    if ($result) {
						// Se il numero di righe nel risultato è == 1
                        if (mysqli_num_rows($result) == 1) {
							// Estrai un array associativo corrispondente alla riga nel risultato
							$row = mysqli_fetch_assoc($result);
                            $correct_password = $row["password"];
							/* 
								Nota importante realtiva alla sicurezza 
							
								Le password all'interno della base di dati non è memorizzata in chiaro ma attraverso il valore hash,
								tale valore è calcolato utilizzando la funzione password_hash().
								Password_hash come algoritmo utilizzato per generare la password utilizza PASSWORD_DEFAULT, 
								il quale fa si che venga utilizzato sempre l'algoritmo hash più sicuro. 
								La stringa ritornata da tale funzione conterrà $<IDENTIFICATIVO DELL'ALGORITMO HASH UTILIZZATO>$<VALORE DEL SALT>$<VALORE HASH>

								password_verify utilizzerà le informazioni memorizzate in $correct_password per 'hashare' la password in chiaro (l'algoritmo e il salt)
								e confronterà il valore generato a partire dalla password in chiaro ($_POST["inputPassword"]) con quello presente in $correct_password,
								ritornerà true se i valori corrispondono

								Maggiori informazioni relative alla funzione password_hash(): 	https://www.php.net/manual/en/function.password-hash.php
								Maggiori informazioni relative alla funzione password_verify(): 	https://www.php.net/manual/en/function.password-verify.php
							*/
                            if (password_verify($password, $correct_password)) {
								// Rigenero il l'id della sessione corrente allo scopo di prevenire attacchi di session hijacking e session fixation.
								session_regenerate_id();
								// Vado a settare i valori delle variabili di sessione 
								$_SESSION['id16360004_loggedin'] = true;
								$_SESSION['id16360004_id'] = $matricola;
								$_SESSION['id16360004_type'] = $row["ruolo"];
								$_SESSION['id16360004_reservations_enable'] = false;								
								if(isset($_SESSION['id16360004_id']) && $_SESSION['id16360004_loggedin'] == true) {
									// Se l'utente è un docente
									if ($_SESSION['id16360004_type'] == 'Docente') {
										// Lo reinderizzo alla pagina di gestione del docente
										header("Location:/gestione/");
									} elseif ($_SESSION['id16360004_type'] == 'Studente') {
										// Controllo che lo studente sia abilitato a effettuare le prenotazioni secondo la politica adottata da prodigit
										$days = array(date('Y-m-d', strtotime('monday this week')), 
														date('Y-m-d', strtotime('tuesday this week')),
														date('Y-m-d', strtotime('wednesday this week')), 
														date('Y-m-d', strtotime('thursday this week')), 
														date('Y-m-d', strtotime('friday this week')));
														
										$today = date('Y-m-d', strtotime('+2 hours'));

										$id = (int)substr($matricola, -2);
										$weekNumber = ceil((((int)date("d")) + 1)/7);
										$even = false;
										
										if ($weekNumber % 2 == 0) { $even = true; }

										if (($id < 50 && $even || $id >= 50 && !$even) && in_array($today, $days))
										{
											// Abilito lo studente ad effettuare le prenotazioni
											$_SESSION['id16360004_reservations_enable'] = true;
										}

										header("Location:/home/");
									} else {
										// Se $_SESSION['id16360004_type'] contiene un valore inatteso distruggo la sessione 
										header("Location:logout.php");
									}
								}
                            } else {
                                $error = "* Password non corretta";
                            }
                        } else {
                            $error = "* La matricola non è associata a nessun account";
                        }
                        mysqli_free_result($result);
                    } else {
                        $error = "Errore schema database";
                    }
					mysqli_close($conn);
                } else {
                    $error = $connection_error;
                }
            }
        }
    }
}

// Funzione utilizzata per prevenire attacchi di ignezione di codice HTML / Javascript (Cross-site Scripting attacks)
function check_input($data) {
    // Rimuovo gli spazi
    $data = trim($data);
    // Rimuovo i backslashes
    $data = stripslashes($data);
    // Converto le parentesi angolari in entità html
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="it">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<meta name="title" content="Sistema di prenotazione posti in aula: Accedi">
		<meta name="description" content="Effettua il login con le tue credenziali e riservati per ogni tua lezione il posto che preferisci.">
		<meta name="keywords" content="prenotazioni, sistema di prenotazione, posti, aula, covid-19, prenotazione">

		<!-- metatag con name="robots" utilizzato per dire ai bot crawler di non proseguire l'esplorazione verso i link presenti in
			questa pagina, ma di indicizzare solamente questa pagina -->
		<meta name="robots" content="index, nofollow">
		<meta name="author" content="Simone Ruberto, Riccardo Ruberto">

		<!-- Questi tag consentono alla favicon di essere supportata su tutti i browser e su tutte le piattaforme, 
			generati attraverso l'ausilio del sito https://realfavicongenerator.net/ -->
		<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/media/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/media/favicon/favicon-16x16.png">
		<link rel="manifest" href="/media/favicon/site.webmanifest">
		<link rel="mask-icon" href="/media/favicon/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="theme-color" content="#ffffff">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="bootstrap-4.5.3-dist\css\bootstrap.min.css">

		<title>Sistema di prenotazione posti in aula: Accedi</title>
		
		<style>
		/* Ho lasciato i css incorporati all'interno di questa pagina perchè sono a uso esclusivo di essa */

		html {height: 100%;}
		body {
			background-image: url("/media/sapienza.webp");
			height: 100%;
			background-size: cover;
		}
		
		.panel {
			background-color: rgba(255, 255, 255, 0.7);
			min-height: 100%;
			padding: 1.5rem;
		}
		
		@media only screen and (max-height: 452px) {
			.panel {
				overflow-y: scroll;
			}
		}
		
		.spacing-text {
			margin-top: 2rem;
			margin-left: 3rem;
		}
		
		.spacing {
			margin-top: 3rem;
			margin-left: 3rem;
			margin-right: 3rem;
		}
		
		/* Ho utilizzato questa media query per consentire una visualizzazione consona a seconda della grandezza dello schermo */
		@media only screen and (max-width: 1140px) {
			.spacing {
				margin: .5rem;
			}
			.spacing-text {
				margin-top: 0.5rem;
				margin-left: 0.5rem;
			}
		}
		
		label {
			font-size: 1.25rem;
		}
		
		.adjust-position {
			position: relative;
			float: right;
			margin-right: 4.5rem;
			top: -2rem;
		}
		
		.my-checkbox {
			position: relative;
			top: 4px;
			left: 2px;
			width: 20px;
			height: 20px;
			margin-right: .5rem;
		}
		
		/* NON FUNZIONA */
		.my-checkbox:checked {
			background-color: #800000!important;
			border-color: #990000!important;
		}

		.my-button {
			padding-top: 0.5rem!important;
			padding-bottom: 0.5rem!important;
			padding: 1rem;
			color: white;
			background-color: #800000;
			border-color: #990000;
		}

		.my-button:hover {
			color: white;
			background-color: #660000;
			border-color: #4d0000;
		}

		/* Utilizzato per nascondere il logo promozionale del sito di webhosting 000webhost */
		a[href="https://www.000webhost.com/?utm_source=000webhostapp&utm_campaign=000_logo&utm_medium=website&utm_content=footer_img"] {
			display:none;
		}
		
		</style>
		<script>

		// Funzione attivata al click sull'elemento checkbox (id=changeStatus) per visualizzare la password in chiaro
		function changeType() {
			
			var element = document.getElementById("inputPassword");
			
			if (element.type == "password")
				element.type = "text";
			else element.type = "password";
			
		}
		</script>
	</head>
	<body>
		<!-- Per rendere la pagina responsive ho utilizzato il grid system fornitoci dal framework boostrap -->
		<div class="container-fluid h-100">
			<div class="row" style="min-height: 100%;">
				<div class="panel col-md-12 offset-lg-7 col-lg-5">
					<header>
						<h1 class="display-4 text-center">SDP Posto in aula</h1>
					</header>
					<hr>
					<section>
						<p class="text-center" style="font-size: 1.25rem;">Accedi con il tuo account InfoStud</p>
						<!-- Ho specificato l'action in questo modo per poter evitare attachi di tipo Cross-site scripting (XSS) -->
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<div class="form-group">
								<div class="row spacing">
									<div class="col-md-12 col-lg-4">
										<label for="inputMatricola">Matricola</label>
									</div>
									<div class="col-md-12 col-lg-8">
										<input type="number" class="form-control" id="inputMatricola" name="inputMatricola" pattern="[0-9]" min="1" required autofocus>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row spacing">
									<div class="col-md-12 col-lg-4">
										<label for="inputPassword">Password</label>
									</div>
									<div class="col-md-12 col-lg-8">
										<input type="password" class="form-control" id="inputPassword" name="inputPassword" required>
									</div>
									<div class="offset-lg-4 col-lg-8 mt-1">
										<input type="checkbox" onclick="changeType()" class="my-checkbox" id="changeStatus">
										<label for="changeStatus" style="font-size: 1rem;">Visualizza password</label>
									</div>
								</div>								
							</div>

							<div class="text-center mt-5">
								<span class="mb-3" style="color: #e60000;display:block;">
									<!-- Visualizza (se presente) l'errore avvenuto durante la validazione dei dati sottomessi attraverso il form -->
									<?php echo $error;?>
								</span>
							
								<button type="submit" class="btn my-button">Accedi</button>
							</div>
						<form>
					</section>
				</div>
			</div>
		</div>
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
		<script src="bootstrap-4.5.3-dist\js\bootstrap.bundle.min.js"></script>
	</body>
</html>