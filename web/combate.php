<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tag,$time,$conforonivelataca,$confdifnivel,$conftp,$us,$imgroot;
  if (($us['oro'] >= $conforonivelataca*$us['nivel']) && ($us['protegidohasta']<$time))
    echo '<b>Actualmente puedes ser atacado por otros jugadores.</b><br/><br/>';
  else
    echo '<b>Actualmente no puedes ser atacado por otros jugadores.</b><br/><br/>';
  if (($us['oro'] >= $conforonivelataca*$us['nivel']) && ($us['noatacarhasta']<$time) && ($us['energia']>=5))
    $pat = 1;
  else
    $pat = 0;
  if (!$pat) {
    $pat_d = ahora_dia($us['noatacarhasta']);
    $pat_h = ahora_hora($us['noatacarhasta']);
    echo '¡Aun no puedes volver a atacar!<br/>';
    $oronec = $us['nivel']*$conforonivelataca;
    if ($us['oro']<$oronec)
      echo "Tienes sólo {$us['oro']} monedas de oro y necesitas {$oronec} para poder atacar.<br/>";
    if ($us['noatacarhasta']>=$time)
      echo "Tienes que esperar hasta el {$pat_d} a las {$pat_h} para poder volver a atacar.<br/>";
    if ($us['energia']<5)
      echo "Necesitas tener al menos 5 puntos de energía para combatir.<br/>";
    return;
  }
  $min = $us['nivel']-$confdifnivel;
  $max = $us['nivel']+$confdifnivel;
  if ($min < 1) 
    $min = 1;
  db_lock("{$conftp}jugadores READ");
  $retvol = db_query("SELECT {$conftp}jugadores.nombrejug,nivel,puntos,insignia,combates,vencedor,clan FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND protegidohasta<{$time} AND nivel>={$min} AND nivel<={$max} AND nombrejug<>'{$jug}' AND (clan='(ninguno)' OR clan<>'{$us['clan']}') ORDER by puntos DESC LIMIT 6");
  db_unlock();
  techo('Así que te has decidido a atacar a alguien... ¡Genial! Combatiendo por las calles ganarás experiencia y mucho oro. Puedes elegir entre atacar a un viandante al azar (si cumple los requisitos para ser atacado), o atacar a un jugador de quien conozcas el nombre (si cumple los requisitos para ser atacado). Sólo puedes atacar una vez cada cierto tiempo, y el jugador atacado quedará protegido hasta que pase algún tiempo o hasta que él se decida a atacar. ¡Suerte!');
  echo "Tienes <b>{$us['energia']}</b> puntos de energía.<br/><br/>";

  $numsubs = mysql_num_rows($retvol);
  if ($numsubs) {
    echo 'Tienes dos opciones:<br/><br/>';
    echo '<form method="post" action="combate.php">';
    echo '<div>';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo 'Puedes atacar a alguien al azar:<br/><br/>';
    echo '<input type="submit" name="atacaazar" onclick="return c()" value="Atacar a alguien al azar"/><br/><br/><br/>';
    echo '</div>';
    echo '</form>';
    echo '<form method="post" action="combate.php">';
    echo '<div>';
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo 'O bien puedes indicar un jugador al que atacar:<br/><br/>';
    echo 'Jugador a atacar: <input type="text" name="jugador" size="30" maxlength="30"/><br/><input type="submit" name="atacajugador" onclick="return c()" value="Atacar al jugador indicado"/><br/>';
    echo '</div>';
    echo '</form><br/><br/>';
    echo '<b>Estos son algunos de los jugadores a los que puedes atacar:</b><br/>';
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
  else
    echo '<b>Ningún jugador cumple los requisitos para ser atacado por ti actualmente.</b>';
  mysql_free_result($retvol);
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$time,$conforonivelataca,$confdifnivel,$conftp;
  if (isset($_REQUEST['atacaazar'])) {
    $salirnow = 0;
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        $adelante = 0;
        db_lock("{$conftp}jugadores WRITE,{$conftp}mensajes WRITE,{$conftp}objetos READ,{$conftp}tiene WRITE,{$conftp}mascotas READ,{$conftp}tienemascotas WRITE,{$conftp}claninsignia READ");
        $retval = db_query("SELECT nivel,clan FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        if (puedeatacar($jug)) {
          $ret = mysql_fetch_row($retval);
          $min = $ret[0]-$confdifnivel;
          $max = $ret[0]+$confdifnivel;
          if ($min < 1)
            $min = 1;
          $retvalo = db_query("SELECT nombrejug FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND protegidohasta<{$time} AND nivel>={$min} AND nivel<={$max} AND nombrejug<>'{$jug}' AND (clan='(ninguno)' OR clan<>'{$ret[1]}')");
          $num = mysql_num_rows($retvalo);
          if ($num) {
            mysql_data_seek($retvalo,rand(0,$num-1));
            $ret = mysql_fetch_row($retvalo);
            $_SESSION['mensaje'] = 'Obtenidos resultados del combate.';
            combate($jug,$ret[0],1);
            $adelante = 1;
            $salirnow = 1;
          }
          else
            $_SESSION['error'] = 'No has conseguido encontrar un contrincante.';
          mysql_free_result($retvalo);
	}
	else
	  $_SESSION['error'] = 'Ahora mismo no cumples los requisitos para atacar.';
        db_unlock();
        if ($adelante) {
          db_lock("{$conftp}contador WRITE");
          db_query("UPDATE {$conftp}contador SET contador=0");
          db_unlock();
        }
        mysql_free_result($retval);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    if ($salirnow) {
      header('Location: mensajeria.php');
      sumapuntos();
    }
    else
      header('Location: combate.php');
    return 1;
  }


  if (isset($_REQUEST['atacajugador'])) {
    $salirnow = 0;
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['jugador']))) {
      $gtag = $_REQUEST['tag'];
      $jugador = check_username($_REQUEST['jugador']);
      if ($jugador) {
        if ($tag == $gtag) {
          $adelante = 0;
          if ($jug != $jugador) {
            db_lock("{$conftp}jugadores WRITE,{$conftp}mensajes WRITE,{$conftp}objetos READ,{$conftp}tiene WRITE,{$conftp}mascotas READ,{$conftp}tienemascotas WRITE,{$conftp}claninsignia READ");
            if (puedeatacar($jug)) {
              $retval = db_query("SELECT * FROM {$conftp}jugadores WHERE nombrejug='{$jugador}'");
              if (mysql_num_rows($retval)) {
                $retvalo = db_query("SELECT nivel,clan FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
                $ret = mysql_fetch_row($retvalo);
                mysql_free_result($retvalo);
                $min = $ret[0]-$confdifnivel;
                $max = $ret[0]+$confdifnivel;
                if ($min < 1)
                  $min = 1;
                if ($jugador == 'Xilok')
                  $retvalo = db_query("SELECT * FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND protegidohasta<{$time} AND nivel>={$min}-10 AND nivel<={$max}+10 AND nombrejug='{$jugador}' AND (clan='(ninguno)' OR clan<>'{$ret[1]}')");
                else
                  $retvalo = db_query("SELECT * FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND protegidohasta<{$time} AND nivel>={$min} AND nivel<={$max} AND nombrejug='{$jugador}' AND (clan='(ninguno)' OR clan<>'{$ret[1]}')");
                if (mysql_num_rows($retvalo)) {
  	        $_SESSION['mensaje'] = 'Obtenidos resultados del combate.';
                  combate($jug,$jugador,0);
                  $adelante = 1;
                  $salirnow = 1;
                }
                else
                  $_SESSION['error'] = 'Este jugador no cumple los requisitos para combatir contigo.';
                mysql_free_result($retvalo);
              }
              else
                $_SESSION['error'] = 'El jugador indicado no existe.';
              mysql_free_result($retval);
            }
            else
              $_SESSION['error'] = 'Ahora mismo no cumples los requisitos para atacar.';
            db_unlock();
          }
          else
            $_SESSION['error'] = 'No puedes atacarte a ti mismo.';
          if ($adelante) {
            db_lock("{$conftp}contador WRITE");
            db_query("UPDATE {$conftp}contador SET contador=0");
            db_unlock();
          }
        }
        else
          $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
      }
      else
        $_SESSION['error'] = 'Nombre de jugador a atacar no válido.';
    }
    if ($salirnow) {
      header('Location: mensajeria.php');
      sumapuntos();
    }
    else
      header('Location: combate.php');
    return 1;
  }


}

?>
