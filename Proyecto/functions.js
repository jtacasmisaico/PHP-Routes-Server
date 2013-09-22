function goGlobal() {
	document.mainform.imgext.value = "589980 4913700 609000 4928010";
    document.mainform.imgxy.value = "260.5 200.5";
    document.mainform.zoom[1].checked="true";
    document.mainform.elements[3].checked="true";
    document.mainform.submit();
}

function optDibujo(e){
	if(document.getElementById("operation").checked){
		pinta(e);
	}
	else if(document.getElementById("operation2").checked || document.getElementById("operation3").checked){
		pintaCuadrado(e);
	}
}

function Dibuja(x1,y1,x2,y2){
	jg.setColor("#ff0000"); // green
  	jg.drawLine(parseInt(x1), parseInt(y1), parseInt(x2), parseInt(y2)); // co-ordinates related to myCanvas
	jg.paint(); // draws, in this case, directly into the document
}

function drawMode(e){

	document.getElementById("Save_Map").disabled=false;
	document.getElementById("dibuja").value=false;
	//Se reinicia la secuencia del contador
	document.getElementById("contador").value="";
	document.getElementById('myCanvas').style.display='none';
	indice=0;
	if(ventana_sec!=null)
		ventana_sec.close();
	if(ventana_sec2 != null)
		ventana_sec2.close();

	//Solo puede haber una opcion seleccionada
	if(e==1){
		document.getElementById("operation2").checked=false;
		document.getElementById("operation3").checked=false;
		document.getElementById("operation4").checked=false;
	}
	if(e==2){
		document.getElementById("operation").checked=false;
		document.getElementById("operation3").checked=false;
		document.getElementById("operation4").checked=false;
	}
	if(e==3){
		document.getElementById("operation").checked=false;
		document.getElementById("operation2").checked=false;
		document.getElementById("operation4").checked=false;
	}
	if(e==4){
		document.getElementById("operation").checked=false;
		document.getElementById("operation2").checked=false;
		document.getElementById("operation3").checked=false;
		ventana_sec2=window.open("http://localhost/Proyecto/query.html","","width=550,height=250,scrollbars=yes");
	}

	if(document.getElementById("operation").checked || ((document.getElementById("operation2").checked ||document.getElementById("operation3").checked) && document.getElementById("layer").options[1].selected)){
		document.getElementById("dibuja").value=true;
		document.getElementById('myCanvas').style.display='block';

		if(document.getElementById("operation").checked){
			//PROBAR window.showModalDialog !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			document.getElementById("Save_Map").disabled="disabled";
			ventana_sec=window.open("http://localhost/Proyecto/name.html","","width=450,height=120,scrollbars=yes");
		}
	}
	else if(document.getElementById("operation2").checked && !document.getElementById("layer").options[1].selected){

		alert("Error. Layer \"rutes\" must be selected before selection.\nPlease, select layer rutes and press \"Refresh\" button");
		document.getElementById("operation2").checked=false;
		
	}
	else if(document.getElementById("operation3").checked && !document.getElementById("layer").options[1].selected){
		alert("Error. Layer \"rutes\" must be selected before selection.\nPlease, select layer rutes and press \"Refresh\" button");
		document.getElementById("operation3").checked=false;
	}
}

function pinta(e){

	var x1;
	var y1;
	var x2;
	var y2;
	
	//Con el evento onclick no hace falta comprobar la posicion en coordenadas
	if(document.getElementById("dibuja").value=="true"){
		
		coord(e);
		
		//Las coordenadas x estan en las posiciones pares, las y en las impares
		arrayCoord[indice]=x;
		arrayCoord[indice+1]=y;
		indice=indice+2;

		if(document.getElementById("contador").value == ""){
			document.getElementById("coordX1").value = (x);
			document.getElementById("coordY1").value = (y);
		}
		if(document.getElementById("contador").value == "1"){
			document.getElementById("coordX2").value = (x);
			document.getElementById("coordY2").value = (y);
		}
		document.getElementById("contador").value+=1;
		//La secuencia "11" indica que se han recibido las 2 coordenadas que delimitan un segmento
		if(document.getElementById("contador").value == "11"){
			//A partir del primer segmento solo necesita un punto mas para ir completando la ruta
			document.getElementById("contador").value="1";
			x1=document.getElementById("coordX1").value;
			y1=document.getElementById("coordY1").value;
			x2=document.getElementById("coordX2").value;
			y2=document.getElementById("coordY2").value;
	
			Dibuja(x1,y1,x2,y2);
			
			//El segundo vértice es el primero del nuevo segmento
			document.getElementById("coordX1").value=x2;
			document.getElementById("coordY1").value=y2;
		}
	}
  
}

//Cambiar esta  funcion para incluirla en la de arriba
function pintaCuadrado(e){

	var x1;
	var y1;
	var x2;
	var y2;
	var x3;
	var y3;
	var x4;
	var y4;
	
	//Con el evento onclick no hace falta comprobar la posicion en coordenadas
	if(document.getElementById("dibuja").value=="true"){
		
		coord(e);
		
		//Las coordenadas x estan en las posiciones pares, las y en las impares
		arrayCoord[indice]=x;
		arrayCoord[indice+1]=y;
		indice=indice+4;

		if(document.getElementById("contador").value == ""){
			document.getElementById("coordX1").value = (x);
			document.getElementById("coordY1").value = (y);
		}
		if(document.getElementById("contador").value == "1"){
			document.getElementById("coordX2").value = (x);
			document.getElementById("coordY2").value = (y);
		}
		document.getElementById("contador").value+=1;
		//La secuencia "11" indica que se han recibido las 2 coordenadas que delimitan un segmento
		if(document.getElementById("contador").value == "11"){
			//A partir del primer segmento solo necesita un punto mas para ir completando la ruta
			document.getElementById("contador").value="1";
			x1=document.getElementById("coordX1").value;
			y1=document.getElementById("coordY1").value;
			x2=document.getElementById("coordX2").value;
			y2=document.getElementById("coordY2").value;
			x3=x1;
			y3=y2;
			x4=x2;
			y4=y1;

			//Se completa el cuadrilatero con el resto de las coordenadas
			arrayCoord[2]=x1;
			arrayCoord[3]=y2;
			arrayCoord[6]=x2;
			arrayCoord[7]=y1;	

			Dibuja(x1,y1,x3,y3);
			Dibuja(x3,y3,x2,y2);
			Dibuja(x2,y2,x4,y4);
			Dibuja(x4,y4,x1,y1);
			saveMap();
		}
	}
}

function coord(e) {
  	x = e.offsetX?(e.offsetX):e.pageX-document.getElementById("myCanvas").offsetLeft;
   	y = e.offsetY?(e.offsetY):e.pageY-document.getElementById("myCanvas").offsetTop;
}

function saveMap(){

	var coord =new Array();
	coord=georreferenciar(arrayCoord);
	document.getElementById("arraygeo").value=implode(coord);
	if(arrayCoord!=""){
		if(document.getElementById("operation2").checked){
			ventana_sec=window.open("http://localhost/Proyecto/impress.html","","screen.width,screen.height,scrollbars=yes");
		}
		else{
			document.getElementById("formulario").action="/Proyecto/controller.php";
			document.getElementById("formulario").submit();
		}
		
	}
	else{
		alert("Please, make a rute first");
	}
}

function georreferenciar(coordArray){


	var u;
	var v;
	var x1;
	var y1;

	var coordenadas = new Array();

	//El array indexa de 2 en 2 (coordenadas x e y)
	for(var i=0;i<coordArray.length; i+=2){
		//sacamos el valor u (proyeccion en el eje x)
		u=coordArray[i]/500;
		//sacamos el valor u (proyeccion en el eje y)
		v=coordArray[i+1]/400;

		//Sacamos la pryeccion de la x real en el borde inferior del mapa
		x1=u*(x1max-x1min)+x1min;
		//Sacamos la pryeccion de la x real en el borde superior del mapa
		//x2=u*(x2max-x2min)+x2min;
		//Sacamos la pryeccion de la y real en el borde izquierdo del mapa
		y1=v*(y1max-y1min)+y1min;
		//Sacamos la pryeccion de la y real en el borde derecho del mapa
		//y2=v*(y2max-y2min)+y2min;

		coordenadas[i]=x1;
		coordenadas[i+1]=y1;
	}

	return coordenadas;
}

function implode(array){
  var imploded=array[0];
  for (i=1; i<array.length; i++)imploded += '|' + array[i];
  return imploded
}

function pan(direction) {    
    var pansize = 0.1;
	var x, y;

    if(direction == 'n') {
      	x = ([mapwidth]-1)/2.0;
	  	y = ([mapheight]-1) + [mapheight]*pansize - [mapheight]/2.0;
    } else if(direction == 'nw') {
   	  	x = ([mapwidth]-1) + [mapwidth]*pansize - [mapwidth]/2.0;
      	y = ([mapheight]-1) + [mapheight]*pansize - [mapheight]/2.0;
    } else if(direction == 'ne') {
      	x = 0 - [mapwidth]*pansize + [mapwidth]/2.0;
      	y = ([mapheight]-1) + [mapheight]*pansize - [mapheight]/2.0;
    } else if(direction == 's') {
      	x = ([mapwidth]-1)/2.0;
	  	y = 0 - [mapheight]*pansize + [mapheight]/2.0;
    } else if(direction == 'sw') {
    	x = ([mapwidth]-1) + [mapwidth]*pansize - [mapwidth]/2.0;
      	y = 0 - [mapheight]*pansize + [mapheight]/2.0;  
    } else if(direction == 'se') {
      	x = 0 - [mapwidth]*pansize + [mapwidth]/2.0;
      	y = 0 - [mapheight]*pansize + [mapheight]/2.0;
    } else if(direction == 'e') {
      	x = 0 - [mapwidth]*pansize + [mapwidth]/2.0;
      	y = ([mapheight]-1)/2.0;
    } else if(direction == 'w') {
      	x = ([mapwidth]-1) + [mapwidth]*pansize - [mapwidth]/2.0;
      	y = ([mapheight]-1)/2.0;
    }

	document.mainform.zoomdir[1].checked=true;
    document.mainform.imgxy.value = x + " " + y;
    document.mainform.submit();
}
