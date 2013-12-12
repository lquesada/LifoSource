<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tagc,$time,$zonahoraria,$conftp,$us;
  techo('Desde aquí puedes cambiar tu contraseña o tu cuenta de correo electrónico. Obsérvese que en cualquier caso debes indicar tu contraseña actual para realizar cualquier cambio.');

  db_lock("{$conftp}jugadores READ");
  $retvalem = db_query("SELECT emailnuevo,emailnuevocambio FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
  db_unlock();
  $retemail = mysql_fetch_row($retvalem);
  mysql_free_result($retvalem);

  if ($retemail[0]) {
    echo "<font color=\"red\"><b>AVISO</b>: Se está realizando un cambio de dirección de correo electrónico por la dirección de correo: {$retemail[0]}.</font><br/>";
    if ($retemail[1] < $time) {
      echo '<form action="micuenta.php" method="post"><input type="submit" name="confcambioemail" value="Confirmar cambio"/> <input type="submit" name="canccambioemail" value="Cancelar cambio"/></form><br/>';
    }
    else {
      $dia_t = ahora_dia($retemail[1]);
      $hora_t = ahora_hora($retemail[1]);
      echo "El cambio no se podrá confirmar hasta el {$dia_t} a las {$hora_t}.<br/>";
      echo '<form action="micuenta.php" method="post"><input type="submit" name="canccambioemail" value="Cancelar cambio"/></form><br/>';
    }
  }
  echo '<br/>';
  echo "<table class=\"estado\">
<tr><td><b>Nombre del jugador:</b></td><td>{$jug}</td></tr>
<tr><td><b>Cuenta de correo actual:</b></td><td>{$us['email']}</td></tr>
</table><br/><br/>";
  techo('También puedes cambiar tu zona horaria para que las horas del juego se correspondan a las de tu país. Para ello, debes escoger de esta lista la hora que más se aproxime a la hora en tu país.');
  echo "<form method=\"post\" action=\"micuenta.php\">";
  echo "<div>";
  echo "<input type=\"hidden\" name=\"tagc\" value=\"{$tagc}\"/>";
  echo "<select name=\"hora\">";
  for ($hh = -15;$hh<13;$hh++) {
    $hhshow = $hh*-1;
    if ($hh > 0)
      $val = "Etc/GMT{$hhshow}";
    else
      $val = "Etc/GMT+{$hhshow}";
    if ($hh == -15)
      $val = 'Europe/Madrid';
    l_setdate($hh);
    $dia = ahora_dia($time);
    $hora = ahora_hora($time);
    if ($hh == $zonahoraria)
      $selected = 'selected=\"selected\" ';
    else
      $selected = '';
    echo "<option {$selected}value=\"{$hh}\">{$dia} {$hora} ({$val})</option>";
  }
  l_setdate($zonahoraria);
  echo "</select> ";
  echo "<input type=\"submit\" name=\"cambiarz\" onclick=\"return c()\" value=\"Cambiar zona horaria\"/><br/><br/><br/></div>";
  echo "</form>";

  echo "<form method=\"post\" action=\"micuenta.php\">";
  echo "<table id=\"login\"><tr><td>";
  echo "<input type=\"hidden\" name=\"tagc\" value=\"{$tagc}\"/>";
  echo "Cambiar contraseña o e-mail:<br/><hr/><br/>";

  echo "<b>Contraseña actual *</b><br/>";
  echo "<input type=\"password\" name=\"password\" size=\"25\" maxlength=\"30\"/><br/><br/>";


  if ($retemail[0] == '') {
    echo "Nuevo e-mail:<br/>";
    echo "<input type=\"text\" name=\"email\" size=\"25\" maxlength=\"80\"/><br/><br/>";
  }

  echo "Nueva contraseña:<br/>";
  echo "<input type=\"password\" name=\"newpass\" size=\"25\" maxlength=\"30\"/><br/><br/>";

  echo "Repite la nueva contraseña:<br/>";
  echo "<input type=\"password\" name=\"newpassc\" size=\"25\" maxlength=\"30\"/><br/><br/>";

  echo "<input type=\"submit\" name=\"cambiar\" onclick=\"return c()\" value=\"Cambiar\"/> <b>* obligatorio</b><br/><br/>";
  echo "OJO: dejar en blanco cualquier<br/>valor para no cambiarlo<br/>";

  echo "</td></tr></table></form>";


}

function procesaform() {
  global $_REQUEST,$jug,$tagc,$conftp,$time,$confmailname,$confurl,$ip;
  if (isset($_REQUEST['confcambioemail'])) {
    db_lock("{$conftp}jugadores READ");
    $retval = db_query("SELECT emailnuevo,email,emailnuevocambio FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
    db_unlock();
    $retemail = mysql_fetch_row($retval);
    mysql_free_result($retval);
    if ($retemail[0] != '') {
      if ($retemail[2] < $time) {
        db_lock("{$conftp}jugadores WRITE");
        db_query("UPDATE {$conftp}jugadores SET emailnuevo='',emailnuevocambio=0,email='{$retemail[0]}' WHERE nombrejug='{$jug}'");
        db_unlock();
        $_SESSION['mensaje'] = 'Cambio de correo confirmado';
      }
      else
        $_SESSION['error'] = 'Aun no podías aceptar el cambio de correo.';
    }
    else
      $_SESSION['error'] = 'No se estaba procesando ya ningún cambio de correo.';
    header('Location: micuenta.php');
    return 1;
  }

  if (isset($_REQUEST['canccambioemail'])) {
    db_lock("{$conftp}jugadores READ");
    $retval = db_query("SELECT emailnuevo FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
    db_unlock();
    $retemail = mysql_fetch_row($retval);
    mysql_free_result($retval);
    if ($retemail[0] != '') {
      db_lock("{$conftp}jugadores WRITE");
      db_query("UPDATE {$conftp}jugadores SET emailnuevo='',emailnuevocambio=0 WHERE nombrejug='{$jug}'");
      db_unlock();
      $_SESSION['mensaje'] = 'Cambio de correo cancelado';
    }
    else
      $_SESSION['error'] = 'No se estaba procesando ya ningún cambio de correo.';
    header('Location: micuenta.php');
    return 1;
  }

  if (isset($_REQUEST['cambiarz'])) {
    if ((isset($_REQUEST['hora'])) && (isset($_REQUEST['tagc']))) {
      $gtag = $_REQUEST['tagc'];
      $hora = $_REQUEST['hora'];
      if ($tagc == $gtag) {
        if ((is_numeric($hora)) && ($hora >= -15) && ($hora <= 12)) {
          $_SESSION['zonahoraria'] = $hora;
          if (isset($_SESSION['zonahoraria']))
            $zonahoraria = $_SESSION['zonahoraria'];
          else
            $zonahoraria = '-15';
          if ($zonahoraria > 0)
            $horazonahoraria = "Etc/GMT+{$hora}";
          else
            $horazonahoraria = "Etc/GMT{$hora}";
          if ($zonahoraria == -15)
            $horazonahoraria = 'Europe/Madrid';

          $_SESSION['mensaje'] = 'Zona horaria cambiada.';
          db_lock("{$conftp}jugadores WRITE");
          db_query("UPDATE {$conftp}jugadores SET zonahoraria={$hora} WHERE nombrejug='{$jug}'");
          db_unlock();
        }
        else
          $_SESSION['error'] = 'Hora no válida.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: micuenta.php');
    return 1;
  }
  if (isset($_REQUEST['cambiar'])) {
    if ((isset($_REQUEST['password'])) && (isset($_REQUEST['tagc']))) {
      $gtag = $_REQUEST['tagc'];
      $password = $_REQUEST['password'];
      if ($tagc == $gtag) {
        $pwdhash = pwdHash($jug,$password);
        db_lock("{$conftp}jugadores READ");
        $retval = db_query("SELECT emailnuevo,email FROM {$conftp}jugadores WHERE nombrejug='{$jug}' AND password='{$pwdhash}'");
        db_unlock();
	if (antifloodcheck('cambiafalla',$jug,2,600)) {
          if (mysql_num_rows($retval)) {
            $retemail = mysql_fetch_row($retval);
            antifloodc('cambiafalla',$jug);
  	    $cambiaemail = 0;
	    $cambiapass1 = 0;
	    $cambiapass2 = 0;
            if (isset($_REQUEST['email'])) {
	      if ($_REQUEST['email']) {
	        $cambiaemail = 1;
	        $email = check_email($_REQUEST['email']);
                if ($email == $retemail[1])
                  $cambiaemail = 0;
	      }
	    }
            if (isset($_REQUEST['newpass'])) {
	      if ($_REQUEST['newpass']) {
	        $cambiapass1 = 1;
	        $newpass = check_password($_REQUEST['newpass']);
	      }
	    }
            if (isset($_REQUEST['newpassc'])) {
	      if ($_REQUEST['newpassc']) {
	        $cambiapass2 = 1;
	        $newpassc = check_password($_REQUEST['newpassc']);
	      }
	    }
            if (($cambiaemail) && ($retemail[0] != '')) {
              $_SESSION['error'] = 'Ya se estaba procesando un cambio de dirección de correo.';
            }
	    if (($cambiaemail) && (!$email)) {
              $_SESSION['error'] = 'El nuevo email indicado no es válido. ¡Debe ser válido! No se ha realizado ningún cambio.';
	    }
            if (false) //($cambiaemail) && (ereg('(hotmail\.com$)|(msn\.com$)',$email)))
              //$_SESSION['error'] = 'No puedes utilizar esa dirección de correo electrónico, ya que hotmail filtra los correos electrónicos. Puedes conseguir una cuenta de correo gratuita en <a href="http://www.gmail.com" rel="external">GMail</a>.';
              echo 'a';

	    else {
	      if ((($cambiapass1) && (!$newpass)) || (($cambiapass2) && (!$newpassc)))
	        $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres y debe contener símbolos estandar (letras, números, _-.+:,$#...)';
	      else {
                if (((!$cambiapass1) || (!$cambiapass2) || ($newpass != $newpassc)) && (($cambiapass1) || ($cambiapass2)))
                  $_SESSION['error'] = 'La contraseña nueva no coincide con su confirmación. No se ha realizado ningún cambio.';
                else {
  	          $cambio = '';
                  if (ereg('(hotmail\.com$)|(msn\.com$)',$retemail[1]))
                    $nuevahora = $time+86400*3;
                  else
                    $nuevahora = $time+86400*31;
                  if (($cambiaemail) && ($cambiapass1)) {
                    $pwd = pwdHash($jug,$newpass);
                    $cambio = "password='{$pwd}',emailnuevo='{$email}',emailnuevocambio={$nuevahora}";
 		  }
		  elseif ($cambiaemail)
		    $cambio = "emailnuevo='{$email}',emailnuevocambio={$nuevahora}";
                  elseif ($cambiapass1) {
		    $pwd = pwdHash($jug,$newpass);
		    $cambio = "password='{$pwd}'";
		  }
		  if ($cambio) {
                    if ($cambiaemail) {
                      send_mail($retemail[1],"Cambio de datos en {$confmailname}","Desde la IP {$ip} has solicitado cambiar tu correo en {$confmailname}. <br/>\n <br/>\nEl correo nuevo que has indicado para tu usuario {$jug} es {$email}.<br/>\nSi deseas cancelar este cambio, identifícaté en {$confurl} y ve a la sección Mi Cuenta.<br/>\n <br/>\n <br/>\nUn saludo. <br/>\n");
                    }
                    db_lock("{$conftp}jugadores WRITE");
                    db_query("UPDATE {$conftp}jugadores SET {$cambio} WHERE nombrejug='{$jug}'");
                    db_unlock();
		    if ($cambiapass1)
		      $_SESSION['error'] = 'Cambios realizados, tienes que identificarte con la nueva contraseña.';
		    else
                      if ($cambiaemail) {
                        $_SESSION['mensaje'] = 'Cambios realizados. Tendrás que confirmar el cambio de correo a partir de la fecha indicada.';
                      }
                      else
                        $_SESSION['mensaje'] = 'Cambios realizados.';
		    session_regenerate_id(TRUE);
		  }
	          else
	            $_SESSION['mensaje'] = 'No se ha realizado ningún cambio.';
	        }
	      }
	    }
	  }
          else {
            antiflood('cambiafalla',$jug,2,600);
            $_SESSION['error'] = 'Tu contraseña no coincide con tu usuario.';
	  }
	}
	else
	  $_SESSION['error'] = 'Demasiados intentos fallidos, intente en diez minutos.';
        mysql_free_result($retval);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: micuenta.php');
    return 1;
  }
}
?>
