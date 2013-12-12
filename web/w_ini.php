<?php

if (isset($_REQUEST['login'])) {
  if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
    $username = check_username($_REQUEST['username']);
    $password = pwdHash($username,$_REQUEST['password']);
    if (antifloodcheck('loginfalla',"{$username}.{$ip}",3,600)) {
      if ($username) {
        db_lock("{$conftp}jugadores READ");
        $retval = db_query("SELECT password,zonahoraria,baneadohasta FROM {$conftp}jugadores WHERE nombrejug='{$username}'");
        db_unlock();
        if (mysql_num_rows($retval)) {
          $ret = mysql_fetch_row($retval);
	  if ($ret[0] == $password) {
            if ($ret[2] < $time) {
              $_SESSION['identificado'] = 1;
              $_SESSION['jugador'] = $username;
              $_SESSION['tag'] = pwdgen();
              $_SESSION['tags'] = pwdgen();
              $_SESSION['tagc'] = pwdgen();
              $_SESSION['pwd'] = $password;
              $_SESSION['zonahoraria'] = $ret[1];
              $_SESSION['tp'] = $confpwdsalt.$conftp;
              $_SESSION['ultimaact'] = $time;
              $_SESSION['vmen'] = 0;
              $_SESSION['vfor'] = 0;
              antifloodc('loginfalla',"{$username}.{$ip}");
              session_regenerate_id(TRUE);
              db_lock("{$conftp}jugadores WRITE");
              db_query("UPDATE {$conftp}jugadores SET ultimaact={$time},iplogin='{$ip}',login={$time} WHERE nombrejug='{$username}'");
              db_unlock();
              db_lock("{$conftp}loginlog WRITE");
              db_query("INSERT INTO {$conftp}loginlog VALUES ('{$username}',{$time},'{$ip}')");
              db_unlock();
            }
            else {
              $dia = ahora_dia($ret[2]);
              $hora = ahora_hora($ret[2]);
              $_SESSION['error'] = "Cuenta baneada hasta el {$dia} a las {$hora}.";
            }
            mysql_free_result($retval);
	  }
	  else {
            $_SESSION['error'] = 'Usuario o contraseña no válidos.';
            antiflood('loginfalla',"{$username}.{$ip}",3,600);
	  }
        }
        else 
          $_SESSION['error'] = 'Usuario o contraseña no válidos.';
      }
    }
    else
      $_SESSION['error'] = 'Demasiados intentos fallidos, intente de nuevo en diez minutos.';
  }
  header("Location: {$path}");
  db_close();
  exit();
}

if (isset($_SESSION['identificado'])) {
  if ((isset($_SESSION['jugador'])) && (isset($_SESSION['pwd']))) {
    if (isset($_SESSION['tp'])) {
      if ($confpwdsalt.$conftp != $_SESSION['tp']) {
        $_SESSION['pwd'] = 'LOGOUT';       
      }
    }
    db_lock("{$conftp}jugadores READ");
    $retval = db_query("SELECT baneadohasta,admin,premiumhasta,moderador,ultimoforo,nivel,puntos,trabajado,clan,email,trabajando,fintrabajo,energia,oro,protegidohasta,noatacarhasta,creado,puntosnivel,hijos,nietos,padre,combates,vencedor,vencido,insignia,visitashijos,visitasnietos,nocomerhasta,noexplorarhasta,enchufado FROM {$conftp}jugadores WHERE nombrejug='{$_SESSION['jugador']}' AND password='{$_SESSION['pwd']}'");
    db_unlock();
    if (mysql_num_rows($retval)) {
      $dentro = 1;
      $ret = mysql_fetch_row($retval);
      $us['baneadohasta'] = $ret[0];
      $us['admin'] = $ret[1];
      $us['premiumhasta'] = $ret[2];
      $us['moderador'] = $ret[3];
      $us['enchufado'] = $ret[29];
      $us['ultimoforo'] = $ret[4];
      $us['nivel'] = $ret[5];
      $us['puntos'] = $ret[6];
      $us['trabajado'] = $ret[7];
      $us['clan'] = $ret[8];
      $us['email'] = $ret[9];
      $us['trabajando'] = $ret[10];
      $us['fintrabajo'] = $ret[11];
      $us['energia'] = $ret[12];
      $us['oro'] = $ret[13];
      $us['protegidohasta'] = $ret[14];
      $us['noatacarhasta'] = $ret[15];
      $us['creado'] = $ret[16];
      $us['puntosnivel'] = $ret[17];
      $us['hijos'] = $ret[18];
      $us['nietos'] = $ret[19];
      $us['padre'] = $ret[20];
      $us['combates'] = $ret[21];
      $us['vencedor'] = $ret[22];
      $us['vencido'] = $ret[23];
      $us['insignia'] = $ret[24];
      $us['visitashijos'] = $ret[25];
      $us['visitasnietos'] = $ret[26];
      $us['nocomerhasta'] = $ret[27];
      $us['noexplorarhasta'] = $ret[28];

      $us['espremium'] = 0;
      $us['tiempopremium'] = 0;
      $us['tiempopremiumefectivo'] = 0;

      if ($us['premiumhasta'] >= $time) {
        $us['espremium'] = 1;
        $us['tiempopremium'] = $us['premiumhasta']-$time;
        $us['tiempopremiumefectivo'] = $us['premiumhasta']-$time;
      }

      if (($us['enchufado']) || ($us['moderador'])) {
        $us['espremium'] = 1;
        $us['tiempopremiumefectivo'] = 86400*30;
      }
    }
    mysql_free_result($retval);
    if (!$dentro) {
      db_lock("{$conftp}jugadores WRITE");
      db_query("UPDATE {$conftp}jugadores SET ultimaact={$time}-1801 WHERE nombrejug='{$_SESSION['jugador']}'");
      db_unlock();
      if (isset($_SESSION['error']))
        $er = $_SESSION['error'];
      else
        $er = '';
      unset($_SESSION['identificado']);
      unset($_SESSION['mensaje']);
      unset($_SESSION['jugador']);
      unset($_SESSION['tag']);
      unset($_SESSION['tags']);
      unset($_SESSION['tagc']);
      unset($_SESSION['pwd']);
      unset($_SESSION['zonahoraria']);
      unset($_SESSION['tp']);
      unset($_SESSION['ultimaact']);
      unset($_SESSION['vmen']);
      unset($_SESSION['vfor']);

      session_unset();
      session_destroy();
      session_start();
      if ($er)
        $_SESSION['error'] = $er;
      if (!isset($_SESSION['error']))
        $_SESSION['error'] = 'Tu contraseña ha sido cambiada, vuelve a identificarte. Si ocurre a menudo, cambia tu dirección de correo electrónico desde "Mi cuenta"';
    }
    else {
      if ($us['baneadohasta'] >= $time) {
        db_lock("{$conftp}jugadores WRITE");
        db_query("UPDATE {$conftp}jugadores SET ultimaact={$time}-1801 WHERE nombrejug='{$_SESSION['jugador']}'");
        db_unlock();
        unset($_SESSION['identificado']);
        unset($_SESSION['mensaje']);
        unset($_SESSION['jugador']);
        unset($_SESSION['tag']);
        unset($_SESSION['tags']);
        unset($_SESSION['tagc']);
        unset($_SESSION['pwd']);
        unset($_SESSION['zonahoraria']);
        unset($_SESSION['tp']);
        unset($_SESSION['ultimaact']);
        unset($_SESSION['vmen']);
        unset($_SESSION['vfor']);
        session_unset();
        session_destroy();
        session_start();
        $dia = ahora_dia($ret[0]);
        $hora = ahora_hora($ret[0]);
        $_SESSION['error'] = "Cuenta baneada hasta el {$dia} a las {$hora}.";
      }
    }
  }
}

if (isset($_SESSION['identificado'])) {
  $ident = 1;
  if (isset($_SESSION['jugador']))
    $jug = $_SESSION['jugador'];
  else
    $jug = '';
  if (isset($_SESSION['tag']))
    $tag = $_SESSION['tag'];
  else
    $tag = '';
  if (isset($_SESSION['tagc']))
    $tagc = $_SESSION['tagc'];
  else
    $tagc = '';
  if (isset($_SESSION['tags'])) {
    $tags = $_SESSION['tags'];

    if (isset($_REQUEST['salir'])) {
      if ($_REQUEST['salir'] == $tags) {
        db_lock("{$conftp}jugadores WRITE");
        db_query("UPDATE {$conftp}jugadores SET ultimaact={$time}-1801 WHERE nombrejug='{$_SESSION['jugador']}'");
        db_unlock();
        unset($_SESSION['identificado']);
        unset($_SESSION['jugador']);
        unset($_SESSION['tag']);
        unset($_SESSION['tags']);
        unset($_SESSION['tagc']);
        unset($_SESSION['pwd']);
        unset($_SESSION['zonahoraria']);
        unset($_SESSION['tp']);
        unset($_SESSION['ultimaact']);
        unset($_SESSION['vmen']);
        unset($_SESSION['vfor']);
        unset($_SESSION['admin']);
        unset($_SESSION['moderador']);
        unset($us);
        $ident = 0;
        $jug = '';
        $tag = '';
        $tags = '';
        $tagc = '';
        session_unset();
        session_destroy();
        session_start();
      }
    }
  }
  else
    $tags = '';
}
else {
  $ident = 0;
  $jug = '';
  $tag = '';
  $tags = '';
  $tagc = '';
}
if (isset($_SESSION['vfor']))
  $vfor = $_SESSION['vfor'];
else
  $vfor = '0';
if (isset($_SESSION['vmen']))
  $vmen = $_SESSION['vmen'];
else
  $vmen = '0';
if (isset($_SESSION['ultimaact']))
  $ultimaact = $_SESSION['ultimaact'];
else
  $ultimaact = '0';
if (isset($_SESSION['zonahoraria']))
  $zonahoraria = $_SESSION['zonahoraria'];
else
  $zonahoraria = '-15';

l_setdate($zonahoraria);
?>
