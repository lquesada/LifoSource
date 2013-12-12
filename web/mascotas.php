<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tag,$imgroot,$conftp;

  techo('Aquí puedes atender a tus mascotas. Puedes elegir qué mascota te acompañará en tus combates y puedes alimentarlas cuando tengan toda la experiencia necesaria para que suban de nivel.');

  db_lock("{$conftp}tienemascotas READ,{$conftp}mascotas READ,{$conftp}objetos READ,{$conftp}tiene READ");
  $rg1 = db_query("SELECT {$conftp}tienemascotas.nombremascota,img,nivel,experiencia,alimento,ataquebase,defensabase,ataquenivel,defensanivel,expbase,expmult,expgana,maxnivel,usado FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}tienemascotas.nombremascota={$conftp}mascotas.nombremascota AND nombrejug='{$jug}' ORDER BY usado DESC,nombremascota ASC");
  $rows = mysql_num_rows($rg1);
  if ($rows == 0) {
    echo 'No tienes ninguna mascota ahora mismo.';
  }
  else 
    echo '<b>Listado de mascotas:</b><br/><br/>';

  for ($ii = 0;$ii < $rows;$ii++) {
    $rrr = mysql_fetch_row($rg1);
    $rg2 = db_query("SELECT img FROM objetos WHERE nombreobj='{$rrr[4]}'");
    $rg3 = db_query("SELECT cantidad FROM tiene WHERE nombreobj='{$rrr[4]}' AND nombrejug='{$jug}'");
    $rrs = mysql_fetch_row($rg2);
    if (mysql_num_rows($rg3)) {
      $rrt = mysql_fetch_row($rg3);
    }
    else
      $rrt[0] = 0;
    mysql_free_result($rg2);
    mysql_free_result($rg3);

    $ataq = $rrr[5]+($rrr[7]*($rrr[2]-1));
    $prot = $rrr[6]+($rrr[8]*($rrr[2]-1));
    $expmax = $rrr[9]+($rrr[10]*($rrr[2]-1));
    if ($rrr[2] >= $rrr[12])
      $expmax = 0;
    if (($expmax > 0) && ($rrr[3] >= $expmax)) {
      $a1 = '<b>';
      $a2 = '</b>';
    }
    else {
      $a1 = '';
      $a2 = '';
    }
    if ($rrr[13]) {
      echo "<b>Ahora mismo te acompaña:</b><br/><br/>";
      echo "<form action=\"mascotas.php\" method=\"post\">";
      echo "<div class=\"item\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"submit\" onclick=\"return c()\" name=\"descansa\" value=\"Hacer que no te acompañe\"/>";
      echo "</form>";
    }
    else {
      echo "<form action=\"mascotas.php\" method=\"post\">";
      echo "<div class=\"item\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"idob\" value=\"{$rrr[1]}\"/>";
      echo "<input type=\"submit\" name=\"sigueme\" onclick=\"return c()\" value=\"Hacer que te acompañe\"/>";
      echo "</form>";
    }
    echo "<img src=\"{$imgroot}img/{$rrr[1]}.gif\" alt=\"{$rrr[0]}\"/> {$rrr[0]} ";
    echo "(Nivel: {$rrr[2]}/{$rrr[12]} / Experiencia: {$a1}{$rrr[3]}/{$expmax}{$a2} / Ataque: {$ataq} / Protección: {$prot})<br/>";
    if (($expmax > 0) && ($rrr[3] >= $expmax)) {
      if ($rrt[0]) {
        echo "<form action=\"mascotas.php\" method=\"post\">";
        echo "<div class=\"item\">";
        echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
        echo "<input type=\"hidden\" name=\"idob\" value=\"{$rrr[1]}\"/>";
        echo "<input type=\"submit\" name=\"come\" onclick=\"return c()\" value=\"Alimentar\"/>";
        echo "</form>";
      }
      else
        echo '[te falta este alimento]';
    }
    else
      echo '[le falta experiencia para comer]';
    echo " Su alimento preferido es: <img src=\"{$imgroot}img/{$rrs[0]}.gif\" alt=\"Alimento preferido\"/> {$rrr[4]}";
    echo "<br/><br/>";
    if ($rrr[13]) {
      echo "<br/><br/><b>Resto de mascotas:</b><br/><br/>";
    }
  }
  echo '<br/><br/>';
  db_unlock();
  mysql_free_result($rg1);
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$conftp;

  if (isset($_REQUEST['descansa'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        db_lock("{$conftp}tienemascotas WRITE");
        $retvalx1 = db_query("UPDATE {$conftp}tienemascotas set usado=0 WHERE nombrejug='{$jug}'");
        db_unlock();
        $_SESSION['mensaje'] = "Tu mascota ya no te acompaña.";
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: mascotas.php');
    return 1;
  }

  if (isset($_REQUEST['sigueme'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tienemascotas WRITE,{$conftp}mascotas READ");
        $rrt = db_query("SELECT {$conftp}tienemascotas.nombremascota FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}mascotas.nombremascota={$conftp}tienemascotas.nombremascota AND nombrejug='{$jug}' AND img='{$idob}'");
        if (mysql_num_rows($rrt)) {
          $retvalx1 = db_query("UPDATE {$conftp}tienemascotas set usado=0 WHERE nombrejug='{$jug}'");
          $masc = mysql_fetch_row($rrt);
          $retvalx1 = db_query("UPDATE {$conftp}tienemascotas set usado=1 WHERE nombrejug='{$jug}' AND nombremascota='{$masc[0]}'");
          $_SESSION['mensaje'] = "Tu mascota ya te está acompañando.";
        }
        else
          $_SESSION['error'] = 'No tienes esa mascota';
        mysql_free_result($rrt);
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: mascotas.php');
    return 1;
  }

  if (isset($_REQUEST['come'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}tienemascotas WRITE,{$conftp}mascotas READ,{$conftp}tiene WRITE,{$conftp}objetos READ,{$conftp}jugadores WRITE");
        $rrt = db_query("SELECT {$conftp}tienemascotas.nombremascota,img,nivel,experiencia,alimento,ataquebase,defensabase,ataquenivel,defensanivel,expbase,expmult,expgana,maxnivel FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}mascotas.nombremascota={$conftp}tienemascotas.nombremascota AND nombrejug='{$jug}' AND img='{$idob}'");
        if (mysql_num_rows($rrt)) {
          $rr = mysql_fetch_row($rrt);
          $rrx = db_query("SELECT cantidad FROM tiene WHERE nombrejug='{$jug}' AND nombreobj='{$rr[4]}'");
          $rrb = db_query("SELECT img FROM objetos WHERE nombreobj='{$rr[4]}'");
          if (mysql_num_rows($rrx)) {
            $rb = mysql_fetch_row($rrb);
            $expmax = $rr[9]+($rr[10]*($rr[2]-1));
            if ($rr[2] >= $rr[12])
            $expmax = 0;
            if (($expmax > 0) && ($rr[3] >= $expmax)) {
              quita_objeto($jug,$rr[4],$rb[0]);
              $retvalx1 = db_query("UPDATE {$conftp}tienemascotas set nivel=nivel+1,experiencia=0 WHERE nombrejug='{$jug}' AND nombremascota='{$rr[0]}'");
              $_SESSION['mensaje'] = '¡Tu mascota ha subido de nivel y se ha puesto más fuerte!';
            }
            else
              $_SESSION['error'] = 'Esta mascota no tiene suficiente experiencia para comer o ha alcanzado su nivel máximo.';
          }
          else
            $_SESSION['error'] = 'No tienes ese alimento.';
          mysql_free_result($rrx);
          mysql_free_result($rrb);
        }
        else
          $_SESSION['error'] = 'No tienes esa mascota';
        mysql_free_result($rrt);
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: mascotas.php');
    return 1;
  }

}

?>
