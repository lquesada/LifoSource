<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tag,$confvalorventa,$_REQUEST,$confmercencuentra,$confmercvecesmax,$time,$conftp,$us,$imgroot,$confmuestrafabricar;

  $cat = '';
  if (isset($_REQUEST['cat']))
    $cat = check_itemname($_REQUEST['cat']);

  techo('Aquí puedes crear los objetos y hechizos que has aprendido a fabricar. Para aprender a fabricar objetos o hechizos debes leer cualesquiera de los distintos libros disponibles. Cada libro te enseña a fabricar unos objetos o hechizos determinados.');

  echo "Tienes <b>{$us['energia']}</b> puntos de energía.<br/><br/>";

  if ($us['tiempopremium'] > 86400*7) {
    echo "<form action=\"fabricar.php\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo "<input type=\"submit\" onclick=\"return c()\" name=\"joyapremiumf\" value=\"Convertir una semana premium en una joya premium\"/>";
    echo "</form><br/><br/>";
  }

  if ($cat) {
    if (isset($_REQUEST['p']))
      $p = $_REQUEST['p'];
    else 
      $p = 1;
    if (!is_numeric($p))
      $p = 1;
    if ($p < 1)
      $p = 1;
    $ini = ($p-1)*$confmuestrafabricar;
    db_lock("{$conftp}recetas READ,{$conftp}conoce READ,{$conftp}objetos READ");
    $retval3 = db_query("SELECT {$conftp}recetas.resultado,cantidadresultado,{$conftp}objetos.tipo,img,ataq,prot,niveluso,usos,energia,dificultadhacer,perderingredientes,cantidad FROM {$conftp}recetas,{$conftp}conoce,{$conftp}objetos WHERE {$conftp}recetas.tipo='{$cat}' AND {$conftp}conoce.resultado={$conftp}recetas.resultado AND {$conftp}conoce.nombrejug='{$jug}' AND {$conftp}conoce.resultado={$conftp}objetos.nombreobj ORDER BY dificultadhacer DESC,{$conftp}recetas.resultado DESC LIMIT {$ini},{$confmuestrafabricar}");
    $retvalt = db_query("SELECT COUNT(*) FROM {$conftp}recetas,{$conftp}conoce WHERE tipo='{$cat}' AND {$conftp}conoce.resultado={$conftp}recetas.resultado AND {$conftp}conoce.nombrejug='{$jug}'");
    db_unlock();

    $ret2 = mysql_fetch_row($retvalt);
    mysql_free_result($retvalt);

    $ulresul = '';
    $numrows = mysql_num_rows($retval3);

    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval3);
      if ($ret[7] <= 1) {
        echo "<div class=\"item\">";
        if ($ret[7])
          $usos = ' / UN SOLO USO';
        else
          $usos = '';
        echo "<b>Fabricar:</b> <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Ataque: {$ret[4]} / Protección: {$ret[5]} / Nivel: {$ret[6]}{$usos})<br/></div>";
      }
      else if ($ret[7] == 2) {
        echo "<div class=\"item\">";
        echo "<b>Fabricar:</b> <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: +{$ret[5]} / UN SOLO USO)<br/></div>";
      }
      else if ($ret[7] == 3) {
        $aprendido = '';
        $esaprendido = 0;
        for ($ba = 0;$ba < $nrb;$ba++) {
          if ($sabe[$ba] == $ret[0]) {
            $aprendido = ' / APRENDIDO';
            $esaprendido = 1;
          }
        }
        echo "<div class=\"item\">";
	echo "<b>Fabricar:</b> <img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / UN SOLO USO{$aprendido})<br/></div>";
      }
      else if ($ret[7] == 4) {
        echo "<b>Fabricar:</b> <div class=\"item\"><img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]}<br/></div>";
      }
      else if ($ret[7] == 5) {
        echo "<b>Fabricar:</b> <div class=\"item\"><img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (consumir para obtener 1 semana premium)<br/></div>";
      }
      else if ($ret[7] == 6) {
        echo "<b>Fabricar:</b> <div class=\"item\"><img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (MASCOTA)<br/></div>";
      }
      else if ($ret[7] == 7) {
        echo "<b>Fabricar:</b> <div class=\"item\"><img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]} (Energía: -{$ret[4]} / Nivel: {$ret[6]} / MAPA)<br/></div>";
      }
      else if ($ret[7] == 8) {
        echo "<b>Fabricar:</b> <div class=\"item\"><img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]}</b> {$ret[0]}<br/></div>";
      }
      $inccon = floor($ret[11]/100);
      if ($inccon > 50)
        $inccon = 50;
      $dif = $ret[9]-$inccon;
      if ($dif < 0)
        $dif = 0;

      if ($ret[11] < 10)
        echo "No tienes ninguna experiencia fabricando este objeto.";
      else if ($ret[11] < 100)
        echo "Tienes un poco de experiencia fabricando este objeto.";
      else if ($ret[11] < 300)
        echo "Tienes bastante experiencia fabricando este objeto.";
      else if ($ret[11] < 500)
        echo "Estás familiarizado con la fabricación de este objeto.";
      else if ($ret[11] < 1000)
        echo "Estás muy familiarizado con la fabricación de este objeto.";
      else if ($ret[11] < 3000)
        echo "Eres un experto fabricando este objeto.";
      else if ($ret[11] < 5000)
        echo "Eres un maestro fabricando este objeto.";
      else
        echo "Eres un gran maestro fabricando este objeto.";
      echo '<br/>'; 
      $exito = 100-$dif;
      echo "Posibilidad de éxito: <b>{$exito}</b>%<br/>";
      echo "Coste de energía: <b>{$ret[8]}</b><br/>";
      if ($ret[10])
        echo "¡Perderás los ingredientes si fallas!<br/>";
      echo "Ingredientes necesarios:<br/>";
      db_lock("{$conftp}ingrediente READ,{$conftp}objetos READ,{$conftp}tiene READ");
      $ketval = db_query("SELECT {$conftp}ingrediente.nombreobj,cantidad,tipo,img,ataq,prot,niveluso,usos FROM {$conftp}ingrediente,{$conftp}objetos WHERE {$conftp}ingrediente.nombreobj={$conftp}objetos.nombreobj AND {$conftp}ingrediente.resultado='{$ret[0]}'");
      $ketvali2 = db_query("SELECT nombreobj,cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}'");
      $ketval2 = db_query("SELECT nombreobj,cantidad FROM {$conftp}ingrediente WHERE resultado='{$ret[0]}'");
      db_unlock();
      $ni2 = mysql_num_rows($ketvali2);
      $n2 = mysql_num_rows($ketval2);
      $faltan = 0;
      for ($aa0 = 0;$aa0 < $n2 && !$faltan;$aa0++) {
        $retbb = mysql_fetch_row($ketval2);
        $faltaeste = 1;
        for ($aa1 = 0;$aa1 < $ni2 && $faltaeste;$aa1++) {
          $retba = mysql_fetch_row($ketvali2);
          if (($retba[0]==$retbb[0]) && ($retba[1]>=$retbb[1])) 
            $faltaeste = 0;
        }
        if ($ni2)
          mysql_data_seek($ketvali2,0);
        if ($faltaeste)
          $faltan = 1;
      }
      $kumrows = mysql_num_rows($ketval);
      for ($y = 0;$y < $kumrows;$y++) {
        $ket = mysql_fetch_row($ketval);
        if ($ket[7] <= 1) {
          echo "<div class=\"item\">";
          if ($ket[7])
            $usos = ' / UN SOLO USO';
          else
            $usos = '';
          echo "<img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]} (Ataque: {$ket[4]} / Protección: {$ket[5]} / Nivel: {$ket[6]}{$usos})<br/></div>";
        }
        else if ($ket[7] == 2) {
          echo "<div class=\"item\">";
          echo "<img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]} (Energía: +{$ket[5]} / UN SOLO USO)<br/></div>";
        }
        else if ($ket[7] == 3) {
          $aprendido = '';
          $esaprendido = 0;
          for ($ba = 0;$ba < $nrb;$ba++) {
            if ($sabe[$ba] == $ket[0]) {
              $aprendido = ' / APRENDIDO';
              $esaprendido = 1;
            }
          }
          echo "<div class=\"item\">";
          echo "<img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]} (Energía: -{$ket[4]} / UN SOLO USO{$aprendido})<br/></div>";
        }
        else if ($ket[7] == 4) {
          echo "<div class=\"item\"><img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]}<br/></div>";
        }
        else if ($ket[7] == 5) {
          echo "<div class=\"item\"><img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]} (consumir para obtener 1 semana premium)<br/></div>";
        }
        else if ($ket[7] == 6) {
          echo "<div class=\"item\"><img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]} (MASCOTA)<br/></div>";
        }
        else if ($ket[7] == 7) {
          echo "<div class=\"item\"><img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]} (Energía: -{$ket[4]} / Nivel: {$ket[6]} / MAPA)<br/></div>";
        }
        else if ($ket[7] == 8) {
          echo "<div class=\"item\"><img src=\"{$imgroot}img/{$ket[3]}.gif\" alt=\"{$ket[2]}\"/><b>x{$ket[1]}</b> {$ket[0]}<br/></div>";
        }
      }
      if ($faltan) {
        echo '[te faltan ingredientes]<br/>';
      }
      else {
        if ($ret[8] > $us['energia'])
          echo '[te falta energía]<br/>';
        else {
          echo "<form action=\"fabricar.php\" method=\"post\"><div>";
          echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
          echo "<input type=\"hidden\" name=\"cat\" value=\"{$cat}\"/>";
          echo "<input type=\"hidden\" name=\"p\" value=\"{$p}\"/>";
          echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
          echo "<input type=\"submit\" name=\"fabricar\" onclick=\"return c()\" value=\"Fabricar\"/>x<input type=\"text\" name=\"cantidad\" value=\"1\" size=\"2\" maxlength=\"3\"/>";
          echo "</div></form>";
        }
      }
      echo '<br/><br/>';
    }
    mysql_free_result($retval3);

    $npags = $ret2[0]/$confmuestrafabricar;
    $npag = floor($npags);
    if ($npag != $npags)
      $npag++;
    if ($npag > 1) {
      echo "Página: ";
      for ($i = 1;$i <= $npag;$i++) {
        if ($i != $p)
          echo "<a href=\"fabricar.php?cat={$cat}&amp;p={$i}\">{$i}</a> ";
        else
          echo "<b>{$i}</b> ";
      }
    }
    echo "<br/><br/><a href=\"fabricar.php\">Volver</a><br/><br/>";
  }
  else {
    db_lock("{$conftp}aprendido READ,{$conftp}objetos READ");
    $retvab = db_query("SELECT resultado,0,img FROM {$conftp}aprendido,{$conftp}objetos WHERE nombreobj=resultado AND nombrejug='{$jug}'");
    db_unlock();
    $nrb = mysql_num_rows($retvab);
    for ($i = 0;$i < $nrb;$i++) {
      $xen = mysql_fetch_row($retvab);
      $sabe[$i] = $xen[0];
      $sabei[$i] = $xen[2];
    }
    mysql_free_result($retvab);

    db_lock("{$conftp}recetas READ,{$conftp}conoce READ");
    $retvaln = db_query("SELECT DISTINCT tipo,COUNT(*) FROM {$conftp}recetas,{$conftp}conoce WHERE {$conftp}conoce.nombrejug='{$jug}' AND {$conftp}conoce.resultado={$conftp}recetas.resultado GROUP BY tipo");
    db_unlock();
    $retn = mysql_num_rows($retvaln);
    if ($retn) {
      echo '<b>Ver objetos a fabricar por categoría:</b><br/><br/>';
      for ($i = 0;$i < $retn;$i++) {
        $ret = mysql_fetch_row($retvaln);
        echo "<a href=\"fabricar.php?cat={$ret[0]}\">Categoría: {$ret[0]} ($ret[1] objetos)</a><br/>";
      }
    }
    else 
      echo 'Aun no sabes fabricar ningún objeto. Intenta leer libros para aprender a fabricarlos.<br/>';
    mysql_free_result($retvaln);
    echo '<br/><br/>';
    if ($nrb) {
      echo 'Libros aprendidos completamente:<br/><br/>';
      for ($i = 0;$i < $nrb;$i++) {
        echo "<div class=\"item\"><img src=\"{$imgroot}img/{$sabei[$i]}.gif\" alt=\"Aprendido\"/> {$sabe[$i]}<br/></div>";
      }
      echo '<br/><br/>';
    }
  }
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$confvalorventa,$conftp,$time,$imgroot,$us;

  if ((isset($_REQUEST['joyapremiumi'])) || (isset($_REQUEST['joyapremiume'])) || (isset($_REQUEST['joyapremiumf']))) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        if ($us['tiempopremium'] > 86400*7) {
          db_lock("{$conftp}jugadores WRITE,{$conftp}tiene WRITE");
          $retbola = db_query("SELECT trabajopremium,fintrabajo FROM jugadores WHERE nombrejug='{$jug}'");
          $retbele = mysql_fetch_row($retbola);
          mysql_free_result($retbola);
          if (($time+$us['tiempopremium']-86400*7<$retbele[1]) && ($retbele[0]) && (!$us['moderador']) && (!$us['enchufado'])) {
            $_SESSION['error'] = 'Estás realizando un trabajo premium y no te queda suficiente tiempo premium, deja de trabajar antes de intentar fabricar una joya premium.';
          }
          else {
            db_query("UPDATE {$conftp}jugadores SET premiumhasta=premiumhasta-86400*7 WHERE nombrejug='{$jug}'");
            dale_objeto($jug,'Joya premium');
            $_SESSION['mensaje'] = 'Has convertido una semana premium en una joya premium.';
          }
          db_unlock();
        }
        else
          $_SESSION['error'] = 'No tienes suficiente tiempo premium.';
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    if (isset($_REQUEST['joyapremiumf']))
      header('Location: fabricar.php');
    if (isset($_REQUEST['joyapremiumi']))
      header('Location: inventario.php');
    if (isset($_REQUEST['joyapremiume']))
      header('Location: estado.php');
    return 1;
  }
  if (isset($_REQUEST['fabricar'])) {
    $salirnow = 0;
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob'])) && (isset($_REQUEST['cantidad']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      $cantidad = floor($_REQUEST['cantidad']);
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob)) && (is_numeric($cantidad)) && ($cantidad > 0)) {
        db_lock("{$conftp}jugadores WRITE,{$conftp}recetas READ,{$conftp}objetos READ,{$conftp}ingrediente READ,{$conftp}tiene WRITE,{$conftp}conoce WRITE,{$conftp}mensajes WRITE");
        $retvalo = db_query("SELECT energia,resultado,dificultadhacer,perderingredientes,cantidadresultado,img,{$conftp}recetas.tipo FROM {$conftp}recetas,{$conftp}objetos WHERE {$conftp}objetos.nombreobj={$conftp}recetas.resultado AND {$conftp}objetos.img='{$idob}'");
        if (mysql_num_rows($retvalo)) {
          $retval = db_query("SELECT energia FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
          $ret = mysql_fetch_row($retval);
          mysql_free_result($retval);
          $reto = mysql_fetch_row($retvalo);
          $consume = $reto[0]*$cantidad;
          if ($ret[0]>=$consume) {
            $ketvali2 = db_query("SELECT nombreobj,cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}'");
            $ketval2 = db_query("SELECT nombreobj,cantidad*{$cantidad} FROM {$conftp}ingrediente WHERE resultado='{$reto[1]}'");
            $ni2 = mysql_num_rows($ketvali2);
            $n2 = mysql_num_rows($ketval2);
            $faltan = 0;
            for ($aa0 = 0;$aa0 < $n2 && !$faltan;$aa0++) {
              $retbb = mysql_fetch_row($ketval2);
              $faltaeste = 1;
              for ($aa1 = 0;$aa1 < $ni2 && $faltaeste;$aa1++) {
                $retba = mysql_fetch_row($ketvali2);
                if (($retba[0]==$retbb[0]) && ($retba[1]>=$retbb[1]))
                  $faltaeste = 0;
              }
              if ($ni2)
                mysql_data_seek($ketvali2,0);
              if ($faltaeste)
                $faltan = 1;
            }
            if (!$faltan) {
              db_query("UPDATE {$conftp}jugadores SET energia=energia-{$consume} WHERE nombrejug='{$jug}'");
              $retvelo = db_query("SELECT cantidad FROM {$conftp}conoce WHERE nombrejug='{$jug}' AND resultado='{$reto[1]}'");
              $rete = mysql_fetch_row($retvelo);
              mysql_free_result($retvelo);
              $inccon = floor($rete[0]/100);
              if ($inccon > 50)
                $inccon = 50;
              $total = rand(0,100)+$inccon;
              $creados = $cantidad*$reto[4];
              if ($total >= $reto[2]) {
                $ketvoy = db_query("SELECT {$conftp}objetos.nombreobj,cantidad,img FROM {$conftp}objetos,{$conftp}ingrediente WHERE resultado='{$reto[1]}' AND {$conftp}objetos.nombreobj={$conftp}ingrediente.nombreobj");
                $nk = mysql_num_rows($ketvoy);
                for ($i = 0;$i < $nk;$i++) {
                  $kk = mysql_fetch_row($ketvoy);
                  quita_objeto($jug,$kk[0],$kk[2],$kk[1]*$cantidad);
                }
                mysql_free_result($ketvoy);
                dale_objeto($jug,$reto[1],$creados);
                db_query("UPDATE {$conftp}conoce SET cantidad=cantidad+{$cantidad} WHERE nombrejug='{$jug}' AND resultado='{$reto[1]}'");
                $_SESSION['mensaje'] = 'Objeto fabricado con éxito.';
                db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jug}','@',{$time},'Has creado con éxito <img src=\"{$imgroot}img/{$reto[5]}.gif\" alt=\"{$reto[6]}\"/><b>x{$creados} {$reto[1]}</b>.')");
              }
              else {
                $_SESSION['mensaje'] = 'Has fallado fabricando el objeto.';
                if ($reto[3]) {
                $ketvoy = db_query("SELECT {$conftp}objetos.nombreobj,cantidad,img FROM {$conftp}objetos,{$conftp}ingrediente WHERE resultado='{$reto[1]}' AND {$conftp}objetos.nombreobj={$conftp}ingrediente.nombreobj");
                $nk = mysql_num_rows($ketvoy);
                for ($i = 0;$i < $nk;$i++) {
                  $kk = mysql_fetch_row($ketvoy);
                  quita_objeto($jug,$kk[0],$kk[2],$kk[1]*$cantidad);
                }
                mysql_free_result($ketvoy);
                db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jug}','@',{$time},'No has conseguido crear <img src=\"{$imgroot}img/{$reto[5]}.gif\" alt=\"{$reto[6]}\"/><b>x{$creados} {$reto[1]}</b>, has perdido la energía y los ingredientes.')");
                }
                else {
                db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$jug}','@',{$time},'No has conseguido crear <img src=\"{$imgroot}img/{$reto[5]}.gif\" alt=\"{$reto[6]}\"/><b>x{$creados} {$reto[1]}</b>, has perdido la energía aunque no los ingredientes.')");
                }
              }
              $salirnow = 1;
            }
            else
              $_SESSION['error'] = 'Te faltan ingredientes para fabricar esos objetos.';
            mysql_free_result($ketval2);
          }
          else
            $_SESSION['error'] = 'No tienes energía suficiente para fabricar esos objetos.';
        }
        else
          $_SESSION['error'] = 'No puedes crear ese objeto.';
        mysql_free_result($retvalo);
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor! (es válida la cantidad?)';
    }
    if (isset($_REQUEST['cat']))
      $cat = check_itemname($_REQUEST['cat']);
    else
      $cat = '';
    if (isset($_REQUEST['p']))
      $p = $_REQUEST['p'];
    else
      $p = '';
    if ($salirnow)
      header('Location: mensajeria.php');
    else
      header("Location: fabricar.php?cat={$cat}&p={$p}");
    return 1;
  }
}

?>
