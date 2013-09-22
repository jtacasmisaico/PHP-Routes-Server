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

function createImpressView($result){

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
<meta name=\"Author\" content=\"2008-2009 Alvaro Ortega - UGR\">
<title>SRS - GIS</title>
<link href=\"/Proyecto/style.css\" rel=\"stylesheet\" type=\"text/css\" />

</head>

<body>

<div id=\"container2\">
<form method=GET id=\"formulario\" action=\"/cgi-bin/mapserv\" name=\"mainform\">";
echo "<div id=\"main2\">
	<div id=\"imgRutesMaps\">";

	for($i=0;$i<count($result);$i++){
		echo $result[$i][4]."<br><br>";

		$imgRute="\""."/Proyecto/images_rutes/".$result[$i][0].".png"."\"";
		echo "<INPUT TYPE=\"image\" name=\"imgRute\" src=$imgRute width=\"430\" height=\"220\"><br><br>";

		echo "Pendiente Máxima: ".$result[$i][1]."%<br>";
		echo "Longitud de la ruta: ".$result[$i][2]." metros<br>";
		echo "Altitud máxima: ".$result[$i][3]." metros<br><br>";
	}
	echo "</div>
	<div id=\"imgRutesProf\"
";
	for($i=0;$i<count($result);$i++){
		$imgRute="\""."/Proyecto/profiles/".$result[$i][0].".png"."\"";
		echo "<INPUT TYPE=\"image\" name=\"imgRute\" src=$imgRute width=\"210\" height=\"210\"><br><br><br><br><br><br><br><br><br>";
	}
	echo "</div>";

	echo "<div id=\"noprint\">
		<input type=\"button\" value=\"IMPRIMIR\" onclick=\"javascript:doPrint()\"> 
	</div>
</div>
</form>
</div>

<script language=\"JavaScript\">
function doPrint(){
	document.getElementById(\"noprint\").style.display='none';
	window.print();
	document.getElementById(\"noprint\").style.display='block';
}
</script>
</body>
</html>
";
}
?>