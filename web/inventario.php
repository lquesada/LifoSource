<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tag,$conftp,$time,$us,$imgroot;
  techo('Desde el inventario puedes observar los objetos que tienes y decidir cuales usas. Puedes adoptar un objeto que tengas como insignia, de modo que todos tus combates, mensajes, y tu nombre en la clasificación serán firmados con la imagen de ese objeto.');

  if ($us['tiempopremium'] > 86400*7) {
    echo "<form action=\"fabricar.php\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo "<input type=\"submit\" onclick=\"return c()\" name=\"joyapremiumi\" value=\"Convertir una semana premium en una joya premium\"/>";
    echo "</form><br/><br/>";
  }

  db_lock("{$conftp}tiene READ,{$conftp}objetos READ,{$conftp}jugadores READ");
  $retval2 = db_query("SELECT {$conftp}tiene.nombreobj,cantidad,tipo,img,ataq,prot,usado,niveluso,usos FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj ORDER BY tipo ASC,ataq+prot DESC,posibilidad ASC,niveluso DESC,nombreobj ASC");
  db_unlock();
  db_lock("{$conftp}aprendido READ");
  $retvab = db_query("SELECT resultado FROM {$conftp}aprendido WHERE nombrejug='{$jug}'");
  db_unlock();
  $nrb = mysql_num_rows($retvab);
  for ($i = 0;$i < $nrb;$i++) {
    $xen = mysql_fetch_row($retvab);
    $sabe[$i] = $xen[0];
  }
  mysql_free_result($retvab);

  if ($us['noexplorarhasta'] <= $time)
    $puedeexplorar = 1;
  else
    $puedeexplorar = 0;

  if ($us['nocomerhasta'] <= $time)
    $puedecomer = 1;
  else
    $puedecomer = 0;

  echo "Tienes <b>{$us['oro']}</b> monedas de oro.<br/><br/>";
  echo "Tienes <b>{$us['energia']}</b> puntos de energía.<br/><br/>";

  echo '<b>Cambiar insignia:</b><br/><br/>';
  echo "<form action=\"inventario.php\" method=\"post\">";
  echo '<div>';
  echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
  echo "<select name=\"idob\">";
  if ($us['insignia'] != 'none')
    echo "<option value=\"none\">-- Sin insignia --</option>";
  else
    echo "<option value=\"none\" selected=\"selected\">-- Sin insignia --</option>";

  db_lock("{$conftp}tienemascotas READ,{$conftp}mascotas READ");
  $rg1 = db_query("SELECT {$conftp}tienemascotas.nombremascota,img,nivel,experiencia,alimento,ataquebase,defensabase,ataquenivel,defensanivel,expbase,expmult,expgana,maxnivel,usado FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}tienemascotas.nombremascota={$conftp}mascotas.nombremascota AND nombrejug='{$jug}' ORDER BY usado DESC,nombremascota ASC");
  db_unlock();
  $sel = 0;
  $bumrows = mysql_num_rows($rg1);
  for ($i = 0;$i < $bumrows;$i++) {
    $ret = mysql_fetch_row($rg1);
    if ($us['insignia'] == $ret[1]) {
      echo "<option value=\"{$ret[1]}\" selected=\"selected\">Mascota: {$ret[0]}</option>";
      $sel = 1;
    }
    else {
      echo "<option value=\"{$ret[1]}\">Mascota: {$ret[0]}</option>";
      $sel = 1;
    }
  }
  mysql_free_result($rg1);
  $numrows = mysql_num_rows($retval2);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval2);
    if (($us['insignia'] == $ret[0]) && ($sel == 0))
      echo "<option value=\"{$ret[3]}\" selected=\"selected\">{$ret[0]}</option>";
    else
    echo "<option value=\"{$ret[3]}\">{$ret[0]}</option>";
  }
  if ($numrows)
    mysql_data_seek($retval2,0);
  echo "</select>";
  echo "<input type=\"submit\" name=\"cambiar\" value=\"Cambiar insignia\"/><br/><br/><br/>";
  echo '</div>';
  echo "</form>";
  echo "<b>Insignia actual: <img src=\"{$imgroot}img/{$us['insignia']}.gif\" alt=\"insignia\"/></b><br/><br/>";

  if (!mysql_num_rows($retval2)) {
    echo 'No tienes objetos. Trabaja para conseguir objetos.<br/><br/>';
    return;
  }

  $ataq = 0;
  $prot = 0;

  $numrows = mysql_num_rows($retval2);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval2);
    if ($ret[6]) {
      $ataq += $ret[4];
      $prot += $ret[5];
    }
  }

  if ($numrows)
    mysql_data_seek($retval2,0);


  echo "<table>";
  echo "<tr><td><b>Ataque (objetos en uso):</b></td><td>{$ataq}</td></tr>";
  echo "<tr><td><b>Protección (objetos en uso):</b></td><td>{$prot}</td></tr>";
  echo "</table><br/><br/>";

  $numrows = mysql_num_rows($retval2);
  echo '<b>Actualmente en uso:</b><br/><br/>';
  $ultipo = '';
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval2);
    if ($ret[6]) {
      echo "<form action=\"inventario.php\" method=\"post\">";
      echo "<div class=\"item\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
      echo "<input type=\"submit\" name=\"dejar\" value=\"Dejar de usar\"/> ";
      if ($ret[8])
        $usos = ' / UN SOLO USO';
      else
        $usos = '';
      echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/> {$ret[0]} (Ataque: {$ret[4]} / Protección: {$ret[5]} / Nivel: {$ret[7]}{$usos})<br/></div>";
      echo "</form>";
    }
  }
  echo '<br/><br/>';
  if ($numrows)
    mysql_data_seek($retval2,0);

  $ultipo = '';
  $numrows = mysql_num_rows($retval2);
  echo '<b>Inventario completo:</b><br/><br/>';
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval2);
    if ($ultipo != $ret[2]) {
      $ultipo = $ret[2];
      echo "<br/>Categoría: {$ret[2]}.<br/><br/>";
    }
      if ($ret[8] <= 1) {
        echo "<form action=\"inventario.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
        if (!$ret[6]) {
          if ($ret[7] > $us['nivel'])
  	    echo '[demasiado poco nivel] ';
          else
            echo "<input type=\"submit\" name=\"usar\" value=\"Comenzar a usar\"/> ";
        }
        else
          echo '[actualmente en uso] ';
        if ($ret[8])
          $usos = ' / UN SOLO USO';
        else
          $usos = '';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Ataque: {$ret[4]} / Protección: {$ret[5]} / Nivel: {$ret[7]}{$usos})<br/></div>";
        echo "</form>";
      }
      else if ($ret[8] == 2) {
        echo "<form action=\"inventario.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
        if ($puedecomer)
          echo "<input type=\"submit\" name=\"comer\" value=\"Comer\" onclick=\"return c()\"/> ";
        else
          echo '[ya has comido] ';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: +{$ret[5]} / UN SOLO USO)<br/></div>";      
        echo "</form>";
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
        echo "<form action=\"inventario.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
        if (!$esaprendido) {
          if ($us['energia'] >= $ret[4])
            echo "<input type=\"submit\" name=\"leer\" value=\"Leer\" onclick=\"return c()\"/> ";
          else
            echo '[te falta energía] ';
        }
        else
          echo '[aprendido] ';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / UN SOLO USO{$aprendido})<br/></div>";
        echo "</form>";
      }
      else if ($ret[8] == 4) {
        echo "<div class=\"item\">[no usable] <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]}<br/></div>";
      }
      else if ($ret[8] == 5) {
        echo "<form action=\"inventario.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"submit\" name=\"consumir\" value=\"Consumir\" onclick=\"return c()\"/> ";
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (consumir para obtener 1 semana premium)<br/></div></form>";
      }
      else if ($ret[8] == 6) {
        echo "<form action=\"inventario.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
        db_lock('{$conftp}tienemascotas READ,{$conftp}mascotas READ');
        $rrxhi = db_query("SELECT * FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE nombreobj='{$ret[0]}' AND {$conftp}tienemascotas.nombremascota={$conftp}mascotas.nombremascota AND nombrejug='{$jug}'");
        db_unlock();
        if (mysql_num_rows($rrxhi))
          echo "[ya adoptada] ";
        else
          echo "<input type=\"submit\" name=\"adoptar\" value=\"Adoptar\" onclick=\"return c()\"/> ";
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (MASCOTA)<br/></div></form>";
      }
      else if ($ret[8] == 7) {
        echo "<form action=\"inventario.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
        if ($puedeexplorar) {
          if ($us['energia'] >= $ret[4]) {
            if ($us['nivel'] >= $ret[7])
              echo "<input type=\"submit\" name=\"explorar\" value=\"Explorar\" onclick=\"return c()\"/> ";
            else
              echo "[demasiado poco nivel]";
          }
          else
            echo "[te falta energía]";
        }
        else
            echo "[ya has explorado]";
          
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / Nivel: {$ret[7]} / MAPA)<br/></div></form>";
      }
      else if ($ret[8] == 8) {
        echo "<div class=\"item\">[no usable] <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]}<br/></div>";
      }


  }
  mysql_free_result($retval2);
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$conftp,$confnocomerhastapremium,$confnocomerhasta,$confnoexplorarhasta,$confnoexplorarhastapremium,$time,$us,$imgroot;

  if (isset($_REQUEST['cambiar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}jugadores WRITE,{$conftp}tiene READ,{$conftp}objetos READ,{$conftp}tienemascotas READ,{$conftp}mascotas READ");
        $retval = db_query("SELECT * FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}objetos.img='{$idob}'");
        if (mysql_num_rows($retval) || ($idob=='none')) {
          db_query("UPDATE {$conftp}jugadores SET insignia='{$idob}' WHERE nombrejug='{$jug}'");
          $_SESSION['mensaje'] = 'Has cambiado tu insignia.';
	}
	else {
          $rg1 = db_query("SELECT {$conftp}tienemascotas.nombremascota,img,nivel,experiencia,alimento,ataquebase,defensabase,ataquenivel,defensanivel,expbase,expmult,expgana,maxnivel,usado FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}tienemascotas.nombremascota={$conftp}mascotas.nombremascota AND nombrejug='{$jug}' AND img='{$idob}' ORDER BY usado DESC,nombremascota ASC");
          if ((mysql_num_rows($rg1))) {
            db_query("UPDATE {$conftp}jugadores SET insignia='{$idob}' WHERE nombrejug='{$jug}'");
            $_SESSION['mensaje'] = 'Has cambiado tu insignia.';
          }
          else
            $_SESSION['error'] = 'No tienes ese objeto, no puedes usarlo como insignia.';
        }
        db_unlock();
        mysql_free_result($retval);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: inventario.php');
    return 1;
  }

  if (isset($_REQUEST['dejar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tiene WRITE,{$conftp}objetos READ");
        $retv3 = db_query("SELECT {$conftp}tiene.nombreobj FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND img='{$idob}'");
        if (mysql_num_rows($retv3)) {
          $ret = mysql_fetch_row($retv3);
          db_query("UPDATE {$conftp}tiene SET usado=0 WHERE nombreobj='{$ret[0]}' AND nombrejug='{$jug}' AND usado=1");
          $_SESSION['mensaje'] = 'Has dejado de usar el objeto.';
        }
        else
          $_SESSION['error'] = 'No tenías ese objeto o no lo estabas usando.';
        db_unlock();
        mysql_free_result($retv3);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: inventario.php');
    return 1;
  }

  if (isset($_REQUEST['usar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tiene WRITE,{$conftp}tiene READ,{$conftp}objetos READ,{$conftp}jugadores READ");
	$retv = db_query("SELECT nivel FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
	$ret = mysql_fetch_row($retv);
        mysql_free_result($retv);
	$retv2 = db_query("SELECT nombreobj,tipo FROM {$conftp}objetos WHERE niveluso<={$ret[0]} AND img='{$idob}' AND usos<=1");
        if (mysql_num_rows($retv2)) {
          $reti = mysql_fetch_row($retv2);
          $retv3 = db_query("SELECT * FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND usado=0 AND nombreobj='{$reti[0]}'");
          $retv4 = db_query("SELECT {$conftp}tiene.nombreobj FROM {$conftp}tiene,{$conftp}objetos WHERE {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}' AND usado=1 AND tipo='{$reti[1]}'");
          $reto = mysql_fetch_row($retv4);
          db_query("UPDATE {$conftp}tiene SET usado=0 WHERE nombreobj='{$reto[0]}' AND nombrejug='{$jug}' AND usado=1");
          db_query("UPDATE {$conftp}tiene SET usado=1 WHERE nombreobj='{$reti[0]}' AND nombrejug='{$jug}' AND usado=0");
          if (mysql_num_rows($retv3))
	    $_SESSION['mensaje'] = 'Has comenzado a usar el objeto.';
          else
            $_SESSION['error'] = 'No tenías ese objeto o ya lo estabas usando.';
          mysql_free_result($retv3);
          mysql_free_result($retv4);
        }
	else
	  $_SESSION['error'] = 'No tienes suficiente nivel para usar este objeto o este objeto no es utilizable.';
        db_unlock();
        mysql_free_result($retv2);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: inventario.php');
    return 1;
  }

  if (isset($_REQUEST['leer'])) {
    $salirnow = 0;
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}jugadores WRITE,{$conftp}objetos READ,{$conftp}contiene READ,{$conftp}conoce WRITE,{$conftp}mensajes WRITE,{$conftp}tiene WRITE,{$conftp}aprendido WRITE");
        $retval = db_query("SELECT energia,nivel FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        $retval2 = db_query("SELECT ataq,nombreobj FROM {$conftp}objetos WHERE img='{$idob}' AND usos=3");
        $ret = mysql_fetch_row($retval);
        mysql_free_result($retval);
        if (mysql_num_rows($retval2)) {
          $ret2 = mysql_fetch_row($retval2);
          $retvalte = db_query("SELECT * FROM {$conftp}aprendido WHERE nombrejug='{$jug}' AND resultado='{$ret2[1]}'");
          if (!mysql_num_rows($retvalte)) {
            $retvalt = db_query("SELECT * FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='{$ret2[1]}'");
            if (mysql_num_rows($retvalt)) {
              if ($ret[0] >= $ret2[0]) {
                $retval3 = db_query("SELECT resultado,dificultadaprender FROM {$conftp}contiene where nombreobj='{$ret2[1]}'");
                $retval4 = db_query("SELECT resultado FROM {$conftp}conoce WHERE nombrejug='{$jug}'");
                $nro = mysql_num_rows($retval4);
                for ($i = 0;$i < $nro;$i++) {
                  $retone = mysql_fetch_row($retval4);
                  $sabx[$i] = $retone[0];
                }
                $nrows = mysql_num_rows($retval3);
                $yasabidos = 0;
                if ($nrows) {
                  db_query("UPDATE {$conftp}jugadores SET energia=energia-{$ret2[0]} WHERE nombrejug='{$jug}'");
                  $mens = "Has gastado <b>{$ret2[0]}</b> puntos de energía en leer el <b>{$ret2[1]}</b><br/><br/>";
                  $aprendidos = 0;
                  $incnivel = floor($ret[1]/5);
                  if ($incnivel > 30)
                   $incnivel = 30;
                   for ($i = 0;$i < $nrows;$i++) {
                    $ret3 = mysql_fetch_row($retval3);
                    $aprender = 1;
                    for ($y = 0;$y < $nro;$y++)
                      if ($ret3[0] == $sabx[$y]) 
                        $aprender = 0;
                    if ($aprender) {
                      $total = $incnivel+rand(0,100);
                      if ($total > $ret3[1]) {
                        db_query("INSERT INTO {$conftp}conoce (nombrejug,resultado,cantidad) VALUES ('{$jug}','{$ret3[0]}',0)");
                        $mens .= "Has aprendido a fabricar <b>{$ret3[0]}</b>.<br/>";
                        $aprendidos++;
                      }
                    }
                    else
                      $yasabidos++;
                  }
                  if (!$aprendidos)
                    $mens .= "No has conseguido aprender nada de este libro.<br/>";
                  $faltan = $nrows-$aprendidos-$yasabidos;
                  if (!$faltan) {
                    db_query("INSERT INTO {$conftp}aprendido (nombrejug,resultado) VALUES ('{$jug}','{$ret2[1]}')");
                    $mens .= "<br/>¡Enhorabuena! Te has aprendido este libro entero.";
                  }
                  else
                    $mens .= "<br/>Aun puedes aprender a fabricar <b>{$faltan}</b> objetos más de otros ejemplares de este libro.";
                  $mens .= '<br/><br/>El libro ha quedado emborronado e inservible.';
                  db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jug}','@',$time,'{$mens}')");
                  quita_objeto($jug,$ret2[1],$idob);
                  $_SESSION['mensaje'] = 'Has leido el libro.';
                  $salirnow = 1;
                }
                else
                  $_SESSION['error'] = 'Ya te habías aprendido ese libro de memoria.';
                mysql_free_result($retval3);
                mysql_free_result($retval4);
              }
              else
                $_SESSION['error'] = '¡No tienes suficiente energía!';
            }
            else
              $_SESSION['error'] = 'No tienes ese objeto.';
          }
          else
            $_SESSION['error'] = 'Ya te habías aprendido ese libro de memoria.';
          mysql_free_result($retvalte);
        }
        else
          $_SESSION['error'] = 'Ese objeto no se puede leer.';
        db_unlock();
        mysql_free_result($retval2);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    if ($salirnow)
      header('Location: mensajeria.php');
    else
      header('Location: inventario.php');
    return 1;
  }

  if (isset($_REQUEST['comer'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tiene WRITE,{$conftp}tiene READ,{$conftp}objetos READ,{$conftp}jugadores WRITE");
        $retvalcome = db_query("SELECT nocomerhasta FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        $retcome = mysql_fetch_row($retvalcome);
        mysql_free_result($retvalcome);
        if ($retcome[0] <= $time) {
  	  $retv2 = db_query("SELECT {$conftp}objetos.nombreobj,prot FROM {$conftp}objetos,{$conftp}tiene WHERE {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}' AND img='{$idob}' AND usos=2");
          if (mysql_num_rows($retv2)) {
            $retvv2 = mysql_fetch_row($retv2);
            quita_objeto($jug,$retvv2[0],$idob);
            $retv3 = db_query("SELECT premiumhasta,nivel,energia,nocomerhasta FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
            $r = mysql_fetch_row($retv3);
            mysql_free_result($retv3);
            $espremium = $us['espremium'];
  
            $enermax = 100+$r[1]*10;
            if ($espremium)
              $enermax = $enermax*2;
            $ener = $r[2]+$retvv2[1];
            if ($ener > $enermax)
              $ener = $enermax;
            if ($ener < $r[2])
              $ener = $r[2];
            if (!$espremium)
              $nocomerhasta = $time+$confnocomerhasta;
            else
              $nocomerhasta = $time+$confnocomerhastapremium;
            db_query("UPDATE {$conftp}jugadores SET nocomerhasta={$nocomerhasta},energia={$ener} WHERE nombrejug='{$jug}'");
            $_SESSION['mensaje'] = 'Te sientes saciado.';
          }
	  else
	    $_SESSION['error'] = 'Este objeto no es comestible o no lo tienes.';
          mysql_free_result($retv2);
        }
        else
          $_SESSION['error'] = 'No puedes volver a comer aun.';
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: inventario.php');
    return 1;
  }

  if (isset($_REQUEST['explorar'])) {
    $loc = 'inventario.php';
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tiene WRITE,{$conftp}tiene READ,{$conftp}objetos READ,{$conftp}jugadores WRITE,{$conftp}mensajes WRITE,{$conftp}explorado WRITE,{$conftp}exploracion READ");
        $retvalcome = db_query("SELECT noexplorarhasta,energia,nivel FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        $retcome = mysql_fetch_row($retvalcome);
        mysql_free_result($retvalcome);
        if ($retcome[0] <= $time) {
  	  $retv2 = db_query("SELECT {$conftp}objetos.nombreobj,ataq,niveluso FROM {$conftp}objetos,{$conftp}tiene WHERE {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}' AND img='{$idob}' AND usos=7");
          if (mysql_num_rows($retv2)) {
            $retvv2 = mysql_fetch_row($retv2);
            
            
            if ($retcome[2]>=$retvv2[2]) {

              if ($retcome[1]>=$retvv2[1]) {
             
                $espremium = $us['espremium'];
      
                $ener = $retcome[1]-$retvv2[1];

                if (!$espremium)
                  $noexplorarhasta = $time+$confnoexplorarhasta;
                else
                  $noexplorarhasta = $time+$confnoexplorarhastapremium;

                $reconn = db_query("SELECT cantidad FROM explorado WHERE nombrejug='{$jug}' and mapa='{$retvv2[0]}'");
                if (mysql_num_rows($reconn)==1) {
                  $retconn = mysql_fetch_row($reconn);
                  mysql_free_result($reconn);
                  $veces = $retconn[0]+1;
                  db_query("UPDATE {$conftp}explorado SET cantidad=cantidad+1 WHERE nombrejug='{$jug}' and mapa='{$retvv2[0]}'");
                }
                else {
                  $veces = 1;
                  db_query("INSERT INTO {$conftp}explorado VALUES ('{$jug}','{$retvv2[0]}',1)");
                }

                $retexact = db_query("SELECT nombreobj FROM {$conftp}exploracion WHERE mapa='{$retvv2[0]}' AND vez={$veces} AND exacto=1");
                $objeto = '';
                if (mysql_num_rows($retexact)==1) {
                  $rjga = mysql_fetch_row($retexact);
                  $objeto = $rjga[0];
                }
                else {
 
                  $retminor = db_query("SELECT nombreobj,probabilidad,exito FROM {$conftp}exploracion WHERE mapa='{$retvv2[0]}' AND vez<={$veces} AND exacto=0");
                  $retmax = db_query("SELECT SUM(probabilidad) FROM {$conftp}exploracion WHERE mapa='{$retvv2[0]}' AND vez<={$veces} AND exacto=0");
                  $maxx = mysql_fetch_row($retmax);
                  $maxx = $maxx[0];
                  mysql_free_result($retmax);
                  $azar = rand(1,$maxx);
                  $nro = mysql_num_rows($retminor);
                  for ($i = 0;$i < $nro;$i++) {
                    $row = mysql_fetch_row($retminor);
                    $azar -= $row[1];
                    if ($azar <= 0) {
                      if (rand(0,100)<=$row[2])
                        $objeto = $row[0];
                      $i = $nro;
                    }
                  }
                }
                mysql_free_result($retexact);
                
                $mens .= "Has explorado en ".substr($retvv2[0],6).".<br/><br/>";

                if ($objeto != '') {
                  $mens .= "Has encontrado:<br/><br/>";
                  $retval2 = db_query("SELECT nombreobj,posibilidad,img,tipo,ataq,prot,puntosencontrar,niveluso,usos FROM {$conftp}objetos WHERE nombreobj='{$objeto}'");
                  dale_objeto($jug,$objeto);

                  $reta = mysql_fetch_row($retval2);

                  if ($reta[8] <= 1) {
                    if ($reta[8])
                      $usos = ' / UN SOLO USO';
                    else
                      $usos = '';
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]} (Ataque: {$reta[4]} / Protección: {$reta[5]} / Nivel: {$reta[7]}{$usos}).</div><br/>";
                  }
                  else if ($reta[8] == 2) {
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]} (Energía: +{$reta[5]} / UN SOLO USO).</div><br/>";
                  }
                  else if ($reta[8] == 3) {
                    $aprendido = '';
                    $esaprendido = 0;
                    for ($ba = 0;$ba < $nrb;$ba++) {
                      if ($sabe[$ba] == $reta[0]) {
                        $aprendido = ' / APRENDIDO';
                        $esaprendido = 1;
                      }
                    }
          
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]} (Energía: -{$reta[4]} / UN SOLO USO{$aprendido}).</div><br/>";
                  }
                  else if ($reta[8] == 4) {
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]}.</div><br/>";
                  }
                  else if ($reta[8] == 5) {
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]} (consumir para obtener 1 semana premium).</div><br/>";
                  }
                  else if ($reta[8] == 6) {
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]} (MASCOTA).</div><br/>";
                  }
                  else if ($reta[8] == 7) {
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]} (Energía: -{$reta[4]} / MAPA).</div><br/>";
                  }
                  else if ($reta[8] == 8) {
                    $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[2]}.gif\" alt=\"{$reta[3]}\"/> {$reta[0]}.</div><br/>";
                  }
                   
                  mysql_free_result($retval2);
                }
                else {
                  $mens .= "No has conseguido encontrar nada.<br/><br/>";
                }

                db_query("UPDATE {$conftp}jugadores SET noexplorarhasta={$noexplorarhasta},energia={$ener} WHERE nombrejug='{$jug}'");
                db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jug}','@',{$time},'{$mens}')");
                $loc = 'mensajeria.php';
                $_SESSION['mensaje'] = 'Has explorado.';
              }
	      else
	        $_SESSION['error'] = 'No tienes suficiente energía como para explorar ahí';
  
            }
	    else
	      $_SESSION['error'] = 'No tienes suficiente nivel como para explorar ahí';

          }
	  else
	    $_SESSION['error'] = 'Este objeto no es un mapa o no lo tienes.';
          mysql_free_result($retv2);
        }
        else
          $_SESSION['error'] = 'No puedes volver a explorar aun.';
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: {$loc}");
    return 1;
  }

  if (isset($_REQUEST['consumir'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if (($us['moderador']) || ($us['enchufado']))
        $_SESSION['error'] = 'No uses joyas premium, ¡tienes premium ilimitado!';
      else if ($tag == $gtag) {
        db_lock("{$conftp}tiene WRITE,{$conftp}jugadores WRITE");
        $retvala = db_query("SELECT COUNT(*) FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='Joya premium'");
        $rc = mysql_fetch_row($retvala);
        mysql_free_result($retvala);
        if ($rc[0]) {
          quita_objeto($jug,'Joya premium','rprem');
          db_query("UPDATE {$conftp}jugadores SET premiumhasta=premiumhasta+86400*7 WHERE nombrejug='{$jug}' AND premiumhasta>={$time}");
          db_query("UPDATE {$conftp}jugadores SET premiumhasta={$time}+86400*7 WHERE nombrejug='{$jug}' AND premiumhasta<{$time}");  
          $_SESSION['mensaje'] = 'Joya premium consumida, has ganado una semana premium.';
        }
        else
          $_SESSION['error'] = 'No te quedaban joyas premium.';
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: inventario.php');
    return 1;
  }

  if (isset($_REQUEST['adoptar'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tiene WRITE,{$conftp}tienemascotas WRITE,{$conftp}objetos READ,{$conftp}mascotas READ,{$conftp}jugadores WRITE");
        $retv2 = db_query("SELECT {$conftp}objetos.nombreobj FROM {$conftp}objetos,{$conftp}tiene WHERE {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}' AND img='{$idob}' AND usos=6");
        if (mysql_num_rows($retv2)) {
          $r = mysql_fetch_row($retv2);
          $retv3 = db_query("SELECT nombremascota FROM {$conftp}mascotas WHERE nombreobj='{$r[0]}'");
          if (mysql_num_rows($retv3)) {
            $r2 = mysql_fetch_row($retv3);
            $retv4 = db_query("SELECT count(*) FROM {$conftp}tienemascotas WHERE nombrejug='{$jug}' AND nombremascota='{$r2[0]}'");
            $r5 = mysql_fetch_row($retv4);
            mysql_free_result($retv4);
            if (!$r5[0]) {
              quita_objeto($jug,$r[0],$idob);
              db_query("INSERT INTO tienemascotas (nombrejug,nombremascota,nivel,experiencia,usado) VALUES ('{$jug}','{$r2[0]}',1,0,0)");
              $_SESSION['mensaje'] = '¡Has adoptado una nueva mascota! Revisa la sección Mascotas.'; 
            }
            else
              $_SESSION['error'] = 'Ya tienes esa mascota, sólo puedes tener una mascota de cada clase.';
          }
          else
            $_SESSION['error'] = '¡Eso no es una mascota!';
          mysql_free_result($retv3);
        }
        else
          $_SESSION['error'] = 'No tienes ese objeto.';
        mysql_free_result($retv2);
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: inventario.php');
    return 1;
  }

}

?>
