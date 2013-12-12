<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$_REQUEST,$time,$conftp,$us,$tag;
  if (($us['admin']) || ($us['moderador']))
    $esmodera = 1;
  else
    $esmodera = 0;
  if ($esmodera) {
    techo('Bienvenido a la página de los moderadores. Desde aquí puedes añadir y eliminar silenciamientos a los jugadores conflictivos y ver los mensajes reportados. La implementación no es muy limpia, lo he hecho todo con prisa, aquí tenéis un par de detalles que debéis conocer: Pueden existir varios silenciamientos para un jugador o ip, siempre que haya uno activo el usuario estará silenciado. Al eliminar un silenciamiento determinado se eliminan todos los correspondientes a ese jugador (incluso los de ip) pues se entiende que se quiere dar permiso a ese jugador para hablar de nuevo. <b>No abuseis de los silenciamientos de ip, sólo debeis usarlos cuando un jugador insiste en molestar repetidas veces (silenciamiento corto al usuario+ip), ya que podéis causar problemas a otros jugadores si están conectando desde un cibercafé u otro lugar público</b>. Por norma general os bastará con utilizar silenciamientos al usuario :)');
    echo '<b>Lista de silenciamientos activos:</b><br/><br/>';
    db_lock("{$conftp}silencio READ");
    $conan = db_query("SELECT string,isip,autor,horapuesto,horafin,stringip FROM {$conftp}silencio WHERE horafin>{$time} order by horafin");
    db_unlock();
    $n = mysql_num_rows($conan);
    for ($i = 0;$i < $n;$i++) {
      $r = mysql_fetch_row($conan);
      $dia_t = ahora_dia($r[4]);
      $hora_t = ahora_hora($r[4]);
      $e = $i+1;
      echo "<form action=\"mod.php\" method=\"post\"><input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/><input type=\"hidden\" name=\"str\" value=\"{$r[5]}\"/><input type=\"submit\" onclick=\"return c()\" value=\"Eliminar\" name=\"eliminar\"/> ";
      if ($r[1] == 1)
        echo "{$e}. Silenciamiento a la ip de {$r[5]} puesto por {$r[2]} hasta el {$dia_t} a las {$hora_t}";
      else
        echo "{$e}. Silenciamiento al usuario {$r[5]} puesto por {$r[2]} hasta el {$dia_t} a las {$hora_t}";
      echo "</br></form>";
    }
    mysql_free_result($conan);

  echo '<br/><br/>';
  echo '<b>Añadir silenciamiento:</b><br/><br/>';
  echo "<form action=\"mod.php\" method=\"post\"><input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>Nombre de cuenta del usuario a silenciar: <input type=\"text\" name=\"ussil\" value=\"\"/><br/>Acción a llevar a cabo sobre esta cuenta:<br/>";
  echo "<input type=\"submit\" value=\"Silenciar usuario 2 horas\" onclick=\"return c()\" name=\"silus2\"/> <input type=\"submit\" value=\"Silenciar ip 2 horas\" onclick=\"return c()\" name=\"silip2\"/> <input type=\"submit\" value=\"Silenciar usuario e ip 2 horas\" onclick=\"return c()\" name=\"silusip2\"/>";
  echo '<br/>';
  echo "<input type=\"submit\" value=\"Silenciar usuario 6 horas\" onclick=\"return c()\" name=\"silus6\"/> <input type=\"submit\" value=\"Silenciar ip 6 horas\" onclick=\"return c()\" name=\"silip6\"/> <input type=\"submit\" value=\"Silenciar usuario e ip 6 horas\" onclick=\"return c()\" name=\"silusip6\"/>";
  echo '<br/>';
  echo "<input type=\"submit\" value=\"Silenciar usuario 1 día\" onclick=\"return c()\" name=\"silus1\"/> <input type=\"submit\" value=\"Silenciar ip 1 día\" onclick=\"return c()\" name=\"silip1\"/> <input type=\"submit\" value=\"Silenciar usuario e ip 1 día\" onclick=\"return c()\" name=\"silusip1\"/>";
  echo '<br/>';
  echo "<input type=\"submit\" value=\"Silenciar usuario 3 días\" onclick=\"return c()\" name=\"silus3\"/> <input type=\"submit\" value=\"Silenciar ip 3 días\" onclick=\"return c()\" name=\"silip3\"/> <input type=\"submit\" value=\"Silenciar usuario e ip 3 días\" onclick=\"return c()\" name=\"silusip3\"/>";
  echo '<br/>';
  echo "<input type=\"submit\" value=\"Silenciar usuario 7 días\" onclick=\"return c()\" name=\"silus7\"/> <input type=\"submit\" value=\"Silenciar ip 7 días\" onclick=\"return c()\" name=\"silip7\"/> <input type=\"submit\" value=\"Silenciar usuario e ip 7 días\" onclick=\"return c()\" name=\"silusip7\"/>";

  echo "</form>";



  echo '<br/><br/><br/><b>Ver y eliminar mensajes reportados (si no sale nada aquí debajo es que no quedan mensajes por revisar).</b><br/>';
  db_lock("{$conftp}mensajes READ");
  $retval = db_query("SELECT idmensaje,nombrejug,remitente,hora,mensaje FROM {$conftp}mensajes WHERE reportado=1");
  db_unlock();
  $num = mysql_num_rows($retval);
  for ($i = 0;$i < $num;$i++) {
    $ret = mysql_fetch_row($retval);
    $dia = ahora_dia($ret[3]);
    $hora = ahora_hora($ret[3]);
    echo "<form action=\"mod.php\" method=\"post\"><input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/><input type=\"hidden\" name=\"idmens\" value=\"{$ret[0]}\"/><input type=\"submit\" name=\"borramens\" onclick=\"return c()\" value=\"Borrar!\"/><u>Mensaje de <b>{$ret[2]}</b> recibido Y REPORTADO el {$dia} a las {$hora} POR <b>{$ret[1]}</b>.</u><br/>{$ret[4]}<br/><br/></form>";
  }

  }
  else
    return;
}

function procesaform() {
  global $us,$_REQUEST,$tag,$jug,$time,$conftp;
  if (isset($_REQUEST['eliminar'])) {
    if (($us['admin']) || ($us['moderador']))
      $esmodera = 1;
    else
      $esmodera = 0;
    if ($esmodera) {
      $gtag = $_REQUEST['tag'];
      $str = $_REQUEST['str'];
      if ((ereg('^[a-zA-Z]+$',$str)) && (strlen($str) >= 3) && (strlen($str) <= 14))
        $str = strtoupper($str[0]).strtolower(substr($str,1));
      else {
        if (!(ereg('^[0-9\.]*$',$str))) { 
          $str = '';
        }
      }
    if ($str) {
      if ($tag == $gtag) {
        db_lock("{$conftp}silencio READ");
        $conana = db_query("SELECT * FROM {$conftp}silencio WHERE stringip='{$str}' AND horafin>{$time}");
        db_unlock();
        if (mysql_num_rows($conana)) {
          db_lock("{$conftp}silencio WRITE");
          $conan = db_query("UPDATE {$conftp}silencio SET horafin={$time},eliminado='{$jug}' WHERE stringip='{$str}' AND horafin>{$time}");
          db_unlock();
          $_SESSION['mensaje'] = 'Silenciamiento eliminado.';
        }
        else
          $_SESSION['error'] = 'Ese silenciamiento no existe ya o ya ha caducado.';
        mysql_free_result($conana);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    else
      $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    else
      $_SESSION['error'] = 'No eres moderador.';
    header("Location: mod.php");
    return 1;
  }


  if (isset($_REQUEST['borramens'])) {
    if (($us['admin']) || ($us['moderador']))
      $esmodera = 1;
    else
      $esmodera = 0;
    if ($esmodera) {
      $gtag = $_REQUEST['tag'];
      $idmens = $_REQUEST['idmens'];
      if ((is_numeric($idmens)) && ($tag == $gtag)) {
        db_lock("{$conftp}mensajes WRITE");
        $retval = db_query("DELETE FROM {$conftp}mensajes WHERE idmensaje='{$idmens}'");
        db_unlock();
        $_SESSION['mensaje'] = "Borrado mensaje reportado. Si consideras motivo de silenciamiento el contenido del mensaje, adelante.";
      }
      else
        $_SESSION['error'] = "Intenta de nuevo!";
    }
    else
      $_SESSION['error'] = 'No eres moderador.';
    header('Location: mod.php');
    return 1;
  }

  if (
    (isset($_REQUEST['silus2'])) ||
    (isset($_REQUEST['silip2'])) ||
    (isset($_REQUEST['silusip2'])) ||
    (isset($_REQUEST['silus6'])) ||
    (isset($_REQUEST['silip6'])) ||
    (isset($_REQUEST['silusip6'])) ||
    (isset($_REQUEST['silus1'])) ||
    (isset($_REQUEST['silip1'])) ||
    (isset($_REQUEST['silusip1'])) ||
    (isset($_REQUEST['silus3'])) ||
    (isset($_REQUEST['silip3'])) ||
    (isset($_REQUEST['silusip3'])) ||
    (isset($_REQUEST['silus7'])) ||
    (isset($_REQUEST['silip7'])) ||
    (isset($_REQUEST['silusip7']))
  ) {
    if (($us['admin']) || ($us['moderador']))
      $esmodera = 1;
    else
      $esmodera = 0;
    if ($esmodera) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        $tiemposil = 0;
        $modosil = 0;
        if  ((isset($_REQUEST['silus2'])) ||
            (isset($_REQUEST['silip2'])) ||
            (isset($_REQUEST['silusip2'])))
         $tiemposil = 2;
        if  ((isset($_REQUEST['silus6'])) ||
            (isset($_REQUEST['silip6'])) ||
            (isset($_REQUEST['silusip6'])))
         $tiemposil = 6;
        if  ((isset($_REQUEST['silus1'])) ||
            (isset($_REQUEST['silip1'])) ||
            (isset($_REQUEST['silusip1'])))
         $tiemposil = 1;
        if  ((isset($_REQUEST['silus3'])) ||
            (isset($_REQUEST['silip3'])) ||
            (isset($_REQUEST['silusip3'])))
         $tiemposil = 3;
        if  ((isset($_REQUEST['silus7'])) ||
            (isset($_REQUEST['silip7'])) ||
            (isset($_REQUEST['silusip7'])))
         $tiemposil = 7;
        if  ((isset($_REQUEST['silus2'])) || (isset($_REQUEST['silus6'])) ||(isset($_REQUEST['silus1'])) ||(isset($_REQUEST['silus3'])) ||(isset($_REQUEST['silus7'])))
         $modosil = 1;
        if (    (isset($_REQUEST['silip2'])) ||    (isset($_REQUEST['silip6'])) ||    (isset($_REQUEST['silip1'])) ||    (isset($_REQUEST['silip3'])) ||    (isset($_REQUEST['silip7'])))
         $modosil = 2;
        if (    (isset($_REQUEST['silusip2'])) ||    (isset($_REQUEST['silusip6'])) ||    (isset($_REQUEST['silusip1'])) ||    (isset($_REQUEST['silusip3'])) ||    (isset($_REQUEST['silusip7'])))
         $modosil = 3;
         if (($modosil != 0) && ($tiemposil != 0)) {
           $username = check_username($_REQUEST['ussil']);
           if ($username) {
             db_lock("{$conftp}jugadores READ");
             $con = db_query("SELECT iplogin FROM jugadores WHERE nombrejug='{$username}'");
             db_unlock();
             if (mysql_num_rows($con)) {
               $r = mysql_fetch_row($con);
               if ($tiemposil == 1) $timmm = 86400;
               if ($tiemposil == 2) $timmm = 7200;
               if ($tiemposil == 3) $timmm = 259200;
               if ($tiemposil == 6) $timmm = 21600;
               if ($tiemposil == 7) $timmm = 604800;

               $horafin = $time+$timmm;
               db_lock("{$conftp}silencio WRITE");
               if (($modosil == 1) || ($modosil == 3))
                 db_query("INSERT INTO {$conftp}silencio (string,isip,autor,eliminado,horapuesto,stringip,horafin) VALUES ('{$username}',0,'{$jug}','',$time,'{$username}',$horafin)");
               if (($modosil == 2) || ($modosil == 3))
                 db_query("INSERT INTO {$conftp}silencio (string,isip,autor,eliminado,horapuesto,stringip,horafin) VALUES ('{$r[0]}',1,'{$jug}','',$time,'{$username}',$horafin)");
               db_unlock();


               $_SESSION['mensaje'] = "Silenciamiento definido. Gracias!";
             }
             else
               $_SESSION['error'] = "Esa cuenta no existe.";
           }
           else
             $_SESSION['error'] = "Nombre de cuenta no válido.";
         }
         else
           $_SESSION['error'] = "Datos erroneos";
      }
      else
        $_SESSION['error'] = "Intenta de nuevo!";
    }
    else
      $_SESSION['error'] = 'No eres moderador.';
    header('Location: mod.php');
    return 1;
  }

}

?>
