<?php
$form = 1;
$nosetact = 1;
include('core.php');
function iweb() {
  global $tag,$jug,$time,$conftp,$us;

  if (($us['trabajando'] == 0) || ($us['fintrabajo'] < $time)) {
    db_lock("{$conftp}trabajos READ");
    if ($us['tiempopremiumefectivo'] > 0)
      $retval = db_query("SELECT segundos,nombre FROM {$conftp}trabajos WHERE (premium=1 AND segundos<{$us['tiempopremiumefectivo']}+86400) OR (premium=0 AND segundos>={$us['tiempopremiumefectivo']}+86400) ORDER BY segundos ASC");
    else
      $retval = db_query("SELECT segundos,nombre FROM {$conftp}trabajos WHERE (premium=1 AND segundos<{$us['tiempopremiumefectivo']}) OR (premium=0 AND segundos>={$us['tiempopremiumefectivo']}) ORDER BY segundos ASC");
    db_unlock();
    $numrows = mysql_num_rows($retval);
    techo('¡No todo es divertirse! Trabajando conseguirás experiencia y oro, y de vez en cuando subirás algún nivel o encontrarás algún objeto. Debes elegir un tiempo a trabajar, y pasado ese tiempo habrás ganado la experiencia y el oro indicado.');
    echo '<form action="trabajar.php" method="post">';
    echo '<div>';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo '<select name="trabajo">';
    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval);
      echo "<option value=\"{$ret[0]}\">{$ret[1]}</option>";
    }
    mysql_free_result($retval);
    echo '</select><input type="submit" value="Trabajar" name="trabajar"/></div></form>';
  }
  else {
    $cuantonum = $us['fintrabajo']-$time;
    $cuanto = ahora_tiempo($cuantonum);
    $ran = pwdgen();
    techo('Ahora mismo estás trabajando. En cualquier momento puedes dejar de trabajar y ganarás oro y experiencia proporcional al tiempo que has trabajado y a lo que ibas a ganar. Recuerda, <b>¡no es necesario que permanezcas conectado ni con el ordenador encendido hasta acabar de trabajar!</b>');
    
    echo "Falta <span id=\"ca\">{$cuanto}</span> para que acabes de trabajar.<br/><br/>";
    echo "<script language=\"javascript\">\n/"."/<![CDATA[\ninicia({$cuantonum})\nsetTimeout(\"window.location.reload( false );\", 1200000 );\n/"."/]]>\n</script>";
    echo "<form action=\"trabajar.php\" method=\"post\"><input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/><input type=\"hidden\" name=\"r\" value=\"{$ran}\"/><input type=\"submit\" name=\"dejartrab\" value=\"Dejar de trabajar\"/></form><br/>";
  }
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$time,$conftp,$us;
  if (isset($_REQUEST['dejartrab'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        $adelante = 0;
        db_lock("{$conftp}jugadores WRITE");
        $retval = db_query("SELECT trabajando,fintrabajo,puntostrabajo,orotrabajo FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        $ret = mysql_fetch_row($retval);
        mysql_free_result($retval);
        if ($ret[0] != 0) {
          $thecho = $ret[0]-$ret[1]+$time;
	  if ($thecho < 0)
	    $thecho = 0;
          $tiempo = $time-1;
          $puntostrabajo = floor($ret[2]*$thecho/$ret[0]);
          $orotrabajo = floor($ret[3]*$thecho/$ret[0]);
          db_query("UPDATE {$conftp}jugadores SET puntostrabajo={$puntostrabajo},orotrabajo={$orotrabajo},trabajopremium=0,fintrabajo={$tiempo},trabajando={$thecho} WHERE nombrejug='{$jug}'");
          $adelante = 1;
          $_SESSION['mensaje'] = 'Has dejado de trabajar.';
        }
        else
          $_SESSION['error'] = '¡Ya no estabas trabajando!';
        db_unlock();
        if ($adelante) {
          db_lock("{$conftp}contador WRITE");
          db_query("UPDATE {$conftp}contador SET contador=0");
          db_unlock();
        }
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trabajar.php');
    return 1;
  }
  if (isset($_REQUEST['trabajar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['trabajo']))) {
      $gtag = $_REQUEST['tag'];
      $trabajo = $_REQUEST['trabajo'];
      if (($tag == $gtag) && (is_numeric($trabajo))) {
        db_lock("{$conftp}jugadores READ");
        $retval = db_query("SELECT trabajando,premiumhasta FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        db_unlock();
        $ret = mysql_fetch_row($retval);
        mysql_free_result($retval);
        if ($ret[0] == 0) {
          db_lock("{$conftp}trabajos READ");
          if ($us['tiempopremiumefectivo'])
            $retval2 = db_query("SELECT segundos,puntos,oro,premium FROM {$conftp}trabajos WHERE (premium=1 AND segundos<={$us['tiempopremiumefectivo']}+86400 AND segundos={$trabajo}) OR (premium=0 AND segundos={$trabajo}) ORDER BY segundos ASC");
          else
            $retval2 = db_query("SELECT segundos,puntos,oro,premium FROM {$conftp}trabajos WHERE (premium=1 AND segundos<={$us['tiempopremiumefectivo']} AND segundos={$trabajo}) OR (premium=0 AND segundos={$trabajo}) ORDER BY segundos ASC");
          db_unlock();
          if (mysql_num_rows($retval2)) {
            $ret = mysql_fetch_row($retval2);
            $fintrabajo = $time+$ret[0];
            db_lock("{$conftp}jugadores WRITE");
            db_query("UPDATE {$conftp}jugadores SET trabajando={$ret[0]},fintrabajo={$fintrabajo},puntostrabajo={$ret[1]},orotrabajo={$ret[2]},trabajopremium={$ret[3]} WHERE nombrejug='{$jug}'");
            db_unlock();
	    $_SESSION['mensaje'] = 'Has empezado a trabajar.';
          }
          else
            $_SESSION['error'] = 'Cantidad de trabajo inválida o no tienes premium suficiente.';
          mysql_free_result($retval2);
        }
        else
          $_SESSION['error'] = '¡Ya estabas trabajando!';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: trabajar.php');
    return 1;
  }
}

?>
