<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$_REQUEST,$time,$conftp,$imgroot;
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT admin FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
  db_unlock();
  
  $ret = mysql_fetch_row($retval);

  if (!$ret[0])
    return;

  techo('Cuidado, esta página no valida las entradas de ninguna manera. No des acceso de administrador a nadie. Las funcionalidades que se pueden encontrar en esta página no están completas.');

  if (isset($_REQUEST['verjug'])) {
    $jugador = $_REQUEST['jugador'];
    $mijug = $jug;
    $jug = $jugador;

  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT nombrejug,creado,nivel,puntos,puntosnivel,oro,trabajado,hijos,nietos,padre,fintrabajo,protegidohasta,noatacarhasta,combates,vencedor,vencido,insignia,visitashijos,visitasnietos,premiumhasta,energia,nocomerhasta FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
  $retvol = db_query("SELECT {$conftp}jugadores.nombrejug,nivel,puntos,insignia,combates,vencedor,clan FROM {$conftp}jugadores WHERE padre='{$jug}' ORDER by puntos DESC");
  db_unlock();

  db_lock("{$conftp}tiene READ,{$conftp}objetos READ");
  $retval2 = db_query("SELECT SUM(ataq),SUM(prot) FROM {$conftp}tiene,{$conftp}objetos WHERE usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}'");
  db_unlock();

  $ret = mysql_fetch_row($retval);
  $ret2 = mysql_fetch_row($retval2);

  $dia = ahora_dia($ret[1]);
  $hora = ahora_hora($ret[1]);
  $expnext = expsignivel($ret[2],$ret[4])-$ret[3];

  if ($ret[10]) {
    $dia_t = ahora_dia($ret[10]);
    $hora_t = ahora_hora($ret[10]);
    $trabajandohasta = "el {$dia_t} a las {$hora_t}";
  }
  else
    $trabajandohasta = '(no estás trabajando)';

  if ($ret[11]>=$time) {
    $dia_p = ahora_dia($ret[11]);
    $hora_p = ahora_hora($ret[11]);
    $protegidohasta = "el {$dia_p} a las {$hora_p}";
  }
  else
    $protegidohasta = '(no estás protegido)';

  if ($ret[21]>=$time) {
    $dia_c = ahora_dia($ret[21]);
    $hora_c = ahora_hora($ret[21]);
    $nocomerhasta = "el {$dia_c} a las {$hora_c}";
  }
  else
    $nocomerhasta = '(puedes comer)';

  if ($ret[12]>=$time) {
    $dia_nt = ahora_dia($ret[12]);
    $hora_nt = ahora_hora($ret[12]);
    $noatacarhasta = "el {$dia_nt} a las {$hora_nt}";
  }
  else
    $noatacarhasta = '(puedes atacar si cumples los demás requisitos)';
  $insignia = "<img src=\"{$imgroot}img/{$ret[16]}.gif\" alt=\"insignia\"/>";

  $tiempo = ahora_tiempo($ret[6]);
  $ataqb = 10+floor($ret[2]/3);
  $protb = 10+floor($ret[2]/10);
  $ataqo = $ret2[0];
  $proto = $ret2[1];
  if (!$ataqo)
    $ataqo = 0;
  if (!$proto)
    $proto = 0;
  $ataq = $ataqo+$ataqb;
  $prot = $proto+$protb;
  if ($ret[19] >= $time) {
    $phdia = ahora_dia($ret[19]);
    $phhora = ahora_hora($ret[19]);
    $prem = "<tr><td><b>Premium hasta:</b></td><td>el {$phdia} a las {$phhora}</td></tr>";
    $espremium = 1;
  }
  else {
    $prem = '';
    $espremium = 0;
  }
    
  $enermax = 100+$ret[2]*10;
  if ($espremium)
    $enermax = $enermax*2;
  echo "<table class=\"estado\">
<tr><td><b>Nombre del jugador:</b></td><td>{$ret[0]}</td></tr>
<tr><td><b>Insignia:</b></td><td>{$insignia}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Jugando desde:</b></td><td>el {$dia} a las {$hora}</td></tr>
<tr><td><b>Tiempo trabajado:</b></td><td>{$tiempo}</td></tr>
{$prem}
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Combates totales:</b></td><td>{$ret[13]}</td></tr>
<tr><td><b>Combates ganados:</b></td><td>{$ret[14]}</td></tr>
<tr><td><b>Combates perdidos:</b></td><td>{$ret[15]}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Trabajando hasta:</b></td><td>{$trabajandohasta}</td></tr>
<tr><td><b>Protegido hasta:</b></td><td>{$protegidohasta}</td></tr>
<tr><td><b>No puedes atacar hasta:</b></td><td>{$noatacarhasta}</td></tr>
<tr><td><b>No puedes comer hasta:</b></td><td>{$nocomerhasta}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Nivel:</b></td><td>{$ret[2]}</td></tr>
<tr><td><b>Experiencia:</b></td><td>{$ret[3]}</td></tr>
<tr><td><b>Experiencia para el siguiente nivel:</b></td><td>{$expnext}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Oro:</b></td><td>{$ret[5]}</td></tr>
<tr><td><b>Energía:</b></td><td>{$ret[20]} (Máxima: {$enermax})</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Subordinados:</b></td><td>{$ret[7]}</td></tr>
<tr><td><b>Subordinados de subordinados:</b></td><td>{$ret[8]}</td></tr>
<tr><td><b>Jefe:</b></td><td>{$ret[9]}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Accesos a mis links de reclutar:</b></td><td>{$ret[17]}</td></tr>
<tr><td><b>Accesos a links de reclutar<br/>de subordinados:</b></td><td>{$ret[18]}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td><b>Ataque (nivel):</b></td><td>{$ataqb}</td></tr>
<tr><td><b>Protección (nivel):</b></td><td>{$protb}</td></tr>
<tr><td><b>Ataque (objetos en uso):</b></td><td>{$ataqo}</td></tr>
<tr><td><b>Protección (objetos en uso):</b></td><td>{$proto}</td></tr>
<tr><td><b>Ataque (total):</b></td><td>{$ataq}</td></tr>
<tr><td><b>Protección (total):</b></td><td>{$prot}</td></tr>

</table><br/><br/><br/>
";
  $numsubs = mysql_num_rows($retvol);
  if ($numsubs) {
    echo '<b>Subordinados:</b><br/>';
    echo '<table class="highscores"><tr><th>Puntos</th><th>Insig.</th><th>Nombre</th><th>Nivel</th><th>Clan</th><th>Combates ganados/totales</th></tr>';
    for ($tx = 0;$tx < $numsubs;$tx++) {
      $r = mysql_fetch_row($retvol);
      $clan = $r[6];
      if ($r[6] == '(ninguno)')
        $clan = '&nbsp;';
      echo "<tr><td>{$r[2]}</td><td><img src=\"{$imgroot}img/{$r[3]}.gif\" alt=\"insignia\"/></td><td>{$r[0]}</td><td>{$r[1]}</td><td>{$clan}</td><td>{$r[5]}/{$r[4]}</td>";
      echo "</tr>";
    }
    echo '</table><br/><br/>';
  }
  db_lock("{$conftp}tiene READ,{$conftp}objetos READ,{$conftp}jugadores READ,{$conftp}claninsignia READ");
  $retval = db_query("SELECT {$conftp}objetos.img,{$conftp}objetos.tipo,{$conftp}objetos.nombreobj,{$conftp}objetos.ataq,{$conftp}objetos.prot,{$conftp}objetos.niveluso,{$conftp}objetos.usos FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}tiene.usado=1 ORDER BY tipo ASC");
  $retval2 = db_query("SELECT {$conftp}tiene.nombreobj,cantidad,tipo,img,ataq,prot,usado,niveluso,usos FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj ORDER BY tipo ASC,ataq+prot DESC,posibilidad ASC");
  $retval3 = db_query("SELECT {$conftp}tiene.nombreobj,img FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj ORDER BY {$conftp}tiene.nombreobj ASC");
  $retval4 = db_query("SELECT insignia,nivel FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
  $retval5 = db_query("SELECT SUM(ataq),SUM(prot) FROM {$conftp}tiene,{$conftp}objetos WHERE usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}'");
  $retval6 = db_query("SELECT {$conftp}claninsignia.insignia FROM {$conftp}claninsignia,{$conftp}jugadores WHERE {$conftp}jugadores.clan={$conftp}claninsignia.clan AND {$conftp}jugadores.nombrejug='{$jug}'");
  $retvalcome = db_query("SELECT nocomerhasta,oro,energia FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
  db_unlock();
  db_lock("{$conftp}aprendido READ");
  $retvab = db_query("SELECT resultado FROM {$conftp}aprendido WHERE nombrejug='{$jug}'");
  db_unlock();
  $nrb = mysql_num_rows($retvab);
  for ($i = 0;$i < $nrb;$i++) {
    $xen = mysql_fetch_row($retvab);
    $sabe[$i] = $xen[0];
  }

  $retcome = mysql_fetch_row($retvalcome);
  if ($retcome[0] <= $time)
    $puedecomer = 1;
  else
    $puedecomer = 0;
  $ret4 = mysql_fetch_row($retval4);

  echo "<b>Insignia actual: <img src=\"{$imgroot}img/{$ret4[0]}.gif\" alt=\"insignia\"/></b><br/><br/>";


  $ret5 = mysql_fetch_row($retval5);

  $ataq = $ret5[0];
  $prot = $ret5[1];
  if (!$ataq)
    $ataq = 0;
  if (!$prot)
    $prot = 0;

  echo "<table>";
  echo "<tr><td><b>Ataque (objetos en uso):</b></td><td>{$ataq}</td></tr>";
  echo "<tr><td><b>Protección (objetos en uso):</b></td><td>{$prot}</td></tr>";
  echo "</table><br/><br/>";

  $numrows = mysql_num_rows($retval);
  echo '<b>Actualmente en uso:</b><br/><br/>';
  $ultipo = '';
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    if ($ret[1] != $ultipo) {
      if ($ret[6])
        $usos = ' / UN SOLO USO';
      else
        $usos = '';
      $ultipo = $ret[1];
      echo "<div class=\"item\">";
      echo "<img src=\"{$imgroot}img/{$ret[0]}.gif\" alt=\"{$ret[1]}\"/> {$ret[2]} (Ataque: {$ret[3]} / Protección: {$ret[4]} / Nivel: {$ret[5]}{$usos})<br/></div>";
    }
  }

  echo '<br/><br/>';
  $ultipo = '';
  $numrows = mysql_num_rows($retval2);
  echo '<b>Inventario completo:</b><br/><br/>';
  if (mysql_num_rows($retval6)) {
    echo "<br/>Categoría: Temporal.<br/><br/>";
    echo "<div class=\"item\">";
    echo '[no usable] ';
    echo "<img src=\"{$imgroot}img/{$retbb[0]}.gif\" alt=\"insignia\"/> Trofeo: Insignia del clan<br/></div>";
  }
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval2);
    if ($ultipo != $ret[2]) {
      $ultipo = $ret[2];
      echo "<br/>Categoría: {$ret[2]}.<br/><br/>";
    } 
      if ($ret[8] <= 1) {
        echo "<div class=\"item\">";
        if (!$ret[6]) {
          if ($ret[7] > $ret4[1])
            echo '[demasiado poco nivel] ';
          else
            echo "[usable] ";
        }
        else
          echo '[actualmente en uso] ';
        if ($ret[8])
          $usos = ' / UN SOLO USO';
        else
          $usos = '';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Ataque: {$ret[4]} / Protección: {$ret[5]} / Nivel: {$ret[7]}{$usos})<br/></div>";
      }
      else if ($ret[8] == 2) {
        echo "<div class=\"item\">";
        if ($puedecomer)
          echo "[comer] ";
        else
          echo '[ya has comido] ';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: +{$ret[5]} / UN SOLO USO)<br/></div>";
      }
      else if ($ret[8] == 3) {
        $aprendido = '';
        $esaprendido = 0;
        for ($ba = 0;$ba < $nrb;$ba++) {
          if ($sabe[$ba] == $ret[0]) {
            $aprendido = ' / APRENDIDO';
            $esaprendido = 1;
          }
        }
        echo "<div class=\"item\">";
        if (!$esaprendido) {
          if ($retcome[2] >= $ret[4])
            echo "[leer] ";
          else
            echo '[te falta energía] ';
        }
        else
          echo '[aprendido] ';
          echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / UN SOLO USO{$aprendido})<br/></div>";
      }
      else if ($ret[8] == 4) {
        echo "<div class=\"item\">[no usable] <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (UN SOLO USO)<br/></div>";
      }
      else if ($ret[8] == 5) {
        echo "<div class=\"item\">[consumir] <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (consumir para obtener 1 semana premium)<br/></div>";
      }
      else if ($ret[8] == 6) {
        echo "<div class=\"item\">[adoptar] <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (MASCOTA)<br/></div>";
      }


    }

  
    echo '<b>Últimos mensajes recibidos:</b><br/><br/>';
    db_lock("{$conftp}mensajes READ");
    $retval = db_query("SELECT remitente,hora,mensaje,idmensaje,visto FROM {$conftp}mensajes WHERE nombrejug='{$jug}' AND reportado=0 ORDER BY idmensaje DESC");
    db_unlock();
    $numrows = mysql_num_rows($retval);
    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval);
      $dia = ahora_dia($ret[1]);
      $hora = ahora_hora($ret[1]);
      echo "<div class=\"news\">";
      if (!$ret[4])
        echo '<img src="'.$imgroot.'img/nuevo.png" alt="Nuevo"/> ';
      else
        echo '<img src="'.$imgroot.'img/leido.png" alt="Leido"/> ';
      if ($ret[0] == '@')
        echo "Noticia recibida el {$dia} a las {$hora}:</div><br/><div class=\"newsitem\">{$ret[2]}";
      else {
        echo "Mensaje de <b>{$ret[0]}</b> recibido el {$dia} a las {$hora}.<br/>";
        echo "</div><br/><div class=\"newsitem\">{$ret[2]}";
      }
      echo "</div>";
      echo "<br/><br/><br/>";
    }




    $jug = $mijug;
    echo '<br/><br/>';
    echo '<a href="admin.php">Volver</a>';
    echo '<br/><br/>';
    return;
  }
  echo 'Bienvenido a la página de administración.<br/><br/>';
  echo 'Dar o quitar joyas premium.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/>+- Joyas:<input type="text" name="tiempo" value="5"/><input type="submit" name="joyaspremium"/></form><br/><br/>';
  db_lock("{$conftp}tiene READ");
  $retval = db_query("SELECT nombrejug,cantidad FROM {$conftp}tiene WHERE nombreobj='Joya premium'");
  db_unlock();
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    echo "$ret[0] - Posee $ret[1] joyas premium<br/>";
  }
  echo '<br/><br/>';
  echo 'Dar o quitar tiempo premium.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/>+- Tiempo:<input type="text" name="tiempo" value="2419200"/><input type="submit" name="premium"/></form><br/><br/>';
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT nombrejug,premiumhasta-{$time} FROM {$conftp}jugadores WHERE premiumhasta-{$time}>0");
  db_unlock();
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    echo "$ret[0] - Premium $ret[1] segundos<br/>";
  }
  echo '<br/><br/>';
  echo 'Dar o quitar tiempo BAN.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/>+- Tiempo:<input type="text" name="tiempo" value="86400"/><input type="submit" name="ban"/></form><br/><br/>';
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT nombrejug,baneadohasta-{$time} FROM {$conftp}jugadores WHERE baneadohasta-{$time}>0");
  db_unlock();
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    echo "$ret[0] - Baneado $ret[1] segundos<br/>";
  }
  echo '<br/><br/>';
  echo 'Enviar noticia a jugador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/><br/>Mensaje:<br/><textarea name="mensaje" rows="7" cols="60"></textarea><br/><input type="submit" name="noticia"/></form><br/><br/>';
  echo 'Enviar noticia a todos los jugadores.<br/>';
  echo '<form action="admin.php" method="post">Mensaje:<br/><textarea name="mensaje" rows="7" cols="60"></textarea><br/><input type="submit" name="noticiat"/></form><br/><br/>';
  echo 'Dar insignias de clan.<br/>';
  echo '<form action="admin.php" method="post">Clan: <input type="text" name="clan"/> Insignia:<input type="text" name="ins" value=""/><input type="submit" name="darins"/></form><br/><br/>';
  echo 'Quitar insignias de clan.<br/>';
  echo '<form action="admin.php" method="post">Clan: <input type="text" name="clan"/><input type="submit" name="quitarins"/></form><br/><br/>';
  echo 'Dar o quitar oro a jugador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/>+- Oro:<input type="text" name="cantoro" value="1000"/><input type="submit" name="oro"/></form><br/><br/>';
  echo 'Dar puntos a jugador (puntossuma).<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/> Puntos:<input type="text" name="cantpuntos" value="1000"/><input type="submit" name="puntoss"/></form><br/><br/>';
  echo 'Quitar puntos a jugador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/> Puntos:<input type="text" name="cantpuntos" value="1000"/><input type="submit" name="puntosq"/></form><br/><br/>';
  echo 'Dar objetos a jugador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/> Objeto:<input type="text" name="objeto"/><input type="submit" name="dar"/></form><br/><br/>';
  echo 'Quitar objetos a jugador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/> Objeto:<input type="text" name="objeto"/><input type="submit" name="quitar"/></form><br/><br/>';
  echo 'Nuevo moderador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/><input type="submit" name="moderadar"/></form><br/>';
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT nombrejug FROM {$conftp}jugadores WHERE moderador=1");
  db_unlock();
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    echo "$ret[0] - Es moderador<br/>";
  }
  echo '<br/>';
  echo 'Eliminar moderador.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/><input type="submit" name="moderaquitar"/></form><br/><br/>';
  echo 'Nuevo enchufado.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/><input type="submit" name="enchufadar"/></form><br/>';
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT nombrejug FROM {$conftp}jugadores WHERE enchufado=1");
  db_unlock();
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    echo "$ret[0] - Es enchufado<br/>";
  }
  echo '<br/>';
  echo 'Eliminar enchufado.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/><input type="submit" name="enchufaquitar"/></form><br/><br/>';
  echo 'Ver estado jugador.<br/>';
  echo '<form action="admin.php" method="get">Jugador: <input type="text" name="jugador"/><input type="submit" name="verjug"/></form><br/><br/>';
  echo 'Cambiar contraseña.<br/>';
  echo '<form action="admin.php" method="post">Jugador: <input type="text" name="jugador"/> Contraseña: <input type="text" name="pass"/> <input type="submit" name="cpass"/></form><br/><br/>';
  echo 'Ver y eliminar mensajes reportados.<br/>';
  db_lock("{$conftp}mensajes READ");
  $retval = db_query("SELECT idmensaje,nombrejug,remitente,hora,mensaje FROM {$conftp}mensajes WHERE reportado=1");
  db_unlock();
  $num = mysql_num_rows($retval);
  for ($i = 0;$i < $num;$i++) {
    $ret = mysql_fetch_row($retval);
    $dia = ahora_dia($ret[3]);
    $hora = ahora_hora($ret[3]);
    echo "<br/><br/><form action=\"admin.php\" method=\"post\"><input type=\"hidden\" name=\"idmens\" value=\"{$ret[0]}\"/><input type=\"submit\" name=\"borramens\" value=\"Borrar!\"/></form><u>Mensaje de <b>{$ret[2]}</b> recibido Y REPORTADO el {$dia} a las {$hora} POR <b>{$ret[1]}</b>.</u><br/>{$ret[4]}<br/><br/>";
  }
  echo '<br/><br/>';
  db_lock("{$conftp}textos READ");
  $retval2 = db_query("SELECT texto FROM {$conftp}textos WHERE nombre='noticiaspag'");
  db_unlock();
  $noti = mysql_fetch_row($retval2);
  echo 'Noticias arriba.<br/>';
  echo '<form action="admin.php" method="post">Contenido:<br/><textarea name="texto" rows="7" cols="60">'.$noti[0].'</textarea><br/><input type="submit" name="cnoticia"/></form><br/><br/>';
}

function procesaform() {
  global $_REQUEST,$jug,$time,$_SESSION,$conftp,$imgroot,$confmail;
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT admin FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
  db_unlock();
  $ret = mysql_fetch_row($retval);
  if (!$ret[0])
    header('Location: estado.php');

  if (isset($_REQUEST['cnoticia'])) {
    $texto = $_REQUEST['texto'];
    db_lock("{$conftp}textos WRITE");
    $retval = db_query("UPDATE {$conftp}textos SET texto='{$texto}' WHERE nombre='noticiaspag'");
    db_unlock();
    $_SESSION['mensaje'] = "Cambiado contenido.";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['cpass'])) {
    $jugador = check_username($_REQUEST['jugador']);
    $pass = $_REQUEST['pass'];
    $passc = pwdhash($jugador,$pass);
    db_lock("{$conftp}jugadores WRITE");
    $retval = db_query("UPDATE {$conftp}jugadores SET password='{$passc}' WHERE nombrejug='{$jugador}'");
    db_unlock();
    $_SESSION['mensaje'] = "Cambiada contraseña.";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['cnoticias'])) {
    $texto = $_REQUEST['texto'];
    db_lock("{$conftp}textos WRITE");
    $retval = db_query("UPDATE {$conftp}textos SET texto='{$texto}' WHERE nombre='noticias'");
    db_unlock();
    $_SESSION['mensaje'] = "Cambiado contenido.";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['moderaquitar'])) {
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}jugadores WRITE");
    $retval = db_query("UPDATE {$conftp}jugadores SET moderador=0 WHERE nombrejug='{$jugador}'");
    db_unlock();
    $_SESSION['mensaje'] = "Eliminado moderador.";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['moderadar'])) {
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}jugadores WRITE");
    $retval = db_query("UPDATE {$conftp}jugadores SET moderador=1 WHERE nombrejug='{$jugador}'");
    db_unlock();
    $_SESSION['mensaje'] = "Creado moderador.";
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['enchufaquitar'])) {
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}jugadores WRITE");
    $retval = db_query("UPDATE {$conftp}jugadores SET enchufado=0 WHERE nombrejug='{$jugador}'");
    db_unlock();
    $_SESSION['mensaje'] = "Eliminado enchufado.";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['enchufadar'])) {
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}jugadores WRITE");
    $retval = db_query("UPDATE {$conftp}jugadores SET enchufado=1 WHERE nombrejug='{$jugador}'");
    db_unlock();
    $_SESSION['mensaje'] = "Creado enchufado.";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['borramens'])) {
    $idmens = $_REQUEST['idmens'];
    db_lock("{$conftp}mensajes WRITE");
    $retval = db_query("DELETE FROM {$conftp}mensajes WHERE idmensaje='{$idmens}'");
    db_unlock();
    $_SESSION['mensaje'] = "Borrado mensaje";
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['quitar'])) {
    $objeto = $_REQUEST['objeto'];
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}objetos READ");
    $retval = db_query("SELECT img FROM {$conftp}objetos WHERE nombreobj='{$objeto}'");
    db_unlock();
    $ret = mysql_fetch_row($retval);
    db_lock("{$conftp}tiene WRITE,{$conftp}jugador WRITE");
    quita_objeto($jugador,$objeto,$ret[0]);
    $_SESSION['mensaje'] = "Quitado {$objeto} a {$jugador}";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['dar'])) {
    $objeto = $_REQUEST['objeto'];
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}tiene WRITE");
    dale_objeto($jugador,$objeto);
    $_SESSION['mensaje'] = "Dado {$objeto} a {$jugador}";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['joyaspremium'])) {
    $joyas = $_REQUEST['tiempo'];
    $jugador = $_REQUEST['jugador'];
    if ($jugador) {
      if ($joyas > 0) {
        db_lock("{$conftp}tiene WRITE,{$conftp}mensajes WRITE");
        dale_objeto($jugador,'Joya premium',$joyas);
        db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jugador}','@',$time,'Hola,<br/><br/>Has recibido <b><img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$joyas} Joya Premium</b>.<br/><br/>Puedes convertirlas en tiempo premium utilizándolas en el inventario (cada joya equivale a una semana de tiempo premium).<br/><br/>Puedes convertir tiempo premium en joyas (siempre que al menos te quede una semana premium completa) desde la página de estado o de fabricar.<br/><br/>Puedes utilizar las joyas premium para adquirir objetos únicos en la sección premium del mercader.<br/<br/>Puedes utilizar las joyas premium en trueques, aunque su valor será siempre 0.<br/><br/>Si tienes cualquier duda, envíame un correo electrónico a <a href=\"mailto:{$confmail}\">{$confmail}</a>.')");
        $_SESSION['mensaje'] = "Otorgadas joyas premium.";
        db_unlock();
      }
      else if ($joyas < 0) {
        db_lock("{$conftp}tiene WRITE,{$conftp}jugadores WRITE");
        quita_objeto($jugador,'Joya premium','rprem',-1*$joyas);
        $_SESSION['mensaje'] = "Eliminadas joyas premium.";
        db_unlock();
      }
    }
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['darins'])) {
    $clan = $_REQUEST['clan'];
    $ins = $_REQUEST['ins'];
    db_lock("{$conftp}claninsignia WRITE");
    db_query("INSERT INTO {$conftp}claninsignia VALUES ('{$clan}','{$ins}')");
    $_SESSION['mensaje'] = "Insertada insignia.";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['quitarins'])) {
    $clan = $_REQUEST['clan'];
    db_lock("{$conftp}claninsignia WRITE,{$conftp}jugadores WRITE");
    $ret1 = db_query("SELECT insignia FROM {$conftp}claninsignia WHERE clan='{$clan}'");
    if (mysql_num_rows($ret1)) {
      $ret = mysql_fetch_row($ret1);
      db_query("UPDATE {$conftp}jugadores SET insignia='none' WHERE clan='{$clan}' AND insignia='{$ret[0]}'");
      db_query("DELETE FROM {$conftp}claninsignia WHERE clan='{$clan}'");
    }
    mysql_free_result($ret1);
    $_SESSION['mensaje'] = "Eilminada insignia.'";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }

  if (isset($_REQUEST['ban'])) {
    $tiempo = $_REQUEST['tiempo'];
    $jugador = $_REQUEST['jugador'];
    if ($tiempo > 0)
      $tiempo = "+{$tiempo}";
    db_lock("{$conftp}jugadores WRITE");
    db_query("UPDATE {$conftp}jugadores SET baneadohasta=baneadohasta{$tiempo} WHERE nombrejug='{$jugador}' AND baneadohasta>={$time}");
    db_query("UPDATE {$conftp}jugadores SET baneadohasta={$time}{$tiempo} WHERE nombrejug='{$jugador}' AND baneadohasta<{$time}");
    $_SESSION['mensaje'] = "Actualizado ban.";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
 
  if (isset($_REQUEST['premium'])) {
    $tiempo = $_REQUEST['tiempo'];
    $jugador = $_REQUEST['jugador'];
    if ($tiempo > 0)
      $tiempo = "+{$tiempo}";
    db_lock("{$conftp}jugadores WRITE");
    db_query("UPDATE {$conftp}jugadores SET premiumhasta=premiumhasta{$tiempo} WHERE nombrejug='{$jugador}' AND premiumhasta>={$time}");
    db_query("UPDATE {$conftp}jugadores SET premiumhasta={$time}{$tiempo} WHERE nombrejug='{$jugador}' AND premiumhasta<{$time}");
    $_SESSION['mensaje'] = "Actualizado premium.";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['puntoss'])) {
    $cantpuntos = $_REQUEST['cantpuntos'];
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}jugadores WRITE");
    db_query("UPDATE {$conftp}jugadores SET puntossuma=puntossuma+{$cantpuntos} WHERE nombrejug='{$jugador}'");
    $_SESSION['mensaje'] = "Actualizados puntos.";
    db_unlock();
    sumapuntos();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['puntosq'])) {
    $cantpuntos = $_REQUEST['cantpuntos'];
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}jugadores WRITE");
    db_query("UPDATE {$conftp}jugadores SET puntos=puntos-{$cantpuntos} WHERE nombrejug='{$jugador}'");
    $_SESSION['mensaje'] = "Actualizados puntos.";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['oro'])) {
    $cantoro = $_REQUEST['cantoro'];
    $jugador = $_REQUEST['jugador'];
    if ($cantoro > 0)
      $cantoro = "+{$cantoro}";
    db_lock("{$conftp}jugadores WRITE");
    db_query("UPDATE {$conftp}jugadores SET oro=oro{$cantoro} WHERE nombrejug='{$jugador}'");
    $_SESSION['mensaje'] = "Actualizado oro.";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['noticiat'])) {
    $mensaje = $_REQUEST['mensaje'];
    db_lock("{$conftp}jugadores READ");
    $retval = db_query("SELECT nombrejug FROM {$conftp}jugadores");
    db_unlock();
    $num = mysql_num_rows($retval);
    db_lock("{$conftp}mensajes WRITE");
    for ($i = 0;$i < $num;$i++) {
      $ret = mysql_fetch_row($retval);
      db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$ret[0]}','@',$time,'{$mensaje}')");
      $_SESSION['mensaje'] = "Insertada noticia para todos los jugadores.";
    }
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
  if (isset($_REQUEST['noticia'])) {
    $mensaje = $_REQUEST['mensaje'];
    $jugador = $_REQUEST['jugador'];
    db_lock("{$conftp}mensajes WRITE");
    db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jugador}','@',$time,'{$mensaje}')");
    $_SESSION['mensaje'] = "Insertada noticia.";
    db_unlock();
    header('Location: admin.php');
    return 1;
  }
}

?>
