<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$_REQUEST,$tag,$confdiasmensajes,$time,$conftp,$vmen,$_SESSION,$imgroot;

  techo('Bienvenido a la mensajería. Aquí podrás leer informes de combate, noticias y sucesos en general, y mensajes que te envíen otros jugadores. También puedes enviar mensajes a otros jugadores. No está permitido insultar en ningún caso, ni amenazar fuera del sentido del juego a otros jugadores. Única y exclusivamente si un mensaje incumple de forma clara esa norma, puedes informar al administrador del contenido del mensaje, que tomará las acciones oportunas.<br/><br/>Los administradores NUNCA te vamos a pedir tu contraseña ni ninguno de tus datos, y menos a través de la mensajería del juego. ¡No facilites a nadie tu contraseña por aquí!');
  if (isset($_REQUEST['p']))
    $p = $_REQUEST['p'];
  else
    $p = 1;
  if (!is_numeric($p))
    $p = 1;
  if ($p < 1)
    $p = 1;

  $vmen = 0;
  $_SESSION['vmen'] = 0;

  $ini = ($p-1)*15;
  $fin = $p*15;
  db_lock("{$conftp}mensajes READ");
  $retval = db_query("SELECT remitente,hora,mensaje,idmensaje,visto FROM {$conftp}mensajes WHERE nombrejug='{$jug}' AND reportado=0 ORDER BY idmensaje DESC LIMIT {$ini},15"); 
  $retval2 = db_query("SELECT COUNT(*) FROM {$conftp}mensajes WHERE nombrejug='{$jug}' AND reportado=0");
  db_unlock();
  $numrows = mysql_num_rows($retval);
  $primero = 0;
  $ultimo = 0;
  echo '<b>Mensajes recibidos:</b><br/><br/>';
  $nuevos = 0;
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    if (!$primero)
      $ultimo = $ret[3];
    $primero = $ret[3];
    $dia = ahora_dia($ret[1]);
    $hora = ahora_hora($ret[1]);
    if ($ret[0] != '@')
      echo "<form action=\"mensajeria.php\" method=\"post\">";
    echo "<div class=\"news\">";
    if (!$ret[4]) {
      echo '<img src="'.$imgroot.'img/nuevo.png" alt="Nuevo"/> ';
      $nuevos = 1;
    }
    else
      echo '<img src="'.$imgroot.'img/leido.png" alt="Leido"/> ';
    if ($ret[0] == '@')
      echo "Noticia recibida el {$dia} a las {$hora}:</div><br/><div class=\"newsitem\">{$ret[2]}";
    else {
      echo "Mensaje de <b>{$ret[0]}</b> recibido el {$dia} a las {$hora}.<br/>";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"idmens\" value=\"{$ret[3]}\"/>";
      echo "<input type=\"submit\" name=\"reportar\" onclick=\"return c('Esta opción se debe usar ÚNICAMENTE bajo INSULTOS, o AMENAZAS no relacionadas con el juego. El abuso de esta opción será castigado.')\" value=\"Informar al administrador de este mensaje\"/></div><br/><div class=\"newsitem\">{$ret[2]}";
    }
    echo "</div>";
    if ($ret[0] != '@')
      echo "</form>";
    echo "<br/><br/><br/>";

  }
  mysql_free_result($retval);

  $horalimite = floor($time-$confdiasmensajes*86400);
  if ($nuevos) {
    db_lock("{$conftp}mensajes WRITE");
    db_query("UPDATE {$conftp}mensajes SET visto=1 WHERE nombrejug='{$jug}' AND idmensaje >= {$primero} AND idmensaje <= {$ultimo}");
    db_query("DELETE FROM {$conftp}mensajes WHERE hora<{$horalimite} AND reportado=0 AND visto=1");
    db_unlock();
  }

  $ret2 = mysql_fetch_row($retval2);
  mysql_free_result($retval2);
  $npags = $ret2[0]/15;
  $npag = floor($npags);
  if ($npag != $npags)
    $npag++;
  if ($npag > 1) {
    echo "Página: ";
    $ini = $p-3;
    $fin = $p+3;
    if ($ini < 1)
      $ini = 1;
    if ($fin > $npag)
      $fin = $npag;
    if ($ini > 1)
      echo '... ';
    for ($i = $ini;$i <= $fin;$i++) {
      if ($i != $p)
        echo "<a href=\"mensajeria.php?p={$i}\">{$i}</a> ";
      else
        echo "<b>{$i}</b> ";
    }
    if ($fin < $npag)
      echo '... ';
  }
  echo '<br/><br/>';
  echo '<form action="mensajeria.php" method="post">';
  echo '<div>';
  echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
  echo "<input type=\"hidden\" name=\"p\" value=\"{$p}\"/>";
  echo "<input type=\"hidden\" name=\"primero\" value=\"{$primero}\"/>";
  echo "<input type=\"hidden\" name=\"ultimo\" value=\"{$ultimo}\"/>";
  echo '<input type="submit" name="borrarpagina" onclick="return c()" value="Borrar mensajes de esta página"/> <input type="submit" name="borrartodos" onclick="return c()" value="Borrar todos los mensajes leidos"/><br/><br/><br/></div></form>';
  echo '<form action="mensajeria.php" method="post">';
  echo '<div>';
  echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
  echo "<input type=\"hidden\" name=\"p\" value=\"{$p}\"/>";
  echo "<input type=\"hidden\" name=\"primero\" value=\"{$primero}\"/>";
  echo "<input type=\"hidden\" name=\"ultimo\" value=\"{$ultimo}\"/>";

  if (isset($_SESSION['lastmuser'])) {
    $lastmuser = check_username($_SESSION['lastmuser']);
    unset($_SESSION['lastmuser']);
  }
  else
    $lastmuser = '';

  if (isset($_SESSION['lastmens'])) {
    $lastmens = stripslashes($_SESSION['lastmens']);
    unset($_SESSION['lastmens']);
  }
  else
    $lastmens = '';

  techo('<b>Si eres miembro de un clan, puedes enviar un mensaje a todos los miembros de tu clan especificando como destino \'@\' (sin comillas), es decir, una arroba.</b>'); 

  if ($_SESSION['silenciado']) {
    $dia_t = ahora_dia($_SESSION['silenciado']);
    $hora_t = ahora_hora($_SESSION['silenciado']);
    echo "<font color=\"red\"><b>Estás siendo silenciado por un moderador, no puedes enviar mensajes hasta el {$dia_t} a las {$hora_t}</b></font>";
  }
  else {
    echo "<table id=\"mensajet\"><tr><td>Enviar un mensaje<br/><hr/><br/>Usuario al que quieres enviar el mensaje:<br/><input type=\"text\" name=\"username\" size=\"25\" maxlength=\"30\" value=\"{$lastmuser}\"/><br/><br/>Mensaje a enviar:<br/><textarea name=\"mensaje\" rows=\"7\" cols=\"60\">{$lastmens}</textarea><br/><br/><input type=\"submit\" name=\"enviar\" onclick=\"return c()\" value=\"Enviar\"/></td></tr></table>";
  }
  echo '</div>';
  echo '</form>';


}

function procesaform() {
  global $_REQUEST,$tag,$jug,$time,$confmaxmsg,$conftp,$imgroot;
  if (isset($_REQUEST['p']))
    $p = $_REQUEST['p'];
  else
    $p = 1;
  if (!is_numeric($p))
    $p = 1;

  if (isset($_REQUEST['reportar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idmens']))) {
      $gtag = $_REQUEST['tag'];
      $idmens = $_REQUEST['idmens'];
      if (($tag == $gtag) && (is_numeric($idmens))) {
        db_lock("{$conftp}mensajes WRITE");
        $retval = db_query("SELECT * FROM {$conftp}mensajes WHERE nombrejug='{$jug}' AND idmensaje={$idmens} AND reportado=0");
        if (mysql_num_rows($retval)) {
          db_query("UPDATE {$conftp}mensajes SET reportado=1 WHERE idmensaje={$idmens}");
          $_SESSION['mensaje'] = 'Se ha informado al administrador del contenido y remitente de ese mensaje.';
          $p = $p-1;
        }
        else
          $_SESSION['error'] = 'Ese mensaje ya no está en tu mensajería.';
        mysql_free_result($retval);
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: mensajeria.php?p={$p}");
    return 1;
  }
  if (isset($_REQUEST['enviar'])) {
    if ($_SESSION['silenciado']) {
      $_SESSION['error'] = 'No puedes hablar, estás siendo silenciado por un moderador.';
    }
    else if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if ((isset($_REQUEST['username'])) && (isset($_REQUEST['mensaje']))) {
          $username = check_username($_REQUEST['username']);
	  if ($_REQUEST['username'] == '@')
	    $username = '@';
	  $mensorig2 = $_REQUEST['mensaje'];
	  $mensorig = check_text($_REQUEST['mensaje']);
          $mensaje = str_replace("\n",'<br/> ',$mensorig);
          if (strlen(stripslashes($mensorig2)) > $confmaxmsg) {
            $_SESSION['lastmuser'] = $username;
            $_SESSION['lastmens'] = substr($mensorig,0,$confmaxmsg-2);
            $_SESSION['error'] = 'Mensaje demasiado largo, ha sido recortado. Reenvíe para confirmar';
          }
          else {
            if (($username) && (strlen($mensaje))) {
              db_lock("{$conftp}jugadores READ,{$conftp}mensajes WRITE,{$conftp}claninsignia READ");
              $retval = db_query("SELECT * FROM {$conftp}jugadores WHERE nombrejug='{$username}'");
              if ((mysql_num_rows($retval)) || ($username == '@')) {
	        $retvali = db_query("SELECT insignia,clan FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
		$reti = mysql_fetch_row($retvali);
                mysql_free_result($retvali);
		if ($username == '@') {
		  if ($reti[1] != '(ninguno)') {
                    $insigniac = '';
                    $retvalic = db_query("SELECT insignia FROM {$conftp}claninsignia WHERE clan='{$reti[1]}'");
                    if (mysql_num_rows($retvalic) == 1) {
                      $retic = mysql_fetch_row($retvalic);
                      $insigniac = "<img src=\"{$imgroot}img/{$retic[0]}.gif\" alt=\"insignia\"/>";
                    }
                    mysql_free_result($retvalic);
                    $retvalg = db_query("SELECT nombrejug FROM {$conftp}jugadores WHERE clan='{$reti[1]}'");
		    $numrows = mysql_num_rows($retvalg);
                    for ($ix = 0;$ix < $numrows;$ix++) {
		      $reto = mysql_fetch_row($retvalg);
		      db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$reto[0]}','{$jug}',{$time},'<img src=\"{$imgroot}img/{$reti[0]}.gif\" alt=\"insignia\"/> <b>{$jug}</b> (a los miembros del clan {$insigniac} <b>{$reti[1]}</b>):<br/><br/> {$mensaje}')");
		    }
                    mysql_free_result($retvalg);
                    $_SESSION['mensaje'] = "Mensaje enviado correctamente a los {$numrows} miembros del clan.";
		  }
		  else {
		    $_SESSION['error'] = 'No eres miembro de un clan.';
                    $_SESSION['lastmens'] = $mensorig;
		  }
		}
		else {
		  if ($reti[1] == '(ninguno)')
		    $clan = '';
		  else {
                    $insigniac = "";
                    if ($ret[7] != '(ninguno)') {
                      $retvalic = db_query("SELECT insignia FROM {$conftp}claninsignia WHERE clan='{$reti[1]}'");
                      if (mysql_num_rows($retvalic) == 1) {
                        $retic = mysql_fetch_row($retvalic);
                        $insigniac = "<img src=\"{$imgroot}img/{$retic[0]}.gif\" alt=\"insignia\"/>";
                      }
                      mysql_free_result($retvalic);
                    }
		    $clan = " del clan {$insigniac} <b>{$reti[1]}</b>";
}
                  db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$username}','{$jug}',{$time},'<img src=\"{$imgroot}img/{$reti[0]}.gif\" alt=\"insignia\"/> <b>{$jug}</b>{$clan}:<br/><br/> {$mensaje}')");
                  $_SESSION['mensaje'] = 'Mensaje enviado correctamente.';
		}
              }
              else {
	        $_SESSION['lastmens'] = $mensorig;
                $_SESSION['error'] = 'El usuario que has indicado no existe.';
              }
              db_unlock();
              mysql_free_result($retval);
            }
	    else {
	      $_SESSION['error'] = 'No se ha indicado usuario o mensaje a enviar.';
	      $_SESSION['lastmuser'] = $username;
	      $_SESSION['lastmens'] = $mensorig;
	    }
          }
        }
	else {
          $_SESSION['error'] = 'No se ha indicado usuario o mensaje a enviar.';
          $_SESSION['lastmuser'] = $username;
          $_SESSION['lastmens'] = $mensorig;
        }
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: mensajeria.php?p={$p}");
    return 1;
  }
  if (isset($_REQUEST['borrarpagina'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if ((isset($_REQUEST['primero'])) && (isset($_REQUEST['ultimo']))) {
          $primero = $_REQUEST['primero'];
          $ultimo = $_REQUEST['ultimo'];
          if ((is_numeric($primero)) && (is_numeric($ultimo)) && ($primero <= $ultimo)) {
            db_lock("{$conftp}mensajes WRITE");
            db_query("DELETE FROM {$conftp}mensajes WHERE nombrejug='{$jug}' AND idmensaje >= {$primero} AND idmensaje <= {$ultimo} AND reportado=0");
            db_unlock();
            if ($p > 1)
              $p--;
	    $_SESSION['mensaje'] = 'Mensajes borrados.';
          }
          else
            $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
        }
        else
          $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: mensajeria.php?p={$p}");
    return 1;
  }
  if (isset($_REQUEST['borrartodos'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        db_lock("{$conftp}mensajes WRITE");
        db_query("DELETE FROM {$conftp}mensajes WHERE nombrejug='{$jug}' AND reportado=0 AND visto=1");
        db_unlock();
        $p = 1;
	$_SESSION['mensaje'] = 'Mensajes borrados.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: mensajeria.php?p={$p}");
    return 1;
  }

}
?>
