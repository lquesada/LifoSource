<?php
$form = 1;
include('core.php');
function iweb() {
  global $jug,$tag,$confvalorventa,$_REQUEST,$confmercencuentra,$confmercvecesmax,$time,$conftp,$us,$imgroot;

  $of = 0;
  if (isset($_REQUEST['of'])) {
    if ($_REQUEST['of'] == 'premium') {
      $of = 1;
    }
  }

  db_lock("{$conftp}aprendido READ");
  $retvab = db_query("SELECT resultado FROM {$conftp}aprendido WHERE nombrejug='{$jug}'");
  db_unlock();
  $nrb = mysql_num_rows($retvab);
  for ($i = 0;$i < $nrb;$i++) {
    $xen = mysql_fetch_row($retvab);
    $sabe[$i] = $xen[0];
  }
  mysql_free_result($retvab);

  if ($of) {

    techo('El mercader espera a que la tienda esté vacía y te hace pasar a la trastienda. Allí guarda los objetos de importación más extraños, interesantes... o inútiles pero bonitos que puedas imaginar. A cambio de ellos sólo pide joyas premium, porque su valor es muy elevado en el extranjero. Los objetos con el distintivo "ÚNICO" son totalmente exclusivos: no habrá nunca ninguna otra unidad a la venta y no se podrán conseguir de ninguna manera. "No se aceptan devoluciones" - te grita el mercader.');

    db_lock("{$conftp}objetos READ,{$conftp}ofertasesp READ,{$conftp}tiene READ");
    $retval3 = db_query("SELECT {$conftp}ofertasesp.nombreobj,tipo,img,ataq,prot,valor,niveluso,usos,unico,preciojoyas FROM {$conftp}objetos,{$conftp}ofertasesp WHERE {$conftp}objetos.nombreobj={$conftp}ofertasesp.nombreobj ORDER BY tipo ASC");
    $retvalpp = db_query("SELECT cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='Joya premium'");
    db_unlock();
    if (mysql_num_rows($retvalpp)) {
      $rxp = mysql_fetch_row($retvalpp);
      $njop = $rxp[0];
    }
    else
      $njop = 0;
    mysql_free_result($retvalpp);

    echo "<a href=\"mercader.php\">Regresar a la parte principal de la tienda</a>.<br/><br/>";
    echo "Tienes <b>{$njop}</b> joyas premium.<br/><br/>";

    $ultipo = '';
    $numrows = mysql_num_rows($retval3);
    echo '<b>Comprar objetos:</b><br/><br/>';
    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval3);
      if ($ultipo != $ret[1]) {
        $ultipo = $ret[1];
        echo "<br/>Categoría: {$ret[1]}.<br/><br/>";
      }
      echo "<form action=\"mercader.php\" method=\"post\">";
      echo "<div class=\"item\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[2]}\"/>";
      if ($ret[5] == 0) {
        if ($ret[7] != 3 && $ret[7] != 5 && $ret[7] != 6 && $ret[7] != 7 && $ret[7] != 8)
          $valorenoro = ' / INTRANSFERIBLE';
        else
          $valorenoro = '';
      }
      else
        $valorenoro = " / Valor: {$ret[5]} monedas de oro";
      if ($ret[8] == 1)
        $valorenoro .= ' / ÚNICO';

      if ($njop < $ret[9])
        echo '[comprar]';
      else {
        echo "<input type=\"submit\" name=\"comprarprem\" onclick=\"return c()\" value=\"Comprar\"/> ";
      }
      if ($ret[7] <= 1) {
        if ($ret[7])
          $usos = ' / UN SOLO USO';
        else
         $usos = '';
         echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} (Ataque: {$ret[3]} / Protección: {$ret[4]} / Nivel: {$ret[6]}{$usos}{$valorenoro})<br/></div>";
      }
      else if ($ret[7] == 2) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} (Energía: +{$ret[4]} / UN SOLO USO{$valorenoro})<br/></div>";      
      }
      else if ($ret[7] == 3) {
        $aprendido = '';
        for ($ba = 0;$ba < $nrb;$ba++) {
          if ($sabe[$ba] == $ret[0])
            $aprendido = ' / APRENDIDO';
        }
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} (Energía: -{$ret[3]} / UN SOLO USO{$aprendido}{$valorenoro})<br/></div>";
      }
      else if ($ret[7] == 4) {
        if ($valorenoro!='')
          $valorenoro='('.substr($valorenoro,3);
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} {$valorenoro}<br/></div>";      
      }
      else if ($ret[7] == 5) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} (consumir para obtener 1 semana premium{$valorenoro})<br/></div>";      
      }
      else if ($ret[7] == 6) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} (MASCOTA{$valorenoro})<br/></div>";      
      }
      else if ($ret[7] == 7) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} (Energía: -{$ret[3]} / Nivel: {$ret[6]} / MAPA{$valorenoro})<br/></div>";      
      }
      else if ($ret[7] == 8) {
        if ($valorenoro!='')
          $valorenoro='('.substr($valorenoro,3);
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio: <img src=\"{$imgroot}img/rprem.gif\" alt=\"Premium\"/>x{$ret[9]}.</b> {$ret[0]} {$valorenoro}<br/></div>";      
      }
      echo "</form>";
    }








    echo "<br/><br/><a href=\"mercader.php\">Volver</a><br/><br/>";
    return;
  }

  $cat = '';
  if (isset($_REQUEST['cat']))
    $cat = check_itemname($_REQUEST['cat']);

  /*Inserta objetos en el mercader*/
  db_lock("{$conftp}lasttime READ");
  $retval = db_query("SELECT * FROM {$conftp}lasttime");
  $ret = mysql_fetch_row($retval);
  mysql_free_result($retval);
  db_unlock();
  if (!$ret[0])
    $veces = 0;
  else
    $veces = floor(($time-$ret[0])/$confmercencuentra);
  if ($veces) {
    db_lock("{$conftp}lasttime WRITE,{$conftp}jugadores READ,{$conftp}objetos READ,{$conftp}ofertas WRITE");
    $retval = db_query("SELECT * FROM {$conftp}lasttime");
    $ret = mysql_fetch_row($retval);
    mysql_free_result($retval);
    if (!$ret[0]) {
      $veces = 0;
      db_query("UPDATE {$conftp}lasttime SET lasttime={$time}");
    }
    else {
      $veces = floor(($time-$ret[0])/$confmercencuentra);
      $vecesall = $veces;
      if ($veces>$confmercvecesmax)
        $veces = $confmercvecesmax;
      if ($veces>0) {
        $retval2 = db_query("SELECT MAX(nivel) FROM {$conftp}jugadores");
        $ret2 = mysql_fetch_row($retval2);
        mysql_free_result($retval2);
        $maxniv = $ret2[0]-2;
        if ($maxniv < 1) {
          db_query("UPDATE {$conftp}lasttime SET lasttime={$time}");
          $veces = 0;
        }
        $retval2 = db_query("SELECT nombreobj,posibilidad,img,tipo,ataq,prot,puntosencontrar,nivelcomprar FROM {$conftp}objetos WHERE nivelencontrar<={$maxniv} AND nivelencontrar>0 ORDER BY posibilidad DESC");
      
        $maxpos = 0;
        $numrows2 = mysql_num_rows($retval2);
        for ($y = 0;$y < $numrows2;$y++) {
          $reta[$y] = mysql_fetch_row($retval2);
          $maxpos += $reta[$y][1];
        }
        if ($maxpos == 0)
          $veces = 0;
        mysql_free_result($retval2);
        db_query("UPDATE {$conftp}lasttime SET lasttime=lasttime+{$confmercencuentra}*{$vecesall}");
      }
      for ($x = 0;$x < $veces;$x++) {
        $azar = rand(1,$maxpos)-$reta[0][1];
        $nuevo = 0;
        while ($azar > 0) {
          $nuevo++;
          $azar = $azar-$reta[$nuevo][1];
        }
        if (!$reta[$nuevo][7])
          addoferta($reta[$nuevo][0]);
      }
    }
    db_unlock();
  }
  
  techo('"¡Bueno, bonito y barato! ¿o no?" En el mercader puedes adquirir toda clase de objetos artesanales en base a tu nivel y al oro o joyas premium de que dispongas. Ten en cuenta que el mercader sólo te mostrará aquellos objetos que puedes permitirte pagar o usar. El mercader también se ofrecerá a comprar los objetos y artefactos que encuentres. Si vendes la última unidad del objeto que utilizas como insignia, tu insignia desaparecerá. El mercader también venderá objetos extraños que otros jugadores le hayan vendido.');

  db_lock("{$conftp}tiene READ,{$conftp}objetos READ");
  $retval2 = db_query("SELECT {$conftp}tiene.nombreobj,cantidad,tipo,img,ataq,prot,valor,usado,niveluso,usos FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj ORDER BY tipo ASC,ataq+prot DESC,posibilidad ASC");
  $retvalpp = db_query("SELECT cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='Joya premium'");
  db_unlock();
  if (mysql_num_rows($retvalpp)) {
    $rxp = mysql_fetch_row($retvalpp);
    $njop = $rxp[0];
  }
  else
    $njop = 0;
  mysql_free_result($retvalpp);

  echo "Tienes <b>{$njop}</b> joyas premium. Puedes cambiarlas por <a href=\"mercader.php?of=premium\">ofertas premium especiales</a><br/><br/>";

  echo "Tienes <b>{$us['oro']}</b> monedas de oro.<br/><br/>";

  if ($cat) {
    if (isset($_REQUEST['p']))
      $p = $_REQUEST['p'];
    else 
      $p = 1;
    if (!is_numeric($p))
      $p = 1;
    if ($p < 1)
      $p = 1;
    $ini = ($p-1)*8;
    $fin = $p*8;

    db_lock("{$conftp}objetos READ,{$conftp}ofertas READ");
    $retvaloff = db_query("SELECT DISTINCT nombreobj FROM {$conftp}ofertas");
    $nroff = mysql_num_rows($retvaloff);
    $query = '(';
    for ($g = 0;$g < $nroff;$g++) {
      $ret = mysql_fetch_row($retvaloff);
      $query .= "'{$ret[0]}',";
    }
    $query .= '\'0_$\')';
    mysql_free_result($retvaloff);
    $retval3 = db_query("SELECT nombreobj,tipo,img,ataq,prot,valor,niveluso,usos,posibilidad FROM {$conftp}objetos WHERE ((niveluso<={$us['nivel']}) OR (valor<={$us['oro']})) AND ((nivelcomprar>0 AND nivelcomprar<={$us['nivel']}) OR nombreobj IN {$query}) AND tipo='{$cat}' ORDER BY ataq+prot DESC,posibilidad ASC LIMIT {$ini},1000");
    $retvalt = db_query("SELECT COUNT(*) FROM {$conftp}objetos WHERE ((niveluso<={$us['nivel']}) OR (valor<={$us['oro']})) AND ((nivelcomprar>0 AND nivelcomprar<={$us['nivel']}) OR nombreobj IN {$query}) AND tipo='{$cat}'");
    db_unlock();


    $ultipo = '';
    $numrows = mysql_num_rows($retval3);
    echo '<b>Comprar objetos:</b><br/><br/>';
    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval3);
      if ($ultipo != $ret[1]) {
        $ultipo = $ret[1];
        echo "<br/>Categoría: {$ret[1]}.<br/><br/>";
      }
      echo "<form action=\"mercader.php\" method=\"post\">";
      echo "<div class=\"item\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"cat\" value=\"{$cat}\"/>";
      echo "<input type=\"hidden\" name=\"p\" value=\"{$p}\"/>";
      echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[2]}\"/>";
      if ($us['oro'] < $ret[5])
        echo '[comprar]';
      else {
        echo "<input type=\"submit\" name=\"comprar\" onclick=\"return c()\" value=\"Comprar\"/>x<input type=\"text\" name=\"cantidad\" value=\"1\" size=\"2\" maxlength=\"5\"/> ";
      }
      if ($ret[7] <= 1) {
        if ($ret[7])
          $usos = ' / UN SOLO USO';
        else
         $usos = '';
         echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]} (Ataque: {$ret[3]} / Protección: {$ret[4]} / Nivel: {$ret[6]}{$usos})<br/></div>";
      }
      else if ($ret[7] == 2) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]} (Energía: +{$ret[4]} / UN SOLO USO)<br/></div>";      
      }
      else if ($ret[7] == 3) {
        $aprendido = '';
        for ($ba = 0;$ba < $nrb;$ba++) {
          if ($sabe[$ba] == $ret[0])
            $aprendido = ' / APRENDIDO';
        }
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]} (Energía: -{$ret[3]} / UN SOLO USO{$aprendido})<br/></div>";
      }
      else if ($ret[7] == 4) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]}<br/></div>";      
      }
      else if ($ret[7] == 5) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]} (consumir para obtener 1 semana premium)<br/></div>";      
      }
      else if ($ret[7] == 6) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]} (MASCOTA)<br/></div>";      
      }
      else if ($ret[7] == 7) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]} (Energía: -{$ret[3]} / Nivel: {$ret[6]} / MAPA)<br/></div>";      
      }
      else if ($ret[7] == 8) {
        echo "<img src=\"{$imgroot}img/{$ret[2]}.gif\" alt=\"{$ret[1]}\"/> <b>Precio compra: {$ret[5]}.</b> {$ret[0]}<br/></div>";      
      }
      echo "</form>";
    }
    mysql_free_result($retval3);
    $ret2 = mysql_fetch_row($retvalt);
    mysql_free_result($retvalt);
    $npags = $ret2[0]/1000;
    $npag = floor($npags);
    if ($npag != $npags)
      $npag++;
    if ($npag > 1) {
      echo "Página: ";
      for ($i = 1;$i <= $npag;$i++) {
        if ($i != $p)
          echo "<a href=\"mercader.php?cat={$cat}&amp;p={$i}\">{$i}</a> ";
        else
          echo "<b>{$i}</b> ";
      }
    }
    echo '<br/><br/>';
    echo "<a href=\"mercader.php\">Volver</a><br/>";
  }
  else {
    db_lock("{$conftp}objetos READ,{$conftp}ofertas READ,{$conftp}tiene READ");
    $retvaloff = db_query("SELECT DISTINCT nombreobj FROM {$conftp}ofertas");
    $nroff = mysql_num_rows($retvaloff);
    $query = '(';
    for ($g = 0;$g < $nroff;$g++) {
      $ret = mysql_fetch_row($retvaloff);
      $query .= "'{$ret[0]}',";
    }
    $query .= '\'0_$\')';
    mysql_free_result($retvaloff);
    $retvaln = db_query("SELECT DISTINCT tipo,COUNT(*) FROM {$conftp}objetos WHERE ((niveluso<={$us['nivel']}) OR (valor<={$us['oro']})) AND ((nivelcomprar>0 AND nivelcomprar<={$us['nivel']}) OR nombreobj IN $query) GROUP by tipo");
    $retvalx1 = db_query("SELECT {$conftp}tiene.nombreobj,cantidad-usado,valor FROM {$conftp}tiene,{$conftp}objetos WHERE {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}' AND usos=0");
    db_unlock();
    $retn = mysql_num_rows($retvaln);
    if ($retn) {
      echo '<b>Ver objetos ofertados por categoría:</b><br/><br/>';
      for ($i = 0;$i < $retn;$i++) {
        $ret = mysql_fetch_row($retvaln);
        echo "<a href=\"mercader.php?cat={$ret[0]}\">Categoría: {$ret[0]} ({$ret[1]} objetos)</a><br/>";
      }
      mysql_free_result($retvaln);
    }
    echo '<br/><br/>';
    $ultipo = '';
    $numrows = mysql_num_rows($retval2);
    echo '<b>Vender objetos:</b><br/><br/>';
    $ventatot = 0;
    $numrowsx1 = mysql_num_rows($retvalx1);

    for ($x = 0;$x < $numrowsx1;$x++) {
      $retx = mysql_fetch_row($retvalx1);
      $ventatot = $ventatot+floor($retx[2]*$confvalorventa)*$retx[1];
    }
    mysql_free_result($retvalx1);

    echo "<form action=\"mercader.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
    echo "<input type=\"submit\" name=\"vendertodo\" onclick=\"return c()\" value=\"Vender armaduras y armas que no están siendo usados por {$ventatot} monedas de oro\"/> ";
    echo "</div></form>";


    for ($i = 0;$i < $numrows;$i++) {
      $ret = mysql_fetch_row($retval2);
      if ($ultipo != $ret[2]) {
        $ultipo = $ret[2];
        echo "<br/>Categoría: {$ret[2]}.<br/><br/>";
      }
      echo "<form action=\"mercader.php\" method=\"post\">";
      echo "<div class=\"item\">";
      echo "<input type=\"hidden\" name=\"tag\" value=\"{$tag}\"/>";
      echo "<input type=\"hidden\" name=\"idob\" value=\"{$ret[3]}\"/>";
      $venta = floor($ret[6]*$confvalorventa);
      if ($venta <= 0) {
          echo '[no vendible]';
      }
      else if (($ret[7] == 1) && ($ret[1] == 1))
        echo '[en uso] ';
      else
        echo "<input type=\"submit\" name=\"vender\" onclick=\"return c()\" value=\"Vender\"/>x<input type=\"text\" name=\"cantidad\" value=\"1\" size=\"2\" maxlength=\"5\"/> ";
      if ($ret[9] <= 1) {
        if ($ret[9])
          $usos = ' / UN SOLO USO';
        else
          $usos = '';
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]} (Ataque: {$ret[4]} / Protección: {$ret[5]} / Nivel: {$ret[8]}{$usos})<br/></div>";
      }
      else if ($ret[9] == 2) {
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]} (Energía: +{$ret[5]} / UN SOLO USO)<br/></div>";                                           
      }
      else if ($ret[9] == 3) {
        $aprendido = '';
        for ($ba = 0;$ba < $nrb;$ba++) {
          if ($sabe[$ba] == $ret[0])
            $aprendido = ' / APRENDIDO';
        }
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]} (Energía: -{$ret[4]} / UN SOLO USO{$aprendido})<br/></div>";
      }
      else if ($ret[9] == 4) {
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]}<br/></div>";
      }
      else if ($ret[9] == 5) {
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]} (consumir para obtener 1 semana premium)<br/></div>";
      }
      else if ($ret[9] == 6) {
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]} (MASCOTA)<br/></div>";
      }
      else if ($ret[9] == 7) {
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]} (Energía: -{$ret[4]} / Nivel: {$ret[8]} / MAPA)<br/></div>";
      }
      else if ($ret[9] == 8) {
        echo "<img src=\"{$imgroot}img/{$ret[3]}.gif\" alt=\"{$ret[2]}\"/><b>x{$ret[1]} Precio venta: {$venta}.</b> {$ret[0]}<br/></div>";
      }
      echo "</form>";
    }
    mysql_free_result($retval2);
  }
}

function procesaform() {
  global $_REQUEST,$tag,$jug,$confvalorventa,$conftp;

  if (isset($_REQUEST['vendertodo'])) {
    if (isset($_REQUEST['tag'])) {
      $gtag = $_REQUEST['tag'];
      if ($tag == $gtag) {
        db_lock("{$conftp}tiene WRITE,{$conftp}objetos READ,{$conftp}jugadores WRITE,{$conftp}ofertas WRITE");
        $retvalx1 = db_query("SELECT {$conftp}tiene.nombreobj,cantidad-usado,valor,nivelcomprar,img FROM {$conftp}tiene,{$conftp}objetos WHERE {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$jug}' AND valor>0 AND usos=0 AND cantidad-usado>0");
        $ventatot = 0;
        $numrowsx1 = mysql_num_rows($retvalx1);
        for ($x = 0;$x < $numrowsx1;$x++) {
          $retx = mysql_fetch_row($retvalx1);
          $ventatot = $ventatot+floor($retx[2]*$confvalorventa)*$retx[1];
          if (!$retx[3]) {
            addoferta($retx[0],$retx[1]);
          }
          quita_objeto($jug,$retx[0],$retx[4],$retx[1]);
        }
        mysql_free_result($retvalx1);
        db_query("UPDATE {$conftp}jugadores set oro=oro+{$ventatot} WHERE nombrejug='{$jug}'");
        $_SESSION['mensaje'] = "Has vendido todos los objetos que no estaban en uso por {$ventatot} monedas de oro.";
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor!';
    }
    header('Location: mercader.php');
    return 1;
  }

  if (isset($_REQUEST['vender'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob'])) && (isset($_REQUEST['cantidad']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      $cantidad = floor($_REQUEST['cantidad']);
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob)) && (is_numeric($cantidad)) && ($cantidad > 0)) {
        db_lock("{$conftp}jugadores WRITE,{$conftp}tiene WRITE,{$conftp}objetos READ,{$conftp}ofertas WRITE");
        $retval = db_query("SELECT {$conftp}tiene.nombreobj,valor,img,usado,cantidad,nivelcomprar FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$jug}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}objetos.img='{$idob}' AND ((cantidad>={$cantidad} AND usado=0) OR (cantidad-1>={$cantidad} AND usado=1))");
        if (mysql_num_rows($retval)) {
          $ret = mysql_fetch_row($retval);
          $valor = floor($ret[1]*$confvalorventa);
	  if ($valor > 0) {
            db_query("UPDATE {$conftp}jugadores SET oro=oro+{$valor}*{$cantidad} WHERE nombrejug='{$jug}'");
            if (!$ret[5])
              addoferta($ret[0],$cantidad);
            quita_objeto($jug,$ret[0],$ret[2],$cantidad);
            $_SESSION['mensaje'] = 'Objetos vendidos.';
	  }
	  else
	    $_SESSION['error'] = 'Este objeto no se puede vender.';
        }
        else
          $_SESSION['error'] = '¡No puedes vender esa cantidad de ese objeto porque no la tienes o la estás usando!';
        mysql_free_result($retval);
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor! (indica una cantidad válida)';
    }
    header('Location: mercader.php');
    return 1;
  }

  if (isset($_REQUEST['comprar'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob'])) && (isset($_REQUEST['cantidad']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      $cantidad = floor($_REQUEST['cantidad']);
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob)) && (is_numeric($cantidad)) && ($cantidad > 0)) {
        db_lock("{$conftp}jugadores WRITE,{$conftp}tiene WRITE,{$conftp}objetos READ,{$conftp}ofertas WRITE");
        $retval = db_query("SELECT oro,nivel FROM {$conftp}jugadores WHERE nombrejug='{$jug}'");
        $ret = mysql_fetch_row($retval);
        mysql_free_result($retval);
        $retval = db_query("SELECT nombreobj,valor FROM {$conftp}objetos WHERE img='{$idob}' AND nivelcomprar<={$ret[1]} AND nivelcomprar>0 AND valor*{$cantidad}<={$ret[0]}");
        if (mysql_num_rows($retval)) {
          $ret2 = mysql_fetch_row($retval);
          $oro = $ret[0]-$ret2[1]*$cantidad;
          db_query("UPDATE {$conftp}jugadores SET oro={$oro} WHERE nombrejug='{$jug}'");
          dale_objeto($jug,$ret2[0],$cantidad);
	  $_SESSION['mensaje'] = 'Objetos comprados.';
        }
	else {
          $retval = db_query("SELECT {$conftp}objetos.nombreobj,valor,nivelcomprar,cantidad FROM {$conftp}ofertas,{$conftp}objetos WHERE {$conftp}ofertas.nombreobj={$conftp}objetos.nombreobj AND valor*{$cantidad}<={$ret[0]} AND img='{$idob}'");
          if (mysql_num_rows($retval)) {
            $ret2 = mysql_fetch_row($retval);
            if ($ret2[3] >= $cantidad) {
              $oro = $ret[0]-$ret2[1]*$cantidad;
              db_query("UPDATE {$conftp}jugadores SET oro={$oro} WHERE nombrejug='{$jug}'");
              dale_objeto($jug,$ret2[0],$cantidad);
              if (!$ret2[2])
                remoferta($ret2[0],$cantidad);
	      $_SESSION['mensaje'] = 'Objetos comprados.';
            }
            else {
              $_SESSION['error'] = 'No hay tantas unidades disponibles de este objeto.';
            }
	  }
	  else
            $_SESSION['error'] = 'No tienes oro para comprar ese objeto o el objeto no está en venta.';
	}
        db_unlock();
        mysql_free_result($retval);
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor! (indica una cantidad válida)';
    }
    if (isset($_REQUEST['cat']))
      $cat = check_itemname($_REQUEST['cat']);
    else
      $cat = '';
    if (isset($_REQUEST['p']))
      $p = $_REQUEST['p'];
    else
      $p = '';

    header("Location: mercader.php?cat={$cat}&p={$p}");
    return 1;
  }

  if (isset($_REQUEST['comprarprem'])) {
    if ((isset($_REQUEST['tag'])) && (isset($_REQUEST['idob']))) {
      $gtag = $_REQUEST['tag'];
      $idob = $_REQUEST['idob'];
      $cantidad = floor($_REQUEST['cantidad']);
      if (($tag == $gtag) && (ereg('^[a-z]+$',$idob))) {
        db_lock("{$conftp}jugadores WRITE,{$conftp}tiene WRITE,{$conftp}objetos READ,{$conftp}ofertasesp WRITE");
        $retvalpp = db_query("SELECT cantidad FROM {$conftp}tiene WHERE nombrejug='{$jug}' AND nombreobj='Joya premium'");
        if (mysql_num_rows($retvalpp)) {
          $rxp = mysql_fetch_row($retvalpp);
          $njop = $rxp[0];
        }
        else
          $njop = 0;
        mysql_free_result($retvalpp);
        $retvalpp2 = db_query("SELECT unico,preciojoyas,{$conftp}objetos.nombreobj FROM {$conftp}ofertasesp,{$conftp}objetos WHERE img='{$idob}' AND {$conftp}ofertasesp.nombreobj={$conftp}objetos.nombreobj");
        if (mysql_num_rows($retvalpp2)) {
          $bb = mysql_fetch_row($retvalpp2);
          if ($bb[1] <= $njop) {
            quita_objeto($jug,'Joya premium','rprem',$bb[1]);
            dale_objeto($jug,$bb[2]);
            if ($bb[0]) {
              db_query("DELETE FROM {$conftp}ofertasesp WHERE nombreobj='{$bb[2]}'");
              $_SESSION['mensaje'] = 'Objeto único adquirido!';
            }
            else
              $_SESSION['mensaje'] = 'Objeto adquirido!.';
          }
          else
            $_SESSION['error'] = 'No tienes suficientes joyas premium para adquirir este objeto.';
        }
        else
          $_SESSION['error'] = 'Este objeto ya no está en venta.';
        db_unlock();
      }
      else
        $_SESSION['error'] = '¡Intenta de nuevo, por favor! (indica una cantidad válida)';
    }

    header("Location: mercader.php?of=premium");
    return 1;
  }
}

?>

