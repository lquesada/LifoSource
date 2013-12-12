<?php

function l_setdate($zh) {
  global $zonahhhh,$zonaact;
  $zonahhhh = $zh;
  if ($zh == -15)
    $zonahhhh = -2;
  $zonahhhh -= $zonaact;
}
function l_getdate($tiempo) {
  global $zonahhhh;
  return $tiempo-$zonahhhh*3600;
}

/*pwdHash($username,$password) Herramienta de cifrado de contraseña.*/
function pwdHash($username,$password) {
  global $confpwdsalt;
  return sha1("{$username}{$password}{$confpwdsalt}");
}

/*pwdgen(); Herramienta que genera una contraseña. */
function pwdgen() {
  $out = '';
  $string = 'abcdefghijklmnopqrstuvwxyz';
  $len = strlen($string);
  for ($i = 0;$i < 7;$i++) {
    $out .= $string[rand(0,10)];
  }
  return $out;
}
 

/*ahora_dia($time) Conversor a dia en formato texto. */
function ahora_dia($time) {
  return date('d-m-Y',l_getdate($time));
}

/*ahora_hora($time) Conversor a hora en formato texto. */
function ahora_hora($time) {
  return date('H:i:s',l_getdate($time));
}

/* expsignivel($nivactual,$ultimossubio) Devuelve la experiencia necesaria para alcanzar el siguiente nivel. */
function expsignivel($nivactual,$ultimossubio) {
  global $confnivel;
  $incremento = 0;
  if ($nivactual >= 40)
    $incremento += ($nivactual-39)*$confnivel*2;
  if ($nivactual >= 55)
    $incremento += ($nivactual-54)*$confnivel*2;
  if ($nivactual >= 60)
    $incremento += ($nivactual-59)*$confnivel*2;
  if ($nivactual >= 65)
    $incremento += ($nivactual-64)*$confnivel*2;
  if ($nivactual >= 69)
    $incremento += ($nivactual-68)*$confnivel*2;

  return $ultimossubio+$incremento+$nivactual*$confnivel;
}

/* ahora_tiempo($cuanto) Devuelve el tiempo. */
function ahora_tiempo($cuanto) {
  $horas = floor($cuanto/3600);
  $cuanto = $cuanto%3600;
  $minutos = floor($cuanto/60);
  $cuanto = $cuanto%60;
  $segundos = $cuanto;

  if ($horas < 10)
    $horas = "0{$horas}";
  if ($minutos < 10)
    $minutos = "0{$minutos}";
  if ($segundos < 10)
    $segundos = "0{$segundos}";

  return "{$horas}:{$minutos}:{$segundos}";
}


/* send_mail($email,$subject,$text) Envía un mail a una dirección de correo. */
function send_mail($email,$subject,$text) {
  //desactivado
  return;
  global $confmail,$confmailn,$conftp,$time;
  $email = check_email($email);
  if (!$email)
    return -1;
  $headers =  "MIME-Version: 1.0\n";
  $headers .= "Content-type: text/html; charset=iso 8859-1\n";
  $headers .= "Content-Transfer-Encoding: 8bit\n";
  $headers .= "To: {$email}\n";
  $headers .= "From: \"{$confmailn}\" <{$confmail}>\n";
  $headers .= "Reply-To: {$confmail}\n";
  $headers .= "Subject: $subject\n";
  $headers .= "X-Priority: 3\n";
  $headers .= "X-Mail-Priority: Normal\n";
  $headers .= "X-Mailer: PHP Mailer\n";
  $send = str_replace('"','\\"',$headers."\n<html><body>".str_replace('$','\\$',$text)."</body></html>\n");
  exec('(bash -c \'/usr/bin/msmtp -C /etc/msmtp.ini '.$email.' <<< "'.$send.'"\') >/dev/null 2>/dev/null &');
}

function antiflood($evento,$actor,$max,$tiempo) {
  global $time,$conftp;
  $timelimit = $time-$tiempo;
  $return = 0;
  db_lock("{$conftp}flood WRITE");
  db_query("DELETE FROM {$conftp}flood WHERE evento='{$evento}' AND tiempo<{$timelimit}");
  $retval = db_query("SELECT COUNT(*) FROM {$conftp}flood WHERE evento='{$evento}' AND actor='{$actor}' AND tiempo>=$timelimit");
  $ret = mysql_fetch_row($retval);
  if ($ret[0] < $max) {
    db_query("INSERT INTO {$conftp}flood (evento,actor,tiempo) VALUES ('{$evento}','{$actor}',$time)");
    $return = 1;
  }
  db_unlock();
  mysql_free_result($retval);
  return $return;
}
function antifloodcheck($evento,$actor,$max,$tiempo) {
  global $time,$conftp;
  $timelimit = $time-$tiempo;
  $return = 0;
  db_lock("{$conftp}flood WRITE");
  db_query("DELETE FROM {$conftp}flood WHERE evento='{$evento}' AND tiempo<{$timelimit}");
  $retval = db_query("SELECT COUNT(*) FROM {$conftp}flood WHERE evento='{$evento}' AND actor='{$actor}' AND tiempo>=$timelimit");
  $ret = mysql_fetch_row($retval);
  if ($ret[0] < $max) {
    $return = 1;
  }
  db_unlock();
  mysql_free_result($retval);
  return $return;
}
function antifloodc($evento,$actor) {
  global $conftp;
  db_lock("{$conftp}flood WRITE");
  db_query("DELETE FROM {$conftp}flood WHERE evento='{$evento}' AND actor='{$actor}'");
  db_unlock();
}

function puedeatacar($jugador) {
  global $time,$conforonivelataca,$conftp;
  $retval = db_query("SELECT * FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND noatacarhasta<{$time} AND nombrejug='{$jugador}' AND energia>=5");
  $a = mysql_num_rows($retval);
  mysql_free_result($retval);
  return $a;
}

function puedeseratacado($jugador) {
  global $time,$conforonivelataca,$conftp;
  db_lock("{$conftp}jugadores READ");
  $retval = db_query("SELECT * FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND protegidohasta<{$time} AND nombrejug='{$jugador}'");
  db_unlock();
  $a = mysql_num_rows($retval);
  mysql_free_result($retval);
  return $a;
}

function combateinfo($nombre) {
  global $conftp,$time,$confnoatacarhastapremium,$confnoatacarhasta;
  $retval = db_query("SELECT oro,puntossuma,puntos,nivel,combates,vencedor,vencido,insignia,clan,premiumhasta,energia,moderador,enchufado FROM {$conftp}jugadores WHERE nombrejug='{$nombre}'");
  $ret = mysql_fetch_row($retval);
  $juga['nombre'] = $nombre;
  $juga['oro'] = $ret[0];
  $juga['puntossuma'] = $ret[1];
  $juga['puntossumasec'] = $ret[1];
  $juga['puntos'] = $ret[2];
  $juga['nivel'] = $ret[3];
  $juga['premiumhasta'] = $ret[9];
  if ($ret[11] || $ret[12]) {
    $juga['premiumhasta'] = $time+10;
  }
  $juga['combates'] = $ret[4]+1;
  $juga['vencedor'] = $ret[5];
  $juga['vencido'] = $ret[6];
  $juga['insignia'] = $ret[7];
  $juga['clan'] = $ret[8];
  $juga['energia'] = $ret[10];
  $retval = db_query("SELECT SUM(ataq),SUM(prot) FROM {$conftp}tiene,{$conftp}objetos WHERE usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$nombre}'");
  $ret = mysql_fetch_row($retval);
  $juga['ataq'] = $ret[0]+floor($juga['nivel']/3)+10;
  $juga['prot'] = floor(($ret[1]+floor(($juga['nivel'])/10)+10)*0.6);
  mysql_free_result($retval);
  $juga['ataqmascota'] = 0;
  $juga['protmascota'] = 0;

  $retvox = db_query("SELECT {$conftp}tienemascotas.nombremascota,img,nivel,experiencia,alimento,ataquebase,defensabase,ataquenivel,defensanivel,expbase,expmult,expgana,maxnivel,usado,expgana FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}tienemascotas.nombremascota={$conftp}mascotas.nombremascota AND nombrejug='{$nombre}' AND usado=1");
  if (mysql_num_rows($retvox)) {
    $rrr = mysql_fetch_row($retvox);
    $juga['mascota'] = $rrr[1];
    $juga['expmascota'] = $rrr[14];
    $juga['nombremascota'] = $rrr[0];
    $juga['ataqmascota'] = $rrr[5]+($rrr[7]*($rrr[2]-1));
    $juga['protmascota'] = $rrr[6]+($rrr[8]*($rrr[2]-1));
  }
  else {
    $juga['mascota'] = '';
    $juga['expmascota'] = 0;
    $juga['nombremascota'] = '';
    $juga['ataqmascota'] = 0;
    $juga['protmascota'] = 0;
  }
  $juga['ataq'] = $juga['ataq']+$juga['ataqmascota'];
  $juga['prot'] = $juga['prot']+$juga['protmascota'];
  $juga['ataqprotmascota'] = $juga['ataqmascota']+$juga['protmascota'];
  $juga['ataqprot'] = $juga['ataq']+$juga['prot'];
  mysql_free_result($retvox);

  if ($juga['clan'] == '(ninguno)') {
    $juga['clanpuntos'] = 0;
    $juga['claninsignia'] = 0;
  }
  else {
    $retvx = db_query("SELECT sum(puntos) p FROM {$conftp}jugadores GROUP BY clan HAVING clan='{$juga['clan']}'");
    $rxx = mysql_fetch_row($retvx);
    $juga['clanpuntos'] = $rxx[0];
    mysql_free_result($retvx);
    $retvx = db_query("SELECT insignia FROM {$conftp}claninsignia WHERE clan='{$juga['clan']}'");
    $rxx = mysql_fetch_row($retvx);
    $juga['claninsignia'] = $rxx[0];
    mysql_free_result($retvx);
  }
  return $juga;
}

function gestionaataque(&$tacante,&$tacado,$motivo) {
  if ($motivo) {
    $motivo = ' '.$motivo;
  }
  $mensaje = "<b>{$tacante['nombre']}</b> se lanza hacia <b>{$tacado['nombre']}</b>{$motivo}.<br/>";

  $ataque = rand(1,$tacante['ataq']*3);
  $defensa = rand(1,$tacado['prot']*3);
  if ($ataque > $defensa) {
    $ataque = floor($ataque/5);
    $defensa = floor($defensa/5);
    if ($ataque == 0)
      $ataque = 1;
    if ($defensa == 0)
      $defensa = 1;
    if (rand(1,5) == 5) {
      $ataque = rand(2,5)*$ataque;
      $tacado['vida'] = $tacado['vida']-$ataque;
      $mensaje .= "¡<b>{$tacante['nombre']}</b> asesta un golpe crítico a <b>{$tacado['nombre']}</b> por <b>{$ataque}</b> puntos de resistencia!<br/>";
    }
    else {
      $tacado['vida'] = $tacado['vida']-$ataque;
      $mensaje .= "¡<b>{$tacante['nombre']}</b> asesta un golpe a <b>{$tacado['nombre']}</b> por <b>{$ataque}</b> puntos de resistencia!<br/>";
    }
  }
  else {
    $mensaje .= "¡<b>{$tacado['nombre']}</b> detiene el ataque de <b>{$tacante['nombre']}</b>!<br/>";
  }
  if ($tacante['vida'] < 0)
    $tacante['vida'] = 0;
  if ($tacado['vida'] < 0)
    $tacado['vida'] = 0;
  $mensaje .= '<br/>';
  return $mensaje;
}
function infocombate($tacante,$tacado) {
  return "<b>{$tacante['nombre']}</b> (puntos de resistencia: <b>{$tacante['vida']}</b>).<br/><b>{$tacado['nombre']}</b> (puntos de resistencia: <b>{$tacado['vida']}</b>).<br/><br/>";
}

function combate($atacante,$atacado,$aleat) {
  global $confnoatacarhasta,$time,$confprotegidohasta,$conforonivelataca,$confganacombexpmax,$confganacombexpmin,$confnoatacarhastapremium,$conftp,$imgroot;
  $tacante = combateinfo($atacante);
  $tacado = combateinfo($atacado);
  if ($tacante['premiumhasta'] >= $time)
    $tacante['noatacarhasta'] = $time+$confnoatacarhastapremium;
  else
    $tacante['noatacarhasta'] = $time+$confnoatacarhasta;
  $tacado['protegidohasta'] = $time+$confprotegidohasta;


  if ($tacante['clan'] == '(ninguno)')
    $tteclan = '';
  else {
    if ($tacante['claninsignia'] == '')
      $tteclan = " del clan <b>{$tacante['clan']}</b>";
    else
      $tteclan = " del clan <img style=\"vertical-align:middle\" src=\"{$imgroot}img/{$tacante['claninsignia']}.gif\" alt=\"insignia\"/> <b>{$tacante['clan']}</b>";
  }
  if ($tacado['clan'] == '(ninguno)')
    $tdoclan = '';
  else {
    if ($tacado['claninsignia'] == '')
      $tdoclan = " del clan <b>{$tacado['clan']}</b>";
    else
      $tdoclan = " del clan <img style=\"vertical-align:middle\" src=\"{$imgroot}img/{$tacado['claninsignia']}.gif\" alt=\"insignia\"/> <b>{$tacado['clan']}</b>";
  }

  if (!$aleat)
    $mensa2 = '<font color="#DDDDDD">_</font>';
  else
    $mensa2 = '';

  $mensaje = "<b>¡Combate!</b> <img style=\"vertical-align:middle\" src=\"{$imgroot}img/{$tacante['insignia']}.gif\" alt=\"insignia\"/> <b>{$atacante}</b> (nivel {$tacante['nivel']}){$tteclan} ha atacado a <img style=\"vertical-align:middle\" src=\"{$imgroot}img/{$tacado['insignia']}.gif\" alt=\"insignia\"/> <b>{$atacado}</b> (nivel {$tacado['nivel']}){$tdoclan}.{$mensa2}<br/><br/>";

  $retval = db_query("SELECT {$conftp}objetos.img,{$conftp}objetos.nombreobj FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$atacante}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}tiene.usado=1 ORDER BY tipo ASC");
  $mensaje .= "<b>{$atacante}</b> está usando: "; 
  if ($tacante['mascota'])
    $mensaje .= "<img src=\"{$imgroot}img/{$tacante['mascota']}.gif\" alt=\"{$tacante['nombremascota']}\"/>";
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    $mensaje .= "<img src=\"{$imgroot}img/{$ret[0]}.gif\" alt=\"{$ret[1]}\"/>";
  }
  $mensaje .= '<br/>';
  mysql_free_result($retval);

  $retval = db_query("SELECT {$conftp}objetos.img,{$conftp}objetos.nombreobj FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$atacado}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}tiene.usado=1 ORDER BY tipo ASC");
  $mensaje .= "<b>{$atacado}</b> está usando: ";
  if ($tacado['mascota'])
    $mensaje .= "<img src=\"{$imgroot}img/{$tacado['mascota']}.gif\" alt=\"{$tacado['nombremascota']}\"/>";
  $numrows = mysql_num_rows($retval);
  for ($i = 0;$i < $numrows;$i++) {
    $ret = mysql_fetch_row($retval);
    $mensaje .= "<img src=\"{$imgroot}img/{$ret[0]}.gif\" alt=\"{$ret[1]}\"/>";
  }
  $mensaje .= '<br/><br/>';
  mysql_free_result($retval);


  $orooblig = $conforonivelataca*$tacado['nivel'];
  if ($conforonivelataca*$tacante['nivel'] < $orooblig)
    $orooblig = $conforonivelataca*$tacante['nivel'];
  $oromax = $tacado['oro'];
  if ($tacante['oro'] < $oromax)
    $oromax = $tacante['oro'];
  $oromax = floor($oromax / 6);
  $orooblig = floor($orooblig / 6);
  $oroapuesta = rand(1,$oromax);
  if ($orooblig > $oroapuesta) {
    $aux = $orooblig;
    $orooblig = $oroapuesta;
    $oroapuesta = $aux;
  }
  $oro_ganador = rand($orooblig,$oroapuesta);
  $exptacante = floor(($tacante['puntos']+$tacado['puntos']*rand(10,20))/4000);
  $exptacado = floor(($tacado['puntos']+$tacante['puntos']*rand(10,20))/4000);
  if (!$exptacante)
    $exptacante = 10;
  if (!$exptacado)
    $exptacado = 10;

  $tacante['vida'] = 10000;
  $tacado['vida'] = 10000;
  $turno = 1;

  if ($tacante['vida']>0 && $tacado['vida']>0) {
  if ($tacante['clanpuntos']>$tacado['clanpuntos']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacante,$tacado,'porque está en un clan mejor');
    $turno++;
  }
  else if ($tacado['clanpuntos']>$tacante['clanpuntos']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacado,$tacante,'porque está en un clan mejor');
    $turno++;
  }
  }

  if ($tacante['vida']>0 && $tacado['vida']>0) {
  if ($tacante['ataqprotmascota']>$tacado['ataqprotmascota']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacante,$tacado,'porque tiene una mascota más fuerte');
    $turno++;
  }
  else if ($tacado['ataqprotmascota']>$tacante['ataqprotmascota']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacado,$tacante,'porque tiene una mascota más fuerte');
    $turno++;
  }
  }

  if ($tacante['vida']>0 && $tacado['vida']>0) {
  if ($tacante['nivel']>$tacado['nivel']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacante,$tacado,'porque tiene más nivel');
    $turno++;
  }
  else if ($tacado['nivel']>$tacante['nivel']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacado,$tacante,'porque tiene más nivel');
    $turno++;
  }
  }

  if ($tacante['vida']>0 && $tacado['vida']>0) {
  if ($tacante['energia']>$tacado['energia']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacante,$tacado,'porque tiene más energía');
    $turno++;
  }
  else if ($tacado['energia']>$tacante['energia']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacado,$tacante,'porque tiene más energía');
    $turno++;
  }
  }

  if ($tacante['vida']>0 && $tacado['vida']>0) {
  if ($tacante['ataqprot']>$tacado['ataqprot']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacante,$tacado,'porque tiene mejor ataque y protección');
    $turno++;
  }
  else if ($tacado['ataqprot']>$tacante['ataqprot']) {
    $mensaje .= infocombate($tacante,$tacado);
    $mensaje .= gestionaataque($tacado,$tacante,'porque tiene mejor ataque y protección');
    $turno++;
  }
  }

  if ($turno%2==0)
    $turno--;
  while ($turno <= 10) {
    if ($tacante['vida']>0 && $tacado['vida']>0) {
      $mensaje .= infocombate($tacante,$tacado);
      if ($turno%2==0)
        $mensaje .= gestionaataque($tacado,$tacante,'');
      else
        $mensaje .= gestionaataque($tacante,$tacado,'');
    }
    $turno++;
  }

  $mensaje .= infocombate($tacante,$tacado);

  if ($tacante['vida'] > $tacado['vida']) {
    $mensaje .= "¡<b>{$atacante} ha ganado el combate!</b><br/>";
    $ganador = 'atacante';
  }
  else {
    $mensaje .= "¡<b>{$atacado} ha ganado el combate!</b><br/>";
    $ganador = 'atacado';
  }

  if ($ganador == 'atacante') {
    $tacante['vencedor'] = $tacante['vencedor']+1;
    $tacado['vencido'] = $tacado['vencido']+1;
    if (rand(0,3))
      $exptacado = $exptacado*-1;
    $tacante['oro'] = $tacante['oro'] + $oro_ganador;
    $tacado['oro'] = $tacado['oro'] - $oro_ganador;
    $mensaje .= "<b>¡{$atacante} roba a {$atacado} {$oro_ganador} monedas de oro!</b><br/>";
    $tacante['puntossuma'] = $tacante['puntossuma'] + $exptacante;
    $tacado['puntossuma'] = $tacado['puntossuma'] + $exptacado;
    if ($tacante['puntossuma'] <= $tacado['puntossuma'])
      $tacante['puntossuma'] = $tacado['puntossuma']+1;
    if ($tacante['mascota'])
      db_query("UPDATE tienemascotas SET experiencia=experiencia+{$tacante['expmascota']} WHERE nombrejug='{$atacante}' AND nombremascota='{$tacante['nombremascota']}'");
  }
  else {
    $tacado['vencedor'] = $tacado['vencedor']+1;
    $tacante['vencido'] = $tacante['vencido']+1;
    if (rand(0,5))
      $exptacante = $exptacante*-1;
    $tacante['oro'] = $tacante['oro'] - $oro_ganador;
    $tacado['oro'] = $tacado['oro'] + $oro_ganador;
    $mensaje .= "<b>¡{$atacado} roba a {$atacante} {$oro_ganador} monedas de oro!</b><br/>";
    $tacante['puntossuma'] = $tacante['puntossuma'] + $exptacante;
    $tacado['puntossuma'] = $tacado['puntossuma'] + $exptacado;
    if ($tacado['puntossuma'] <= $tacante['puntossuma'])
      $tacado['puntossuma'] = $tacante['puntossuma']+1;
    if ($tacado['mascota'])
      db_query("UPDATE tienemascotas SET experiencia=experiencia+{$tacado['expmascota']} WHERE nombrejug='{$atacado}' AND nombremascota='{$tacado['nombremascota']}'");
  }
  if ($tacante['puntos']+$tacante['puntossuma'] < 0)
    $tacante['puntossuma'] = $tacante['puntos']*-1;
  if ($tacado['puntos']+$tacado['puntossuma'] < 0)
    $tacado['puntossuma'] = $tacado['puntos']*-1;
  if ($tacante['puntossuma'] > $confganacombexpmax)
    $tacante['puntossuma'] = $confganacombexpmax;
  if ($tacado['puntossuma'] > $confganacombexpmax)
    $tacado['puntossuma'] = $confganacombexpmax;
  if ($tacante['puntossuma'] < $confganacombexpmin)
    $tacante['puntossuma'] = $confganacombexpmin;
  if ($tacado['puntossuma'] < $confganacombexpmin)
    $tacado['puntossuma'] = $confganacombexpmin;

  if (!$tacante['puntossuma'])
    $tacante['puntossuma'] = 1;
  if (!$tacado['puntossuma'])
    $tacado['puntossuma'] = 1;

  if ($ganador == 'atacante') {
    $expx = $tacante['puntossuma']-$tacante['puntossumasec'];
    $mensaje .= "<b>¡{$atacante} gana {$expx} puntos de experiencia!";
    if ($tacante['mascota'])
      $mensaje .= ' Su mascota se siente fuerte.';
    $mensaje .= "</b><br/>";

    $expx = $tacado['puntossuma']-$tacado['puntossumasec'];
    if ($expx < 0) {
      $expx = $expx*-1;
      $mensaje .= "<b>¡{$atacado} se desmoraliza y pierde {$expx} puntos de experiencia!</b><br/>";
    }
    else
      $mensaje .= "<b>¡{$atacado} ha aprendido de este combate y gana {$expx} puntos de experiencia!</b><br/>";
  }
  else {
    $expx = $tacado['puntossuma']-$tacado['puntossumasec'];
    $mensaje .= "<b>¡{$atacado} gana {$expx} puntos de experiencia!";
    if ($tacado['mascota'])
      $mensaje .= ' Su mascota se siente fuerte.';
    $mensaje .= "</b><br/>";
    $expx = $tacante['puntossuma']-$tacante['puntossumasec'];
    if ($expx < 0) {
      $expx = $expx*-1;
      $mensaje .= "<b>¡{$atacante} se desmoraliza y pierde {$expx} puntos de experiencia!</b><br/>";
    }
    else
      $mensaje .= "<b>¡{$atacante} ha aprendido de este combate y gana {$expx} puntos de experiencia!</b><br/>";

  }
  db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$atacante}','@',$time,'{$mensaje}')");
  db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$atacado}','@',$time,'{$mensaje}')");

  db_query("UPDATE {$conftp}jugadores SET oro={$tacante['oro']},puntossuma={$tacante['puntossuma']},noatacarhasta={$tacante['noatacarhasta']},protegidohasta=0,combates={$tacante['combates']},vencedor={$tacante['vencedor']},vencido={$tacante['vencido']} WHERE nombrejug='{$atacante}'");
  db_query("UPDATE {$conftp}jugadores SET oro={$tacado['oro']},puntossuma={$tacado['puntossuma']},protegidohasta={$tacado['protegidohasta']},combates={$tacado['combates']},vencedor={$tacado['vencedor']},vencido={$tacado['vencido']} WHERE nombrejug='{$atacado}'");

  $rxx = db_query("SELECT nombrejug,{$conftp}tiene.nombreobj,img FROM {$conftp}tiene,{$conftp}objetos WHERE (nombrejug='{$atacante}' OR nombrejug='{$atacado}') AND usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND usos=1");
  $nr = mysql_num_rows($rxx);
  for ($i = 0;$i < $nr;$i++) {
    $rex = mysql_fetch_row($rxx);
    quita_objeto($rex[0],$rex[1],$rex[2]);
  }
  mysql_free_result($rxx);
}
function techo($texto) {
  echo "<table class=\"texto\"><tr><td>{$texto}</td></tr></table>";
}

function addoferta($obj,$canti=1) {
  global $confmaxoferta,$conftp;
  if ($canti > $confmaxoferta)
    $canti = $confmaxoferta;
  $retval = db_query("SELECT cantidad FROM {$conftp}ofertas WHERE nombreobj='{$obj}'");
  if (mysql_num_rows($retval) == 0)
    db_query("INSERT INTO {$conftp}ofertas (nombreobj,cantidad) VALUES ('{$obj}',{$canti})");
  else {
    $ret = mysql_fetch_row($retval);
    $cantidad = $ret[0]+$canti;
    if ($cantidad > $confmaxoferta)
      $cantidad = $confmaxoferta;
    db_query("UPDATE {$conftp}ofertas SET cantidad={$cantidad} WHERE nombreobj='{$obj}'");
  }
  mysql_free_result($retval);
}

function remoferta($obj,$canti=1) {
  global $conftp;
  $retval = db_query("SELECT cantidad FROM {$conftp}ofertas WHERE nombreobj='{$obj}'");
  if (mysql_num_rows($retval)) {
    $ret = mysql_fetch_row($retval);
    if ($ret[0]<=$canti) {
      db_query("DELETE FROM {$conftp}ofertas WHERE nombreobj='{$obj}'");
    }
    else {
      $cantidad = $ret[0]-$canti;
      db_query("UPDATE {$conftp}ofertas SET cantidad={$cantidad} WHERE nombreobj='{$obj}'");
    }
  }
  mysql_free_result($retval);
}


function dale_objeto($jug,$obj,$canti=1) {
  global $conftp;
  $retval = db_query("SELECT cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='{$obj}'");
  if (mysql_num_rows($retval) == 0)
    db_query("INSERT INTO {$conftp}tiene (nombrejug,nombreobj,cantidad) VALUES ('{$jug}','{$obj}',{$canti})");
  else {
    $ret = mysql_fetch_row($retval);
    $cantidad = $ret[0]+$canti;
    db_query("UPDATE {$conftp}tiene SET cantidad={$cantidad} WHERE nombrejug='{$jug}' AND nombreobj='{$obj}'");
  }
  mysql_free_result($retval);
}

function quita_objeto($jug,$obj,$idob,$canti=1) {
  global $conftp;
  $retval = db_query("SELECT cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='{$obj}'");
  if (mysql_num_rows($retval)) {
    $ret = mysql_fetch_row($retval);
    if ($ret[0]<=$canti) {
      db_query("UPDATE {$conftp}jugadores SET insignia='none' WHERE nombrejug='{$jug}' AND insignia='{$idob}'");
      db_query("DELETE FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='{$obj}'");
    }
    else {
      $cantidad = $ret[0]-$canti;
      db_query("UPDATE {$conftp}tiene SET cantidad={$cantidad} WHERE nombrejug='{$jug}' AND nombreobj='{$obj}'");
    }
  }
  mysql_free_result($retval);
}

function sumapuntos() {
  global $confobj,$confnivel,$conftp,$time,$imgroot;
  $cambiopuntos = 1;
  db_lock("{$conftp}jugadores WRITE,{$conftp}mensajes WRITE,{$conftp}tiene WRITE,{$conftp}objetos READ,{$conftp}aprendido READ");
  while ($cambiopuntos) {
    $cambiopuntos = 0;

    $retval = db_query("SELECT nombrejug,puntos,nivel,puntosnivel,puntosobjeto,puntossuma FROM {$conftp}jugadores WHERE puntossuma<>0 ORDER BY login DESC LIMIT 0,20");
    $numrows = mysql_num_rows($retval);
    for ($i = 0;$i < $numrows;$i++) {
      $mens = '';
      $ret = mysql_fetch_row($retval);
      $njug = $ret[0];
      $puntos = $ret[1]+$ret[5];
      $puntossuma = 0;
      $nivel = $ret[2];
      $ultimossubio = $ret[3];
      $ultimosobjeto = $ret[4];

      $nganaobjetos = floor(($puntos-$ultimosobjeto)/$confobj);
      $ultimosobjeto = $ultimosobjeto+$nganaobjetos*$confobj;

      $retvab = db_query("SELECT resultado FROM {$conftp}aprendido WHERE nombrejug='{$njug}'");
      $nrb = mysql_num_rows($retvab);
      $sabe = array();
      for ($nni = 0;$nni < $nrb;$nni++) {
        $xen = mysql_fetch_row($retvab);
        $sabe[$nni] = $xen[0];
      }

      mysql_free_result($retvab);

      if ($nganaobjetos<0)
        $nganaobjetos = 0;
      if ($nganaobjetos>0) {
        $mens .= "Has encontrado:<br/><br/>";
        $retval2 = db_query("SELECT nombreobj,posibilidad,img,tipo,ataq,prot,puntosencontrar,niveluso,usos FROM {$conftp}objetos WHERE nivelencontrar<={$nivel} AND nivelencontrar>0 ORDER BY posibilidad DESC");
        $maxpos = 0;
        $numrows2 = mysql_num_rows($retval2);
        $reta = array();
        for ($y = 0;$y < $numrows2;$y++) {
          $reta[$y] = mysql_fetch_row($retval2);
          $maxpos += $reta[$y][1];
        } 
        if ($maxpos == 0) 
          $nganaobjetos = 0;
        mysql_free_result($retval2);
      }
      for ($x = 0;$x < $nganaobjetos;$x++) {
        $azar = rand(1,$maxpos)-$reta[0][1];
        $nuevo = 0;
        while ($azar > 0) {
          $nuevo++;
          $azar = $azar-$reta[$nuevo][1];
        }
        dale_objeto($njug,$reta[$nuevo][0]);
        if ($reta[$nuevo][8] <= 1) {
          if ($reta[$nuevo][8])
            $usos = ' / UN SOLO USO';
          else
            $usos = '';
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (Ataque: {$reta[$nuevo][4]} / Protección: {$reta[$nuevo][5]} / Nivel: {$reta[$nuevo][7]}{$usos}) (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 2) {
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (Energía: +{$reta[$nuevo][5]} / UN SOLO USO) (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 3) {
          $aprendido = '';
          $esaprendido = 0;
          for ($ba = 0;$ba < $nrb;$ba++) {
            if ($sabe[$ba] == $reta[$nuevo][0]) {
              $aprendido = ' / APRENDIDO';
              $esaprendido = 1;
            }
          }

          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (Energía: -{$reta[$nuevo][4]} / UN SOLO USO{$aprendido}) (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 4) {
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 5) {
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (consumir para obtener 1 semana premium) (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 6) {
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (MASCOTA) (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 7) {
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (Energía: -{$reta[$nuevo][4]} / MAPA) (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }
        else if ($reta[$nuevo][8] == 8) {
          $mens .= "<div class=\"item\"><img src=\"{$imgroot}img/{$reta[$nuevo][2]}.gif\" alt=\"{$reta[$nuevo][3]}\"/> {$reta[$nuevo][0]} (experiencia +{$reta[$nuevo][6]}).</div><br/>";
        }

        $puntossuma = $puntossuma+$reta[$nuevo][6];
        $ultimosobjeto = $ultimosobjeto+$reta[$nuevo][6];
        if ($reta[$nuevo][6] != 0)
          $cambiopuntos = 1;
      }
      if ($mens == "Has encontrado:<br/><br/>")
        $mens = '';
      while ($puntos >= expsignivel($nivel,$ultimossubio)) {
        $ultimossubio = $ultimossubio+$nivel*$confnivel;
        $nivel++;
        $mens .= "Enhorabuena, has subido hasta el nivel {$nivel}!<br/><br/>";
      }
  
      if (($mens != "Has encontrado:<br/><br/>") && ($mens != '')) {
        $retvalgg = db_query("SELECT idmensaje,mensaje FROM {$conftp}mensajes WHERE nombrejug='{$njug}' AND remitente='@' AND visto=0 ORDER by idmensaje DESC LIMIT 0,1");
        if (mysql_num_rows($retvalgg)) {
          $ret = mysql_fetch_row($retvalgg);
          db_query("UPDATE {$conftp}mensajes SET mensaje='{$ret[1]}<br/><br/>{$mens}' WHERE idmensaje={$ret[0]}");
        }
        else
          db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$njug}','@',{$time},'{$mens}')");
        mysql_free_result($retvalgg);
      }
  
      db_query("UPDATE {$conftp}jugadores SET puntos={$puntos},nivel={$nivel},puntosnivel={$ultimossubio},puntosobjeto={$ultimosobjeto},puntossuma={$puntossuma} WHERE nombrejug='{$njug}'");
    }
    mysql_free_result($retval);
  }
  db_unlock();
}
?>
