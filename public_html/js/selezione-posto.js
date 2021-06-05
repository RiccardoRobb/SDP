/* File di visualizzazione disposizione posti in aula e rilevamento selezione/deselezione posto.
   Si utilizza un canvas interno al file ../effettua-prenotazione/index.html.
   Presi come attributi il numero di file(lines), posti per fila(seats) e i posti occupati(taken), lo script disegna all'interno del canvas. 

La mappa dell'aula è composta da:
	-Posti non occupati colorati di grigio e selezionabili.
	-Posti occupati colorati di rosso e non selezionabili (la mappa è creata lasciando liberi un posto si e uno no per mantenere il distanziamento).
	-Cattedra colorata di giallo e non selezionabile.
*/

var canvas = document.getElementById("canvas");

// Sostituisco l'elemento canvas con una sua copia, senza copiare i figli in modo da eliminare tutti gli event listeners associati
canvas.replaceWith(canvas.cloneNode(false));
canvas = document.getElementById("canvas");
var ctx = canvas.getContext("2d");
var lines = document.currentScript.getAttribute('lines'); 
var seats = document.currentScript.getAttribute('seats');
var taken = document.currentScript.getAttribute('taken'); 

// Vengono controllati i posti occupati 
if(taken != "-"){
	taken = taken.split(",");

	for(var k=0; k<taken.length; k++){
		var temp = taken[k].split(".");
		taken[k] = [parseInt(temp[0],10)-1,parseInt(temp[1],10)-1];
	}
}

/* Variabili utilizzate per determinare il posizionamento di cerchi(posti) di raggio r e margine a sinistra e destra di 4px.
   Avremo uno spazio dedicato a ogni cerchio di 20px x 20px. */
var tL = 20;
var r = 8;

// Impostazione dimensioni canvas determinate dai posti totali con l'aggiunta di un piccolo "margin-right" di 3px e "margin-bottom" di 50px.
canvas.width = (lines*tL)+3; 
canvas.height = (seats*tL)+50;

var wd = canvas.width; var ht = canvas.height;

// Variabili di posizionamento cerchi nel canvas, il primo cerchio verrà posizionato con centro (xx, yy).
var xx = 11; var yy = 45;

// Posizionamento cattedra di raggio 15 con centro in (width/2, 18).
ctx.beginPath();
ctx.arc(wd/2, 18, 15, 0, 2 * Math.PI);
ctx.fillStyle = "yellow";
ctx.fill();
ctx.stroke()
ctx.closePath();

var aula = []

/* Per ogni fila vado a disegnare i posti e li salvo nell'array 'aula', per ogni posto verrà memorizzata la posizione nel canvas, coordiante (x,y). 
   Viene effettuata la colorazione per il distanziamento e per i posti occupati. */
for (var i = 0; i < lines; i++) {
	var line = []

	for (var j = 0; j < seats; j++){
		ctx.beginPath();
		ctx.arc(xx+(i*tL), yy+(j*tL), r, 0, 2 * Math.PI);
		var check = false;
		if(taken != "-") for(var k=0; k<taken.length; k++) if(taken[k][0] == i && taken[k][1] == j) check = true;
		ctx.fillStyle = ((i+j)%2 == 0 && !check) ? "#e6e2da" : "red";
		ctx.fill();
		ctx.stroke()
		ctx.closePath();

		var x = xx+(i*tL); var y = yy+(j*tL);

		line[j] = [x,y];
	}
	aula[i] = line;
}

// Aggiunta di una funzione evento che si attiva ad ogni click effettuato sul canvas
canvas.addEventListener('click', function(event) {
	// Ho utilizzato offsetX, anzichè layerX, in quanto firefox non ritorna sempre un valore corretto con layerX
	var x = event.offsetX;
	var y = event.layerY;

	var posto = document.getElementById("posto");

	// Per ogni posto controllo il colore corrispondete alle coordinate del click (x,y)
	for(var i = 0; i < lines; i++){
		for(var j = 0; j < seats; j++){

			// Controllo su quale cerchio è avvenuto il click.
			if( x > aula[i][j][0]-r && x < aula[i][j][0]+r && y > aula[i][j][1]-r && y < aula[i][j][1]+r ){ 
				var data = ctx.getImageData(x, y, 1, 1).data;
				var temp = [-1,-1];

				// Se è stato selezionato un posto ma non corrisponde al posto precedentemente selezionato allora annullo il click.
				if( posto.value != "" ){
					var t = posto.value.split(".");
					temp = [parseInt(t[0],10)-1, parseInt(t[1],10)-1];
					
					if(temp[0] != i || temp[1] != j) return;
				}

				if( data[0] != 255 && data[1] != 0 ){

					ctx.beginPath();

					/* Se è stato selezionato un posto 'verde', allora resetto il posto a libero 'grigio' e aggiorno l'elemento html con id=posto.
					   Altrimenti se è stato selezionato un posto libero 'grigio', lo seleziono 'verde' e aggiorno l'elemento html con id=posto. */
					if(data[1] == 255){
						ctx.arc(aula[i][j][0], aula[i][j][1], r-1, 0, 2 * Math.PI);
						ctx.fillStyle = "#e6e2da";
						posto.value = "";
						console.log("Lascia "+(i+1).toString()+"."+(j+1).toString());
					}else{
						ctx.arc(aula[i][j][0], aula[i][j][1], r-2, 0, 2 * Math.PI);
						ctx.fillStyle = "#00ff00";
						posto.value = (i+1).toString()+"."+(j+1).toString();
						console.log("Presa "+(i+1).toString()+"."+(j+1).toString());
					}
					
					ctx.fill();
					ctx.closePath();
				}
			}
		}
	}
});