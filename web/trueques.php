<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tag,$conftp,$imgroot,$us;


  db_lock("{$conftp}aprendido READ");
  $retvab = db_query("SELECT resultado FROM {$conftp}aprendido WHERE nombrejug='{$jug}'");
  db_unlock();
  $nrb = mysql_num_rows($retvab);
  for ($i = 0;$i < $nrb;$i++) {
    $xen = mysql_fetch_row($retvab);
    $sabe[$i] = $xen[0];
  }
  mysql_free_result($retvab);

  db_lock("{$conftp}tiene READ,{$conftp}objetos READ");
  $retvtiene = db_query("SELECT tiene.nombreobj,cantidad,img,valor FROM {$conftp}tiene,{$conftp}objetos WHERE tiene.nombreobj=objetos.nombreobj AND nombrejug='{$jug}' AND (valor>0 OR usos=3 OR usos=5 OR usos=6 OR usos=7 OR usos=8) ORDER BY nombreobj DESC");
  db_unlock();
  $tiene = array();
  $nrb2 = mysql_num_rows($retvtiene);
  for ($i = 0;$i < $nrb2;$i++) {
    $xen = mysql_fetch_row($retvtiene);
    $tiene[$i] = $xen;
  }
  mysql_free_result($retvtiene);


  techo('En el mercado también puedes intercambiar objetos con otros jugadores. Puedes ver las ofertas que te han hecho, aceptarlas, rechazarlas, añadir objetos a las ofertas, y demás. No obstante, el mercado, que actúa como mediador, cobrará un 5% del valor total de los objetos intercambiados a cada jugador, y no permitirá intercambios en los que los valores de los objetos no sean iguales (se redondea automáticamente con monedas de oro).');

  echo "Tienes <b>{$us['oro']}</b> monedas de oro.<br/><br/>";

  db_lock("{$conftp}trueques READ");
  $retval = db_query("SELECT idtrueque,inicia,recibe,ultimocambio,estado,tiempo FROM {$conftp}trueques WHERE inicia='{$jug}' or recibe='{$jug}'");
  $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}trueques WHERE inicia='{$jug}'");
  db_unlock();
  $countc = mysql_fetch_row($retvalc);
  mysql_free_result($retvalc);

  if ($countc[0] < 5) {
    echo '<form action="trueques.php" method="post">';
    echo '<b>Iniciar nuevo trueque con un jugador:</b><br/>';
    echo 'Nombre del jugador: <input type="text" name="truequejug" size="25" maxlength="30"/> ';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo '<input type="submit" name="iniciatrueque" value="Iniciar trueque"/></form>';
  }
  else
    echo '<b>Tienes 5 trueques iniciados, cancela o finaliza alguno que tú hayas iniciado para poder iniciar un trueque con otro jugador.</b>';
  $n = mysql_num_rows($retval);
  echo '<br/><hr/><br/><br/>';
  for ($i = 0;$i < $n;$i++) {
    $tr = mysql_fetch_row($retval);
    echo "Inicia la oferta: <b>{$tr[1]}</b> - Recibe la oferta: <b>{$tr[2]}</b><br/>";

    db_lock("{$conftp}itemstrueques READ,objetos READ");
    $bbe = db_query("SELECT {$conftp}itemstrueques.nombreobj,cantidad,tipo,img,ataq,prot,valor,niveluso,usos,nombrejug FROM {$conftp}itemstrueques,{$conftp}objetos WHERE {$conftp}itemstrueques.nombreobj={$conftp}objetos.nombreobj AND idtrueque='{$tr[0]}'");
    db_unlock();
    $m = mysql_num_rows($bbe);
    $trueques = array();
    $truequesvalinicia = 0;
    $truequesvalrecibe = 0;
    for ($xb = 0;$xb < $m;$xb++) {
     $ret = mysql_fetch_row($bbe);
     $trueques[$xb]['nj'] = $ret[9];
     for ($xc = 0;$xc < 9;$xc++) {
       $trueques[$xb][$xc] = $ret[$xc];
     }
     if ($ret[9] == $tr[1])
       $truequesvalinicia += $ret[1]*$ret[6];
     else
       $truequesvalrecibe += $ret[1]*$ret[6];
    }
    mysql_free_result($bbe);

    for ($hh = 0;$hh < 2;$hh++) {
    if ($hh == 0)
      $show = $tr[1];
    else
      $show = $tr[2];

    echo "<br/><b>{$show}</b> ofrece:<br/>";
    $oroinicia = 0;
    $ororecibe = 0;
    if ($truequesvalrecibe > $truequesvalinicia)
      $oroinicia = $truequesvalrecibe-$truequesvalinicia;
    if ($truequesvalrecibe < $truequesvalinicia)
      $ororecibe = $truequesvalinicia-$truequesvalrecibe;
    if (($hh == 0) && ($oroinicia)) {
      echo "<img src=\"{$imgroot}img/mitesoro.gif\" alt=\"Oro\"/><b>x{$oroinicia}</b> monedas de oro (aun no cobradas).<br/>";
    }
    if (($hh == 1) && ($ororecibe)) {
      echo "<img src=\"{$imgroot}img/mitesoro.gif\" alt=\"Oro\"/><b>x{$ororecibe}</b> monedas de oro (aun no cobradas).<br/>";
    }
    for ($xd = 0;$xd < $m;$xd++) {
      $ret = $trueques[$xd];
      if ($ret['nj'] == $show) {
      if ($jug == $show) {
        $eliminar = "<form action=\"trueques.php\" method=\"post\"><input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/><input type=\"hidden\" name=\"idtrueque\" value=\"{$tr[0]}\"/><input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/><input type=\"submit\" name=\"retirar\" onclick=\"return c()\" value=\"Retirar de la oferta\"/>x<input type=\"text\" name=\"cantidad\" value=\"1\" size=\"2\" maxlength=\"5\"/> ";
      }
      else
        $eliminar = '';
      if ($ret[8] <= 1) {
        echo "<div class=\"item\">{$eliminar}";
        if ($ret[8])
          $usos = ' / UN SOLO USO';
        else
          $usos = '';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Ataque: {$ret[4]} / Protección: {$ret[5]} / Nivel: {$ret[7]}{$usos})<br/></div>";
      }
      else if ($ret[8] == 2) {
        echo "<div class=\"item\">{$eliminar}";
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
        echo "<div class=\"item\">{$eliminar}";
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / UN SOLO USO{$aprendido})<br/></div>";
      }
      else if ($ret[8] == 4) {
        echo "<div class=\"item\">{$eliminar}<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]}<br/></div>";
      }
      else if ($ret[8] == 5) {
        echo "<div class=\"item\">{$eliminar}<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (consumir para obtener 1 semana premium)<br/></div>";
      }
      else if ($ret[8] == 6) {
        echo "<div class=\"item\">{$eliminar}<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (MASCOTA)<br/></div>";
      }
      else if ($ret[8] == 7) {
        echo "<div class=\"item\">{$eliminar}<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / Nivel: {$ret[7]} / MAPA)<br/></div>";
      }
      else if ($ret[8] == 8) {
        echo "<div class=\"item\">{$eliminar}<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]}<br/></div>";
      }
    } 
      if ($jug == $show) {
        echo '</form>';
      }
    }

    }

    $impuestos = floor(($truequesvalinicia+$oroinicia+$truequesvalrecibe+$ororecibe)*0.025);
    $valor = floor($impuestos+$truequesvalinicia+$oroinicia+$truequesvalrecibe+$ororecibe);
    echo "<br/>Los impuestos ascienden a <b>{$impuestos}</b> monedas de oro de cada jugador por este trueque.<br/><br/>";
    echo "<br/><form action=\"trueques.php\" method=\"post\">Añadir objetos al trueque (a cambio se ofrece menos oro o el otro jugador ofrece más oro):<br/>";
    if (!$nrb2)
      echo 'No tienes ningún objeto.<br/><br/>';
    else {
      echo 'Objeto: <select name="idob">';
      for ($ju = 0;$ju < $nrb2;$ju++)
        echo "<option value=\"{$tiene[$ju][2]}\">{$tiene[$ju][0]} (Valor: {$tiene[$ju][3]}, Posees: {$tiene[$ju][1]})</option>";
      echo '</select> ';
      echo "Cantidad: <input type=\"text\" name=\"cantidad\"/ size=\"2\" maxlength=\"5\" value=\"1\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"idtrueque\" value=\"{$tr[0]}\"/>";
      echo ' <input type="submit" onclick="return c()" name="ofrecer" value="Añadir"/></form><br/>';
    }
    echo '<form action="trueques.php" method="post">';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo "<input type=\"hidden\" name=\"idtrueque\" value=\"{$tr[0]}\"/>";
    echo "<input type=\"hidden\" name=\"valor\" value=\"{$valor}\"/>";
    echo "<input type=\"hidden\" name=\"tiempo\" value=\"{$tr[5]}\"/>";
    echo '<input type="submit" name="cancelatrueque" onclick="return c()" value="Cancelar trueque y recuperar objetos"/> ';

    $ultimocambio = $tr[3];
    $estado = $tr[4];
    if ($jug == $tr[1])
      $inicioyo = 1;
    else
      $inicioyo = 0;

    $boton = 0;
    $texto = 'error';
    if ($inicioyo) {
      if ((!$ultimocambio) && (!$estado)) { $boton = 0;$texto = 'el otro jugador debe aceptar'; }
      else if ((!$ultimocambio) && ($estado)) { $boton = 1;$texto = 'Aceptar y finalizar el trueque'; }
      else if (($ultimocambio) && (!$estado)) { $boton = 1;$texto = 'Aceptar trueque y esperar al otro jugador'; }
      else if (($ultimocambio) && ($estado)) { $boton = 0;$texto = 'el otro jugador debe aceptar'; }
    }
    else {
      if (($ultimocambio) && (!$estado)) { $boton = 0;$texto = 'el otro jugador debe aceptar'; }
      else if (($ultimocambio) && ($estado)) { $boton = 1;$texto = 'Aceptar y finalizar el trueque'; }
      else if ((!$ultimocambio) && (!$estado)) { $boton = 1;$texto = 'Aceptar trueque y esperar al otro jugador'; }
      else if ((!$ultimocambio) && ($estado)) { $boton = 0;$texto = 'el otro jugador debe aceptar'; }
    }
    if ($boton == 0)
      echo "[{$texto}]</form>";
    else {
      echo "<input type=\"submit\" name=\"aceptatrueque\" onclick=\"return c()\" value=\"{$texto}\"/></form>";
    }

    echo '<br/><br/>Recuerda que debes <a href="trueques.php?reload='.pwdgen().'">refrescar la página</a> para ver los cambios.';

    if ($i < $n-1)
      echo '<br/><br/><hr/><br/><br/>';
    else
      echo '<br/><br/><br/><br/>';
  }
  mysql_free_result($retval);

}

function procesaform() {
  global $_REQUEST,$tag,$jug,$conftp,$time;

  if (isset($_REQUEST['iniciatrueque'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if (isset($_REQUEST['truequejug'])) {
          $truequejug = check_username($_REQUEST['truequejug']);
          if ($truequejug != $jug) {
            db_lock("{$conftp}jugadores READ");
            $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}jugadores WHERE nombrejug='{$truequejug}'");
            db_unlock();
            $countj = mysql_fetch_row($retvalc);
            mysql_free_result($retvalc);
            if ($countj[0]) {
              db_lock("{$conftp}trueques READ");
              $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}trueques WHERE inicia='{$jug}'");
              db_unlock();
              $countc = mysql_fetch_row($retvalc);
              mysql_free_result($retvalc);

              if ($countc[0] < 5) {
                db_lock("{$conftp}trueques WRITE");
                $retvalc = db_query("INSERT INTO {$conftp}trueques (inicia,recibe,ultimocambio,estado) VALUES ('{$jug}','{$truequejug}',0,0)");
                db_unlock();
                $_SESSION['mensaje'] = 'Trueque iniciado, añade objetos al trueque y/o espera a que el otro jugador los añada.';
              }
              else
                $_SESSION['error'] = 'Tienes 5 trueques iniciados, cancela o finaliza alguno que tú hayas iniciado para poder iniciar un trueque con otro jugador.';
              }
              else
                $_SESSION['error'] = 'No existe ese jugador.';
            }
            else
              $_SESSION['error'] = 'No puedes iniciar un trueque contigo mismo.';
        }
        else
          $_SESSION['error'] = 'No has indicado un jugador válido.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trueques.php');
    return 1;
  }

  if (isset($_REQUEST['aceptatrueque'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if (isset($_REQUEST['idtrueque'])) {
          $idtrueque = $_REQUEST['idtrueque'];
          if (is_numeric($idtrueque)) {
            db_lock("{$conftp}trueques READ");
            $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            $retvals = db_query("SELECT idtrueque,inicia,recibe,ultimocambio,estado,tiempo FROM {$conftp}trueques WHERE idtrueque={$idtrueque}");
            db_unlock();
            $countc = mysql_fetch_row($retvalc);
            mysql_free_result($retvalc);
            if ($countc[0]) {
              $tr = mysql_fetch_row($retvals);

              $ultimocambio = $tr[3];
              $estado = $tr[4];
              if ($jug == $tr[1])
                $inicioyo = 1;
              else
                $inicioyo = 0;

              if (
               (($inicioyo) && ((((!$ultimocambio) && (!$estado))) || ((($ultimocambio) && ($estado)))))
               ||
               ((!$inicioyo)) && (((($ultimocambio) && (!$estado))) || (((!$ultimocambio) && ($estado))))
              ) 
                $_SESSION['error'] = 'Debes esperar a que el otro jugador acepte primero.';
              else {
                if ((($inicioyo) && ((($ultimocambio) && (!$estado)))) || ((!$inicioyo) && (((!$ultimocambio) && (!$estado))))) {
                  db_lock("{$conftp}trueques WRITE,{$conftp}itemstrueques READ,{$conftp}objetos READ");





                  $dd1a = db_query("SELECT nombrejug,itemstrueques.nombreobj,cantidad,valor FROM {$conftp}itemstrueques,{$conftp}objetos WHERE {$conftp}itemstrueques.nombreobj={$conftp}objetos.nombreobj AND idtrueque={$idtrueque} AND nombrejug='{$tr[1]}'");

                  $valinicia = 0;
                  $objsinicia = array();
                  for ($io1 = 0;$io1 < mysql_num_rows($dd1a);$io1++) {
                    $gek = mysql_fetch_row($dd1a);
                    $objsinicia[$io1] = array();
                    $objsinicia[$io1][0] = $gek[1];
                    $objsinicia[$io1][1] = $gek[2];
                    $objsinicia[$io1][2] = $gek[3];
                    $valinicia += $gek[2]*$gek[3];
                  }
                  mysql_free_result($dd1a);
 
                  $dd2a = db_query("SELECT nombrejug,itemstrueques.nombreobj,cantidad,valor FROM {$conftp}itemstrueques,{$conftp}objetos WHERE {$conftp}itemstrueques.nombreobj={$conftp}objetos.nombreobj AND idtrueque={$idtrueque} AND nombrejug='{$tr[2]}'");

                  $valrecibe = 0;
                  $objsrecibe = array();
                  for ($io2 = 0;$io2 < mysql_num_rows($dd2a);$io2++) {
                    $gek = mysql_fetch_row($dd2a);
                    $objsrecibe[$io2] = array();
                    $objsrecibe[$io2][0] = $gek[1];
                    $objsrecibe[$io2][1] = $gek[2];
                    $objsrecibe[$io2][2] = $gek[3];
                    $valrecibe += $gek[2]*$gek[3];
                  }
                  mysql_free_result($dd2a);

                  $oroquetieneinicia = $dd1oro[0];
                  $oroquetienerecibe = $dd2oro[0];
                  $ororecibe = 0;
                  $oroinicia = 0;
                  if ($valinicia > $valrecibe)
                    $ororecibe = $valinicia-$valrecibe;
                  if ($valrecibe > $valinicia)
                    $oroinicia = $valrecibe-$valinicia;

                  $impuestos = floor(($valinicia+$valrecibe+$ororecibe+$oroinicia)*0.025);
                  $valor = floor($impuestos+$valinicia+$valrecibe+$ororecibe+$oroinicia);
                  
                  if (isset($_REQUEST['valor']))
                    $valorr = floor($_REQUEST['valor']);
                  else
                    $valorr = 0;
                  if ($valor != $valorr) {
                    $_SESSION['error'] = 'El trueque ha sido modificado mientras aceptabas. Confirma de nuevo.';
                  }
                  else {
                    if (isset($_REQUEST['tiempo']))
                      $tiempo = floor($_REQUEST['tiempo']);
                    else
                      $tiempo = 0;
                    if ($tiempo != $tr[5]) {
                      $_SESSION['error'] = 'El trueque ha sido modificado mientras aceptabas. Confirma de nuevo.';
                    }
                    else {
                      $_SESSION['mensaje'] = 'Trueque aceptado. Espera a que el otro jugador acepte, modifique o cancele el trueque.';
                      db_query("UPDATE {$conftp}trueques SET estado=1 WHERE idtrueque={$idtrueque}");
                    }
                  }
                  db_unlock();
                }
                else {

                  db_lock("{$conftp}mensajes WRITE,{$conftp}jugadores WRITE,{$conftp}tiene WRITE,{$conftp}itemstrueques WRITE,{$conftp}trueques WRITE,{$conftp}objetos READ");

                  $dd1d = db_query("SELECT oro FROM {$conftp}jugadores WHERE nombrejug='{$tr[1]}'");
                  $dd1oro = mysql_fetch_row($dd1d);
                  mysql_free_result($dd1d);
                  $dd2d = db_query("SELECT oro FROM {$conftp}jugadores WHERE nombrejug='{$tr[2]}'");
                  $dd2oro = mysql_fetch_row($dd2d);
                  mysql_free_result($dd2d);

                  $dd1a = db_query("SELECT nombrejug,itemstrueques.nombreobj,cantidad,valor FROM {$conftp}itemstrueques,{$conftp}objetos WHERE {$conftp}itemstrueques.nombreobj={$conftp}objetos.nombreobj AND idtrueque={$idtrueque} AND nombrejug='{$tr[1]}'");

                  $valinicia = 0;
                  $objsinicia = array();
                  for ($io1 = 0;$io1 < mysql_num_rows($dd1a);$io1++) {
                    $gek = mysql_fetch_row($dd1a);
                    $objsinicia[$io1] = array();
                    $objsinicia[$io1][0] = $gek[1];
                    $objsinicia[$io1][1] = $gek[2];
                    $objsinicia[$io1][2] = $gek[3];
                    $valinicia += $gek[2]*$gek[3];
                  }
                  mysql_free_result($dd1a);
 
                  $dd2a = db_query("SELECT nombrejug,itemstrueques.nombreobj,cantidad,valor FROM {$conftp}itemstrueques,{$conftp}objetos WHERE {$conftp}itemstrueques.nombreobj={$conftp}objetos.nombreobj AND idtrueque={$idtrueque} AND nombrejug='{$tr[2]}'");

                  $valrecibe = 0;
                  $objsrecibe = array();
                  for ($io2 = 0;$io2 < mysql_num_rows($dd2a);$io2++) {
                    $gek = mysql_fetch_row($dd2a);
                    $objsrecibe[$io2] = array();
                    $objsrecibe[$io2][0] = $gek[1];
                    $objsrecibe[$io2][1] = $gek[2];
                    $objsrecibe[$io2][2] = $gek[3];
                    $valrecibe += $gek[2]*$gek[3];
                  }
                  mysql_free_result($dd2a);

                  $oroquetieneinicia = $dd1oro[0];
                  $oroquetienerecibe = $dd2oro[0];
                  $ororecibe = 0;
                  $oroinicia = 0;
                  if ($valinicia > $valrecibe)
                    $ororecibe = $valinicia-$valrecibe;
                  if ($valrecibe > $valinicia)
                    $oroinicia = $valrecibe-$valinicia;

                  $impuestos = ($valinicia+$valrecibe+$ororecibe+$oroinicia)*0.025;
                  $oroGANArecibe = $oroinicia-$ororecibe-$impuestos;
                  $oroGANAinicia = $ororecibe-$oroinicia-$impuestos;

                  if ($oroquetieneinicia+$oroGANAinicia<0) {
                    if ($jug == $tr[1])
                      $_SESSION['error'] = 'Te falta oro para cerrar el trato.';
                    else {
                      $_SESSION['error'] = 'Al otro jugador le falta oro para cerrar el trato.';
                      db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$tr[1]}','@',{$time},'Te falta oro para llevar a cabo el trueque con <b>{$tr[2]}</b>.')");
                    }
                  }
                  else if ($oroquetienerecibe+$oroGANArecibe<0) {
                    if ($jug == $tr[2])
                      $_SESSION['error'] = 'Te falta oro para cerrar el trato.';
                    else {
                      $_SESSION['error'] = 'Al otro jugador le falta oro para cerrar el trato.';
                      db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$tr[2]}','@',{$time},'Te falta oro para llevar a cabo el trueque con <b>{$tr[1]}</b>.')");
                    }
                  }
                  else {
                    db_query("UPDATE {$conftp}jugadores SET oro=oro+{$oroGANAinicia} WHERE nombrejug='{$tr[1]}'");
                    db_query("UPDATE {$conftp}jugadores SET oro=oro+{$oroGANArecibe} WHERE nombrejug='{$tr[2]}'");
                    for ($i = 0;$i < $io1;$i++)
                      dale_objeto($tr[2],$objsinicia[$i][0],$objsinicia[$i][1]);
                    for ($i = 0;$i < $io2;$i++)
                      dale_objeto($tr[1],$objsrecibe[$i][0],$objsrecibe[$i][1]);
                    if ($jug == $tr[1])
                      db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$tr[2]}','@',{$time},'Tu trueque con <b>{$tr[1]}</b> ha sido aceptado y finalizado.')");
                    else
                      db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$tr[1]}','@',{$time},'Tu trueque con <b>{$tr[2]}</b> ha sido aceptado y finalizado.')");
                    db_query("DELETE FROM {$conftp}trueques WHERE idtrueque='{$idtrueque}'");
                    db_query("DELETE FROM {$conftp}itemstrueques WHERE idtrueque='{$idtrueque}'");
                    $_SESSION['mensaje'] = 'Trueque aceptado y completo. Has obtenido los objetos que el otro jugador ofrecía.';
                  }
                }
                db_unlock();
              }
            }
            else
              $_SESSION['error'] = 'No existe ese trueque o ya ha sido cancelado.';
            mysql_free_result($retvals);
          }
          else
            $_SESSION['error'] = 'ID de trueque no válida (no me seas hacker, eh? :P).';
        }
        else
          $_SESSION['error'] = 'ID de trueque no definida.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trueques.php');
    return 1;
  }

  if (isset($_REQUEST['cancelatrueque'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if (isset($_REQUEST['idtrueque'])) {
          $idtrueque = $_REQUEST['idtrueque'];
          if (is_numeric($idtrueque)) {
            db_lock("{$conftp}trueques READ");
            $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            $retvals = db_query("SELECT inicia,recibe FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            db_unlock();
            $countc = mysql_fetch_row($retvalc);
            mysql_free_result($retvalc);
            if ($countc[0]) {
              db_lock("{$conftp}trueques WRITE,{$conftp}itemstrueques WRITE,{$conftp}tiene WRITE");
              $retvalc = db_query("SELECT nombrejug,nombreobj,cantidad FROM {$conftp}itemstrueques WHERE idtrueque={$idtrueque}");
              $n = mysql_num_rows($retvalc);
              for ($i = 0;$i < $n;$i++) {
                $ret = mysql_fetch_row($retvalc);
                dale_objeto($ret[0],$ret[1],$ret[2]);
              }
              mysql_free_result($retvalc);
              db_query("DELETE FROM {$conftp}trueques WHERE idtrueque='{$idtrueque}'");
              db_query("DELETE FROM {$conftp}itemstrueques WHERE idtrueque='{$idtrueque}'");
              db_unlock(); 
              $rs = mysql_fetch_row($retvals);
              db_lock("{$conftp}mensajes WRITE");
              if ($jug == $rs[1])
                db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$rs[0]}','@',{$time},'Tu trueque con <b>{$rs[1]}</b> ha sido cancelado y has recuperado los objetos ofrecidos.')");
              else
                db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$rs[1]}','@',{$time},'Tu trueque con <b>{$rs[0]}</b> ha sido cancelado y has recuperado los objetos ofrecidos.')");
              db_unlock();
              $_SESSION['mensaje'] = 'Trueque cancelado.';
            }
            else
              $_SESSION['error'] = 'No existe ese trueque o ya ha sido cancelado.';
            mysql_free_result($retvals);
          }
          else
            $_SESSION['error'] = 'ID de trueque no válida (no me seas hacker, eh? :P).';
        }
        else
          $_SESSION['error'] = 'ID de trueque no definida.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trueques.php');
    return 1;
  }

  if (isset($_REQUEST['retirar'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if (isset($_REQUEST['idtrueque'])) {
          $idtrueque = $_REQUEST['idtrueque'];
          if (is_numeric($idtrueque)) {
            db_lock("{$conftp}trueques READ");
            $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            $retvals = db_query("SELECT inicia,recibe,ultimocambio,estado FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            db_unlock();
            $countc = mysql_fetch_row($retvalc);
            mysql_free_result($retvalc);
            if ($countc[0]) {
              $idob = $_REQUEST['idob'];
              $cantidad = floor($_REQUEST['cantidad']);
              if ((ereg('^[a-z]+$',$idob)) && (is_numeric($cantidad)) && ($cantidad > 0)) {
                db_lock("{$conftp}objetos READ");
                $revd = db_query("SELECT nombreobj FROM {$conftp}objetos WHERE img='{$idob}'");
                db_unlock();
                if (mysql_num_rows($revd)) {
                  $nomob = mysql_fetch_row($revd);
                  db_lock("{$conftp}itemstrueques WRITE,{$conftp}tiene WRITE,{$conftp}trueques");
                  $retp = db_query("SELECT cantidad FROM {$conftp}itemstrueques WHERE nombreobj='{$nomob[0]}' AND nombrejug='{$jug}' AND cantidad>={$cantidad} AND idtrueque={$idtrueque}");
                  if (mysql_num_rows($retp)) {
                     $cantt = mysql_fetch_row($retp);
                     if ($cantt[0] == $cantidad)
                       db_query("DELETE FROM {$conftp}itemstrueques WHERE nombreobj='{$nomob[0]}' AND nombrejug='{$jug}' AND cantidad={$cantidad} AND idtrueque={$idtrueque}");
                     else
                       db_query("UPDATE {$conftp}itemstrueques SET cantidad=cantidad-{$cantidad} WHERE nombreobj='{$nomob[0]}' AND nombrejug='{$jug}' AND cantidad={$cantt[0]} AND idtrueque={$idtrueque}");
                     dale_objeto($jug,$nomob[0],$cantidad);
                     $rs = mysql_fetch_row($retvals);
                     db_query("UPDATE {$conftp}trueques SET tiempo={$time} WHERE idtrueque={$idtrueque}");
                     if (($jug == $rs[0]) && (($rs[2] == 1) || ($rs[3] == 1)))
                       db_query("UPDATE {$conftp}trueques SET ultimocambio=0,estado=0 WHERE idtrueque={$idtrueque}");
                     if (($jug == $rs[1]) && (($rs[2] == 0) || ($rs[3] == 1)))
                       db_query("UPDATE {$conftp}trueques SET ultimocambio=1,estado=0 WHERE idtrueque={$idtrueque}");
                     $_SESSION['mensaje'] = 'Objetos retirados del trueque.';
                  }
                  else
                    $_SESSION['error'] = 'No tienes esa cantidad de este objeto ofertada.';
                  mysql_free_result($retp);
                  db_unlock();
                }
                else
                  $_SESSION['error'] = 'ID de objeto no válida.';
                mysql_free_result($revd);
              }
             else
              $_SESSION['error'] = '¡Intenta de nuevo, por favor! (indica una cantidad válida)';

            }
            else
              $_SESSION['error'] = 'No existe ese trueque o ya ha sido cancelado.';
            mysql_free_result($retvals);
          }
          else
            $_SESSION['error'] = 'ID de trueque no válida (no me seas hacker, eh? :P).';
        }
        else
          $_SESSION['error'] = 'ID de trueque no definida.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trueques.php');
    return 1;
  }

  if (isset($_REQUEST['ofrecer'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if (isset($_REQUEST['idtrueque'])) {
          $idtrueque = $_REQUEST['idtrueque'];
          if (is_numeric($idtrueque)) {
            db_lock("{$conftp}trueques READ");
            $retvalc = db_query("SELECT COUNT(*) FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            $retvals = db_query("SELECT inicia,recibe,ultimocambio,estado FROM {$conftp}trueques WHERE idtrueque={$idtrueque} AND (inicia='{$jug}' OR recibe='{$jug}')");
            db_unlock();
            $countc = mysql_fetch_row($retvalc);
            mysql_free_result($retvalc);
            if ($countc[0]) {
              $idob = $_REQUEST['idob'];
              $cantidad = floor($_REQUEST['cantidad']);
              if ((ereg('^[a-z]+$',$idob)) && (is_numeric($cantidad)) && ($cantidad > 0)) {
                db_lock("{$conftp}objetos READ");
                $revd = db_query("SELECT nombreobj FROM {$conftp}objetos WHERE img='{$idob}' AND (valor>0 OR usos=3 OR usos=5 OR usos=6 OR usos=7 OR usos=8)");
                db_unlock();
                if (mysql_num_rows($revd)) {
                  $nomob = mysql_fetch_row($revd);
                  db_lock("{$conftp}itemstrueques WRITE,{$conftp}tiene WRITE,{$conftp}trueques WRITE,{$conftp}jugadores WRITE");
                  $retp = db_query("SELECT cantidad FROM {$conftp}tiene WHERE nombreobj='{$nomob[0]}' AND nombrejug='{$jug}' AND ((cantidad>=$cantidad AND usado=0) OR (cantidad>=$cantidad+1 AND usado=1))");
                  $retp2 = db_query("SELECT cantidad FROM {$conftp}itemstrueques WHERE nombreobj='{$nomob[0]}' AND nombrejug='{$jug}' AND idtrueque={$idtrueque}");
                  if (mysql_num_rows($retp)) {
                     quita_objeto($jug,$nomob[0],$idob,$cantidad);
                     if (mysql_num_rows($retp2)) {
                       db_query("UPDATE {$conftp}itemstrueques SET cantidad=cantidad+{$cantidad} WHERE nombreobj='{$nomob[0]}' AND nombrejug='{$jug}' AND idtrueque={$idtrueque}");
                     }
                     else {
                       db_query("INSERT INTO {$conftp}itemstrueques (idtrueque,nombrejug,nombreobj,cantidad) VALUES ({$idtrueque},'{$jug}','{$nomob[0]}',$cantidad)");
                     }
                     $rs = mysql_fetch_row($retvals);
                     db_query("UPDATE {$conftp}trueques SET tiempo={$time} WHERE idtrueque={$idtrueque}");
                     if (($jug == $rs[0]) && (($rs[2] == 1) || ($rs[3] == 1)))
                       db_query("UPDATE {$conftp}trueques SET ultimocambio=0,estado=0 WHERE idtrueque={$idtrueque}");
                     if (($jug == $rs[1]) && (($rs[2] == 0) || ($rs[3] == 1)))
                       db_query("UPDATE {$conftp}trueques SET ultimocambio=1,estado=0 WHERE idtrueque={$idtrueque}");
                     $_SESSION['mensaje'] = 'Objetos ofertados en el trueque.';
                  }
                  else
                    $_SESSION['error'] = 'No tienes esa cantidad de este objeto para ofertar o estás utilizando una de esas unidades.';
                  mysql_free_result($retp);
                  db_unlock();
                }
                else
                  $_SESSION['error'] = 'ID de objeto no válida.';
                mysql_free_result($revd);
              }
             else
              $_SESSION['error'] = '¡Intenta de nuevo, por favor! (indica una cantidad válida)';

            }
            else
              $_SESSION['error'] = 'No existe ese trueque o ya ha sido cancelado.';
            mysql_free_result($retvals);
          }
          else
            $_SESSION['error'] = 'ID de trueque no válida (no me seas hacker, eh? :P).';
        }
        else
          $_SESSION['error'] = 'ID de trueque no definida.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trueques.php');
    return 1;
  }

}

?>


