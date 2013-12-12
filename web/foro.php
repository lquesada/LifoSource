<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$_REQUEST,$tag,$time,$conftp,$_SESSION,$vfor,$us,$imgroot;

  techo('Bienvenido al foro, donde podrás hablar con los demás jugadores.');
  if (isset($_REQUEST['p']))
    $p = $_REQUEST['p'];
  else
    $p = 1;
  if (!is_numeric($p))
    $p = 1;
  if ($p < 1)
    $p = 1;

  if ($vfor)
    $dovfor = 1;
  else
    $dovfor = 0;
  $vfor = 0;
  $_SESSION['vfor'] = 0;
  $ini = ($p-1)*15;
  $fin = $p*15;

  if (isset($_REQUEST['id']))
    $id = $_REQUEST['id'];
  else
    $id = 0;
  if (!is_numeric($id))
    $id = 0;
  if ($id < 1)
    $id = 0;

  if (($us['admin']) || ($us['moderador']))
    $esmodera = 1;
  else
    $esmodera = 0;

  if ($id) {
    db_lock("{$conftp}forohebras READ,{$conftp}foromensajes READ,{$conftp}jugadores READ");
    $retval = db_query("SELECT nombre FROM {$conftp}forohebras WHERE id={$id}");
    $retval2 = db_query("SELECT {$conftp}foromensajes.nombrejug,hora,texto,insignia,clan,idmensaje FROM {$conftp}foromensajes,{$conftp}jugadores WHERE {$conftp}jugadores.nombrejug={$conftp}foromensajes.nombrejug AND idhebra={$id} ORDER BY idmensaje ASC LIMIT {$ini},15");
    $retval3 = db_query("SELECT COUNT(*) FROM {$conftp}foromensajes WHERE idhebra={$id}");
    db_unlock();
    $numrows = mysql_num_rows($retval2);
    $ret = mysql_fetch_row($retval);
    mysql_free_result($retval);
    $ret2 = mysql_fetch_row($retval3);
    mysql_free_result($retval3);
    echo "<div><b>{$ret[0]}</b></div><br/>";
    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval2);
      $dia = ahora_dia($ret[1]);
      $hora = ahora_hora($ret[1]);
      $insignia = "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"insignia\"/>";
      if ($ret[4] == '(ninguno)')
        $suclan = '';
      else {
        $insigniac = "";
        db_lock("{$conftp}claninsignia READ");
        $retvalic = db_query("SELECT insignia FROM {$conftp}claninsignia WHERE clan='{$ret[4]}'");
        db_unlock();
        if (mysql_num_rows($retvalic) == 1) {
          $retic = mysql_fetch_row($retvalic);
          $insigniac = "<img src=\"{$imgroot}img/{$retic[0]}.gif\" alt=\"insignia\"/>";
        }
        mysql_free_result($retvalic);
        $suclan = "del clan {$insigniac} <b>{$ret[4]}</b> ";
      }
      if ($esmodera)
        echo "<form>";
      echo "<div>";
      if ($esmodera) {
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"id\" value=\"{$id}\"/>";
        echo "<input type=\"hidden\" name=\"p\" value=\"{$p}\"/>";
        echo "<input type=\"hidden\" name=\"idmensaje\" value=\"{$ret[5]}\"/>";
        echo "<input type=\"submit\" name=\"eliminarm\" onclick=\"return c('Como moderador debe eliminar únicamente los mensajes realmente problemáticos.')\" value=\"Eliminar\"/> ";
      }
      echo "- {$insignia} <b>{$ret[0]}</b> {$suclan}dijo en {$dia} {$hora}:<br/>{$ret[2]}";
      echo "</div>";
      if ($esmodera)
        echo "</form>";
      echo "<br/>";
    }
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
          echo "<a href=\"foro.php?id={$id}&amp;p={$i}\">{$i}</a> ";
        else
          echo "<b>{$i}</b> ";
      }
      if ($fin < $npag)
        echo '... ';
    }
    echo '<br/><br/>';
    echo "<a href=\"foro.php\">Volver</a><br/>";
    if (isset($_SESSION['lasttext'])) {
      $lasttext = stripslashes($_SESSION['lasttext']);
      unset($_SESSION['lasttext']);
    }
    else
      $lasttext = '';

    echo '<br/><br/>';
    echo '<form action="foro.php" method="post">';
    echo '<div>';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo "<input type=\"hidden\" name=\"id\" value=\"{$id}\"/>";
  if ($_SESSION['silenciado']) {
    $dia_t = ahora_dia($_SESSION['silenciado']);
    $hora_t = ahora_hora($_SESSION['silenciado']);
    echo "<font color=\"red\"><b>Estás siendo silenciado por un moderador, no puedes enviar mensajes hasta el {$dia_t} a las {$hora_t}</b></font>";
  }
  else {                                                                                                                                               echo "<table id=\"mensajet\"><tr><td>Nueva respuesta<br/><hr/><br/>Texto:<br/><textarea name=\"mensaje\" rows=\"7\" cols=\"60\">{$lasttext}</textarea><br/><br/><input type=\"submit\" name=\"nuevarespuesta\" onclick=\"return filterHOYGAN(this.form.mensaje.value) && c('Si escribe en el foro público insultos o palabras malsonantes, o amenazas fuera del sentido del juego, su cuenta podrá ser cerrada.')\" value=\"Nueva respuesta\"/></td></tr></table>";
  }
    echo '</div>';
    echo '</form>';
  }
  else {
    if ($dovfor) {
      db_lock("{$conftp}jugadores WRITE");
      $retval = db_query("UPDATE {$conftp}jugadores SET ultimoforo={$time} WHERE nombrejug='{$jug}'");
      db_unlock();
    }
    db_lock("{$conftp}forohebras READ,{$conftp}foromensajes READ,{$conftp}jugadores READ");
    $retval = db_query("SELECT id,nombre,{$conftp}forohebras.nombrejug,{$conftp}forohebras.hora,COUNT(*),insignia,clan FROM {$conftp}forohebras,{$conftp}foromensajes,{$conftp}jugadores WHERE {$conftp}jugadores.nombrejug={$conftp}forohebras.nombrejug AND id=idhebra GROUP BY id ORDER BY hora DESC LIMIT {$ini},15"); 
    $retval2 = db_query("SELECT COUNT(*) FROM {$conftp}forohebras");
    db_unlock();
    $ret2 = mysql_fetch_row($retval2);
    mysql_free_result($retval2);
    $numrows = mysql_num_rows($retval);

    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval);
      $dia = ahora_dia($ret[3]);
      $hora = ahora_hora($ret[3]);
      $insignia = "<img src=\"{$imgroot}img/{$ret[5]}.gif\" alt=\"insignia\"/>";
      $resp = $ret[4]-1;
      if ($esmodera) 
        echo "<form action=\"foro.php\" method=\"post\">";
      echo "<div>";
      if ($esmodera) {
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idhebra\" value=\"{$ret[0]}\"/>";
        echo "<input type=\"hidden\" name=\"p\" value=\"{$p}\"/>";
        echo "<input type=\"submit\" name=\"eliminar\" onclick=\"return c('Como moderador debe eliminar únicamente los mensajes realmente problemáticos.')\" value=\"Eliminar\"/> ";
      }
      if ($us['ultimoforo']<=$ret[3])
        echo '<img src="'.$imgroot.'img/nuevo.png" alt="Nuevo"/> ';
      else
        echo '<img src="'.$imgroot.'img/leido.png" alt="Leido"/> ';
      $pags = '';
      $npags = ($resp+1)/15;
      $npag = floor($npags);
      if ($npag != $npags)
        $npag++;
      if ($npag > 1) {
        $pags .= "Página: ";
        $ini = $npag-3;
        $fin = $npag;
        if ($ini < 1)
          $ini = 1;
        if ($fin > $npag)
          $fin = $npag;
        if ($ini > 1)
          $pags .= '... ';
        for ($x = $ini;$x <= $fin;$x++)
          $pags .= "<a href=\"foro.php?id={$ret[0]}&amp;p={$x}\">{$x}</a> ";
      }
      $insigniac = "";
      if ($ret[6] != '(ninguno)') {
        db_lock("{$conftp}claninsignia READ");
        $retvalic = db_query("SELECT insignia FROM {$conftp}claninsignia WHERE clan='{$ret[6]}'");
        db_unlock();
        if (mysql_num_rows($retvalic) == 1) {
          $retic = mysql_fetch_row($retvalic);
          $insigniac = "<img style=\"vertical-align:middle\" width=24 height=24 src=\"{$imgroot}img/{$retic[0]}.gif\" alt=\"insignia\"/>";
        }
        mysql_free_result($retvalic);
        $clan = "{$insigniac}";
      }
      else
        $clan = '';
      echo "<a href=\"foro.php?id={$ret[0]}\">(ver) {$ret[1]}</a> {$pags} - {$insignia} <b>{$ret[2]}</b> {$clan} ({$dia} {$hora}) ({$resp} respuestas)";
      echo "</div>";
      if ($esmodera) {
        echo "</form>";
      }
      echo "<br/>";
    }
    mysql_free_result($retval);

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
          echo "<a href=\"foro.php?p={$i}\">{$i}</a> ";
        else
          echo "<b>{$i}</b> ";
      }
      if ($fin < $npag)
        echo '... ';
    }
  
    if (isset($_SESSION['lastsubject'])) {
      $lastsubject = stripslashes($_SESSION['lastsubject']);
      unset($_SESSION['lastsubject']);
    }
    else
      $lastsubject = '';
  
    if (isset($_SESSION['lasttext'])) {
      $lasttext = stripslashes($_SESSION['lasttext']);
      unset($_SESSION['lasttext']);
    }
    else
      $lasttext = '';
  
    echo '<br/><br/>';
    echo '<form action="foro.php" method="post">';
    echo '<div>';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
  if ($_SESSION['silenciado']) {
    $dia_t = ahora_dia($_SESSION['silenciado']);
    $hora_t = ahora_hora($_SESSION['silenciado']);
    echo "<font color=\"red\"><b>Estás siendo silenciado por un moderador, no puedes enviar mensajes hasta el {$dia_t} a las {$hora_t}</b></font>";
  }
  else {                                                                                                                                               echo "<table id=\"mensajet\"><tr><td>Nueva hebra<br/><hr/><br/>Asunto:<br/><input type=\"text\" name=\"subject\" size=\"60\" maxlength=\"40\" value=\"{$lastsubject}\"/><br/><br/>Texto:<br/><textarea name=\"mensaje\" rows=\"7\" cols=\"60\">{$lasttext}</textarea><br/><br/><input type=\"submit\" name=\"nuevahebra\" onclick=\"return filterHOYGAN(this.form.mensaje.value) && filterHOYGAN(this.form.subject.value) && c('Si escribe en el foro público insultos o palabras malsonantes, o amenazas fuera del sentido del juego, su cuenta podrá ser cerrada.')\" value=\"Nueva hebra\"/></td></tr></table>";
  }
    echo '</div>';
    echo '</form>';
  }
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$time,$confmaxmsg,$conftp,$us;
  if (isset($_REQUEST['p']))
    $p = $_REQUEST['p'];
  else
    $p = 1;
  if (!is_numeric($p))
    $p = 1;
  if (isset($_REQUEST['id']))
    $id = $_REQUEST['id'];
  else
    $id = 0;
  if (!is_numeric($id))
    $id = 0;

  if (isset($_REQUEST['nuevahebra'])) {
    if ($_SESSION['silenciado']) {
      $_SESSION['error'] = 'No puedes hablar, estás siendo silenciado por un moderador.';
    }
    else if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['subject'])) && (isset($_REQUEST['mensaje']))) {
      $gtag = $_REQUEST['tag'];
      $subject = check_subject($_REQUEST['subject']);
      $mensorig = check_text($_REQUEST['mensaje']);
      $mensaje = str_replace("\n",'<br/> ',$mensorig);
      if ($tag == $gtag) {
        if (strlen($mensorig) > $confmaxmsg) {
          $_SESSION['lastsubject'] = $subject;
          $_SESSION['lasttext'] = substr($mensorig,0,$confmaxmsg-2);
          $_SESSION['error'] = 'Mensaje demasiado largo, ha sido recortado. Reenvíe para confirmar';
        }
        else {
          if ((strlen($mensaje)) && (strlen($subject))) {
            db_lock("{$conftp}forohebras WRITE,{$conftp}foromensajes WRITE");
            db_query("INSERT INTO {$conftp}forohebras (nombrejug,nombre,hora) VALUES ('{$jug}','{$subject}',{$time})");
            $retval = db_query("SELECT MAX(id) FROM {$conftp}forohebras WHERE nombrejug='{$jug}' AND nombre='{$subject}' AND hora={$time}");
            $ret = mysql_fetch_row($retval);
            mysql_free_result($retval);
            db_query("INSERT INTO {$conftp}foromensajes (nombrejug,hora,texto,idhebra) VALUES ('{$jug}',{$time},'{$mensaje}',{$ret[0]})");
            db_unlock();
            $_SESSION['mensaje'] = 'Hebra creada.';
            $id = 0;
            $p = 0;
          }
          else {
            $_SESSION['error'] = 'Tienes que indicar asunto y texto.';
            $_SESSION['lastsubject'] = $subject;
            $_SESSION['lasttext'] = $mensorig;
          } 
        }
      }
      else {
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
        $_SESSION['lastsubject'] = $subject;
        $_SESSION['lasttext'] = $mensorig;
      }
    }
    header("Location: foro.php?id={$id}&p={$p}");
    return 1;
  }

  if (isset($_REQUEST['eliminar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idhebra']))) {
      $gtag = $_REQUEST['tag'];
      $idhebra = $_REQUEST['idhebra'];
      if (($us['admin']) || ($us['moderador']))
        $esmodera = 1;
      else
        $esmodera = 0;
      if ($tag == $gtag) {
        if ($esmodera) {
          if (is_numeric($idhebra)) {
            db_lock("{$conftp}forohebras WRITE,{$conftp}foromensajes WRITE");
            db_query("DELETE FROM {$conftp}forohebras WHERE id={$idhebra}");
            db_query("DELETE FROM {$conftp}foromensajes WHERE idhebra={$idhebra}");
            db_unlock();
            $_SESSION['mensaje'] = 'Hebra eliminada.';
          }
          else
          $_SESSION['error'] = 'ID no válida.';
        }
        else
          $_SESSION['error'] = 'No eres moderador.';
      }
      else 
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: foro.php?p={$p}");
    return 1;
  }

  if (isset($_REQUEST['eliminarm'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idmensaje']))) {
      $gtag = $_REQUEST['tag'];
      $idmensaje = $_REQUEST['idmensaje'];
      if (($us['admin']) || ($us['moderador']))
        $esmodera = 1;
      else
        $esmodera = 0;
      if ($tag == $gtag) {
        if ($esmodera) {
          if (is_numeric($idmensaje)) {
            db_lock("{$conftp}foromensajes WRITE");
            db_query("DELETE FROM {$conftp}foromensajes WHERE idmensaje={$idmensaje}");
            db_unlock();
            $_SESSION['mensaje'] = 'Mensaje eliminado.';
          }
          else
          $_SESSION['error'] = 'ID no válida.';
        }
        else 
          $_SESSION['error'] = 'No eres moderador.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header("Location: foro.php?p={$p}&id={$id}");
    return 1;
  }


  if (isset($_REQUEST['nuevarespuesta'])) {
    if ($_SESSION['silenciado']) {
      $_SESSION['error'] = 'No puedes hablar, estás siendo silenciado por un moderador.';
    }
    else if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['id'])) && (isset($_REQUEST['mensaje']))) {
      $gtag = $_REQUEST['tag'];
      if (is_numeric($_REQUEST['id']))
        $ids = $_REQUEST['id'];
      else
        $ids = 0;
      $mensorig2 = $_REQUEST['mensaje'];
      $mensorig = check_text($_REQUEST['mensaje']);
      $mensaje = str_replace("\n",'<br/> ',$mensorig);
      if ($tag == $gtag) {
        if (strlen(stripslashes($mensorig2)) > $confmaxmsg) {
          $_SESSION['lasttext'] = substr($mensorig2,0,$confmaxmsg-4);
          $_SESSION['error'] = 'Mensaje demasiado largo, ha sido recortado. Reenvíe para confirmar';
        }
        else {
          db_lock("{$conftp}forohebras READ");
          $r = db_query("SELECT * FROM {$conftp}forohebras WHERE id={$ids}");
          db_unlock();
          if (mysql_num_rows($r)) {
            if (strlen($mensaje)) {
              db_lock("{$conftp}forohebras WRITE,{$conftp}foromensajes WRITE");
              db_query("UPDATE {$conftp}forohebras SET hora={$time} WHERE id={$ids}");
              db_query("INSERT INTO {$conftp}foromensajes (nombrejug,hora,texto,idhebra) VALUES ('{$jug}',{$time},'{$mensaje}',{$ids})");
              $retval3 = db_query("SELECT COUNT(*) FROM {$conftp}foromensajes WHERE idhebra={$ids}");
              db_unlock();
              $_SESSION['mensaje'] = 'Respuesta añadida.';
              $ret2 = mysql_fetch_row($retval3);
              mysql_free_result($retval3);
              $npags = $ret2[0]/15;
              $npag = floor($npags);
              if ($npag != $npags)
                $npag++;
              $p = $npag;
            }
            else {
              $_SESSION['error'] = 'Tienes que indicar texto.';
              $_SESSION['lasttext'] = $mensorig;
            }
          }
          else {
            $_SESSION['error'] = 'Esa hebra no existe.';
          }
          mysql_free_result($r);
        }
      }
      else {
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
        $_SESSION['lasttext'] = $mensorig;
      }
    }
    header("Location: foro.php?id={$id}&p={$p}");
    return 1;
  }

}
?>
