<?php
/* Copyright 2010 Álvaro Ortega Cabeza & Universidad de Granada
* 
* This file is part of SRS.
*
* SRS is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
include ("rutes.php");
//Change it if you need
putenv("GISBASE=/opt/grass");
putenv("PATH=bash:/home/alvaro/bin:/opt/grass/bin:/opt/grass/scripts");
putenv("LD_LIBRARY_PATH=/opt/grass/lib");
putenv("GISRC=/opt/lampp/htdocs/Proyecto/gisrc");
putenv("GRASS_TRUECOLOR=TRUE");
putenv("GRASS_WIDTH=900");
putenv("GRASS_PNG_COMPRESSION=1");
putenv("HOME=/tmp");
putenv("GIS_LOCK=$$");

//Operacion dibujar rutas
if(isset($_GET["operation"])){

	$coordGeo = explode('|',$_GET["arraygeo"]);

	$ruteName=$_GET["mapName"];

	//Sacamos la proxima categoria
	//Conectamos con la BD adecuada
	system("db.connect driver=dbf database='/opt/lampp/htdocs/Proyecto/spearfish/PERMANENT/dbf/'");
	
	system("echo \"SELECT cat FROM rutes ORDER BY cat ASC\" | db.select>> /opt/lampp/htdocs/Proyecto/cats.txt;");

	$vlineas = file("cats.txt");
	$cat=$vlineas[count($vlineas)-1];
	$cat=$cat+1;

	$archivo='/opt/lampp/htdocs/Proyecto/coordenadas/'.$cat.'.txt';
	fopen($archivo, 'a+'); 

	//Nos aseguramos primero de que el archivo existe y puede escribirse sobre el.
	if (is_writable($archivo)) {

		// $nombre_archivo en modo de adicion.
   		// El apuntador de archivo se encuentra al final del archivo
   		if (!$gestor = fopen($archivo, 'a')) {
       			echo "No se puede abrir el archivo ($archivo)";
         		exit;
   		}

		fputs($gestor,"L");
		fputs($gestor," ");
		fputs($gestor,(count($coordGeo)/2));
		fputs($gestor," ");
		fputs($gestor,"1");
		fputs($gestor,"\n");	
		// Componer el archivo
			
		for($i=0;$i<count($coordGeo); $i+=2){
				
			if($i==0){
				$Xi=$coordGeo[0];
				$Yi=$coordGeo[1];
			}
			fwrite($gestor, $coordGeo[$i]);
			fputs($gestor," ");
			fwrite($gestor, $coordGeo[$i+1]);
			fputs($gestor,"\n");
			if($i==count($coordGeo)-2){
				$Xf=$coordGeo[$i];
				$Yf=$coordGeo[$i+1];
			}	
		}
		fputs($gestor,"1");
		fputs($gestor," ");
		fputs($gestor,$cat);
		fputs($gestor,"\n");

		fclose($gestor);

	} else {
   		echo "No se puede escribir sobre el archivo $archivo";
	} 
	
	//Antes se borra el mapa auxiliar si existe
	system("g.remove vect=rutes_aux;");
	system("g.remove rast=rutes_aux");
	system("g.remove rast=unitario");
	system("g.remove rast=coste");

	//Se crea el mapa vectorial
	system("v.in.ascii -n input=$archivo output=rutes_aux format=standard;");

	//Se calculan los parámetros para posteriormente acualizar la BD con la nueva ruta

	//Longitud
	system("v.report map=rutes_aux option=length units=meters>> /opt/lampp/htdocs/Proyecto/alt.txt;");

	$vlineas = file("alt.txt");
   	
	$sLinea=explode('|',$vlineas[1]);
	$long=substr($sLinea[1],0,strlen($sLinea[1])-1);

	//Se fija la resolucion a 30x30 m
	system("g.region res=30");
	
	//Se rasteriza para poder trabajar con el
	system("v.to.rast input=rutes_aux@PERMANENT output=rutes_aux use=val type=point,line,area;");

	//Altura
	system("r.mapcalc \"unitario=if(rutes_aux==1,elevation.dem,null())\"");

	//Se crea la superficie de coste
	system("r.cost unitario output=coste coordinate=$Xi,$Yi");	

	system("r.stats input=coste fs=space nv=* nsteps=255>> /opt/lampp/htdocs/Proyecto/stat2.txt;");

	$vlineas = file("stat2.txt");
   	
	$sLinea=$vlineas[count($vlineas)-2];
	$alt=substr($sLinea,0,strlen($sLinea)-1);
	if($alt=="0"){
		//Crear un PopUp aquí
		echo "Ha habido un error al crear la ruta con las alturas. Por favor intentelo de nuevo";
		
		system("rm grasserrors*;
		rm $archivo;
		rm stat.txt;
		rm stat2.txt;
		rm stat3.txt;
		rm cats.txt;
		rm alt.txt;");

		exit;
	}

	$alt=$alt/(count($vlineas)-1);

	system("g.remove rast=unitario");
	system("g.remove rast=coste");

	//Pendiente
	system("r.mapcalc \"unitario=if(rutes_aux==1,slope,null())\"");

	system("r.stats input=unitario fs=space nv=* nsteps=255>> /opt/lampp/htdocs/Proyecto/stat3.txt;");

	$vlineas = file("stat3.txt");

	$pends = explode('-',$vlineas[count($vlineas)-2]);
	$pendiente=$pends[1];
	//Los demás tambien se podrían haber comprobado de esta forma
	if(count($vlineas)==2){
		//Crear un PopUp aquí
		echo "Ha habido un error con las pendientes al crear la ruta. Por favor intentelo de nuevo";
		
		system("rm grasserrors*;
		rm $archivo;
		rm stat.txt;
		rm stat2.txt;
		rm stat3.txt;
		rm cats.txt;
		rm alt.txt;");
		
		exit;
	}
	

	//Se elimina el mapa raster con el que hemos trabajado
	system("g.remove rast=rutes_aux");

//____________________________________________________ BD ________________________________________________________

	$ruteName="'".$ruteName."'";
	
	system("echo \"INSERT INTO rutes (cat,longitud,altura,pendiente,nombre) VALUES ($cat,$long,$alt,$pendiente,$ruteName)\" | db.execute;");

//________________________________________________________________________________________________________________

	system("v.patch -a input=rutes_aux output=rutes --overwrite;");

	//Se borra el mapa shape antes de crearlo de nuevo
	system("rm spearfish/PERMANENT/vectoriales/rutes.dbf;");	
	system("rm spearfish/PERMANENT/vectoriales/rutes.prj;");
	system("rm spearfish/PERMANENT/vectoriales/rutes.shp;");
	system("rm spearfish/PERMANENT/vectoriales/rutes.shx;");

	//Se pasa a formato shp para poder visualizarlo
	system("v.out.ogr input=rutes type=line dsn=/opt/lampp/htdocs/Proyecto/spearfish/PERMANENT/vectoriales layer=1 format=ESRI_Shapefile;");

	$imgPNG=$cat.".png";

	putenv("GRASS_PNGFILE=/opt/lampp/htdocs/Proyecto/images_rutes/$imgPNG");

	system("d.mon PNG 2>> /opt/lampp/htdocs/Proyecto/grasserrors2.txt;
	d.rast elevation.dem;
	d.vect rutes_aux color=red width=3;
	d.vect roads color=black;
	d.mon stop=PNG 2>> /opt/lampp/htdocs/Proyecto/grasserrors3.txt; ");

//______________________________________________________PROFILE________________________________________

	//Se crea el perfil de la ruta
	$rute_profile="/opt/lampp/htdocs/Proyecto/profiles/".$imgPNG;

	if (!$gestor = fopen("scriptplot", 'a')) {
       		echo "No se puede abrir el archivo (\"script\")";
         	exit;
   	}

	fputs($gestor,"set terminal png");
	fputs($gestor,"\n");
	fputs($gestor,"set output '$rute_profile'");
	fputs($gestor,"\n");
	fputs($gestor,"plot \"profile.pts\" with line 3");
	fputs($gestor,"\n");	
	
	fclose($gestor);

	//Se crea el fichero profile.pts
	$archivo='/opt/lampp/htdocs/Proyecto/coordenadas/'.$cat.'.txt';
	
	$vlineas = file($archivo);
	
	if (!$gestor2 = fopen("coordenadas_prof.txt", 'a')) {
       		echo "No se puede abrir el archivo (\"coordenadas_prof.txt\")";
         	exit;
  	 }
	for($i=1;$i<count($vlineas)-1;$i++){
		fputs($gestor2,$vlineas[$i]);
	}
	fclose($gestor2);

	system("cat coordenadas_prof.txt | r.profile input=elevation.dem output=profile.pts");

	system("gnuplot 'scriptplot'");

//___________________________________________________CLEAN______________________________________

	system("rm grasserrors*;
	rm stat.txt;
	rm stat2.txt;
	rm stat3.txt;
	rm cats.txt;
	rm alt.txt;
	rm scriptplot;
	rm profile.pts;
	rm coordenadas_prof.txt;");

}

//Operacion dibujar rutas
if(isset($_GET["operation2"])){
	
	$coordGeo = explode('|',$_GET["arraygeo"]);

	$archivo='coordenadas.txt';

	fopen($archivo, 'a+'); 

	//Nos aseguramos primero de que el archivo existe y puede escribirse sobre el.
	if (is_writable($archivo)) {

		// $nombre_archivo en modo de adicion.
   		// El apuntador de archivo se encuentra al final del archivo
   		if (!$gestor = fopen($archivo, 'a')) {
       			echo "No se puede abrir el archivo ($archivo)";
         		exit;
   		}

		fputs($gestor,"L");
		fputs($gestor," ");
		fputs($gestor,(count($coordGeo)/2)+1);
		fputs($gestor," ");
		fputs($gestor,"1");
		fputs($gestor,"\n");	
		// Componer el archivo
		for($i=0;$i<count($coordGeo); $i+=2){
			fwrite($gestor, $coordGeo[$i]);
			fputs($gestor," ");
			fwrite($gestor, $coordGeo[$i+1]);
			fputs($gestor,"\n");	
		}
		fwrite($gestor, $coordGeo[0]);
		fputs($gestor," ");
		fwrite($gestor, $coordGeo[1]);
		fputs($gestor,"\n");
		fputs($gestor,"1");
		fputs($gestor," ");
		fputs($gestor,"321");
		fputs($gestor,"\n");
		
		fclose($gestor);

	} else {
   		echo "No se puede escribir sobre el archivo $archivo";
	}

	//Antes se borra el mapa auxiliar si existe
	system("g.remove vect=sel;");
	system("g.remove vect=consulta;");

	//Se crea el mapa vectorial
	system("v.in.ascii -n input=/opt/lampp/htdocs/Proyecto/coordenadas.txt output=sel format=standard;");

	system("v.select ainput=rutes binput=sel output=consulta;");

	system("v.category input=consulta option=print>> /opt/lampp/htdocs/Proyecto/cats.txt");

	$vlineas = file("cats.txt");

	system("db.connect driver=dbf database='/opt/lampp/htdocs/Proyecto/spearfish/PERMANENT/dbf/'");

	$cats="(cat=";
	for($i=0;$i<count($vlineas);$i++){
		if($i>=1){
			$cats=$cats." or cat=";
		}
		$vlineas[$i]=substr($vlineas[$i],0,strlen($vlineas[$i])-1);
		$cats=$cats.$vlineas[$i];
	}
	$cats=$cats.")";
	system("echo \"SELECT * FROM rutes WHERE $cats\" | db.select>> /opt/lampp/htdocs/Proyecto/consulta.txt; ");

	system("rm coordenadas.txt;
	rm cats.txt;");

	$vlineas = file("consulta.txt");

	if(count($vlineas)<=1){
		echo "Error en la consulta";
		system("rm consulta.txt;");
		exit;
	}
	else{
		for($i=1;$i<count($vlineas);$i++){
			$params[$i-1] = explode('|',$vlineas[$i]);
		}
	}
	
	system("rm consulta.txt;");

	createImpressView($params);
	
}

//Operacion Consultar los datos de las rutas
if(isset($_GET["operation3"])){

	$coordGeo = explode('|',$_GET["arraygeo"]);

	$archivo='coordenadas.txt';

	fopen($archivo, 'a+'); 

	//Nos aseguramos primero de que el archivo existe y puede escribirse sobre el.
	if (is_writable($archivo)) {

		// $nombre_archivo en modo de adicion.
   		// El apuntador de archivo se encuentra al final del archivo
   		if (!$gestor = fopen($archivo, 'a')) {
       			echo "No se puede abrir el archivo ($archivo)";
         		exit;
   		}

		fputs($gestor,"L");
		fputs($gestor," ");
		fputs($gestor,(count($coordGeo)/2)+1);
		fputs($gestor," ");
		fputs($gestor,"1");
		fputs($gestor,"\n");	
		// Componer el archivo
		for($i=0;$i<count($coordGeo); $i+=2){
			fwrite($gestor, $coordGeo[$i]);
			fputs($gestor," ");
			fwrite($gestor, $coordGeo[$i+1]);
			fputs($gestor,"\n");	
		}
		fwrite($gestor, $coordGeo[0]);
		fputs($gestor," ");
		fwrite($gestor, $coordGeo[1]);
		fputs($gestor,"\n");
		fputs($gestor,"1");
		fputs($gestor," ");
		fputs($gestor,"321");
		fputs($gestor,"\n");
		
		fclose($gestor);

	} else {
   		echo "No se puede escribir sobre el archivo $archivo";
	}

	//Antes se borra el mapa auxiliar si existe
	system("g.remove vect=sel;");
	system("g.remove vect=consulta;");

	//Se crea el mapa vectorial
	system("v.in.ascii -n input=/opt/lampp/htdocs/Proyecto/coordenadas.txt output=sel format=standard;");

	system("v.select ainput=rutes binput=sel output=consulta;");

	system("v.category input=consulta option=print>> /opt/lampp/htdocs/Proyecto/cats.txt");

	$vlineas = file("cats.txt");

	$cats="";
	if(count($vlineas)>1){
		echo "Error. Solo se puede seleccionar una ruta";
		system("rm coordenadas.txt;
		rm cats.txt;");
		exit;
	}
	else if(count($vlineas)==1){
		$cats=$vlineas[0];
	}

	system("g.remove vect=sel;");
	system("g.remove vect=consulta;");
	system("g.remove rast=consulta;");

	system("db.connect driver=dbf database='/opt/lampp/htdocs/Proyecto/spearfish/PERMANENT/dbf/'");

	system("rm coordenadas.txt;
	rm cats.txt;");
	
	system("echo \"SELECT * FROM rutes WHERE cat=$cats\" | db.select>> /opt/lampp/htdocs/Proyecto/consulta.txt; ");

	$vlineas = file("consulta.txt");

	if(count($vlineas)<=1){
		echo "Error en la consulta";
		system("rm consulta.txt;");
		exit;
	}
	else{
		$params = explode('|',$vlineas[count($vlineas)-1]);
		$pendiente=$params[1];
		$long=$params[2];
		$altura=$params[3];
		$RutName=$params[4];
		$RutName=urlencode($RutName);
	}
	
	$prof="/Proyecto/profiles/".$params[0].".png";
	
	system("rm consulta.txt;");

	header("Location: http://localhost/cgi-bin/mapserv?layer=rutes&mode=browse&zoomdir=0&imgxy=250.5+200.5&imgext=589980.000000+4913250.811623+609000.000000+4928459.188377&map=%2Fopt%2Flampp%2Fmap-script%2Fmapserver.map&mapxy=-1.000000+-1.000000&dibuja=false&altura=$altura&long=$long&pendiente=$pendiente&Rname=$RutName&profile=$prof");
}

if(isset($_GET["operationQuery"])){

	$where="";

	if($_GET["nameQuery"]!=""){
		$where=$where."nombre = '".$_GET["nameQuery"]."'";
	}
	else{
		if($_GET["pendmin"]!=""){
			$where=$where."pendiente >= ".$_GET["pendmin"];
		}
		if($_GET["pendmax"]!=""){
			if($_GET["pendmin"]!="")
				$where=$where." AND pendiente <= ".$_GET["pendmax"];
			else
				$where=$where."pendiente <= ".$_GET["pendmax"];
		}
		if($_GET["altmin"]!=""){
			if($_GET["pendmin"]!="" || $_GET["pendmax"]!="")
				$where=$where." AND altura >= ".$_GET["altmin"];
			else
				$where=$where."altura >= ".$_GET["altmin"];
		}
		if($_GET["altmax"]!=""){
			if($_GET["altmin"]!="" || $_GET["pendmin"]!="" || $_GET["pendmax"]!="")
				$where=$where." AND altura <= ".$_GET["altmax"];	
			else
				$where=$where."altura <= ".$_GET["altmax"];
		}
		if($_GET["longmin"]!=""){
			if($_GET["altmax"]!="" || $_GET["altmin"]!="" || $_GET["pendmin"]!="" || $_GET["pendmax"]!="")
				$where=$where." AND longitud >= ".$_GET["longmin"];
			else
				$where=$where."longitud >= ".$_GET["longmin"];
		}
		if($_GET["longmax"]!=""){
			if($_GET["longmin"]!="" || $_GET["altmax"]!="" || $_GET["altmin"]!="" || $_GET["pendmin"]!="" || $_GET["pendmax"]!="")
				$where=$where." AND longitud <= ".$_GET["longmax"];
			else
				$where=$where."longitud <= ".$_GET["longmax"];
		}	
	}
	system("echo \"SELECT * FROM rutes WHERE $where\" | db.select>> /opt/lampp/htdocs/Proyecto/consulta.txt; ");


	$vlineas = file("consulta.txt");

	if(count($vlineas)<=1){
		echo "Error en la consulta";
		system("rm consulta.txt;");
		exit;
	}
	else{
		for($i=1;$i<count($vlineas);$i++){
			$params[$i-1] = explode('|',$vlineas[$i]);
		}
	}
	
	system("rm consulta.txt;");

	createImpressView($params);
}

?>