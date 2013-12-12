<?php
$publico = 1;
$hidelogin = 0;
$form = 1;
include('core.php');
function iweb() {
  global $ident,$conftp;
  echo 'Sistema desactivado.';
  return;
  if ($ident) {
    echo 'Ya estás registrado. Debes salir antes de intentar recuperar tu contraseña.<br/><br/>';
    return;
  }
  techo('¿Has olvidado tu contraseña? Introduce tu nombre de usuario y el correo electrónico de tu cuenta en el formulario que hay aquí debajo, y se generará y enviará una nueva contraseña para ese usuario a tu correo.<br/><br/>El abuso de esta opción para molestar a otros jugadores no está permitido y será sancionado.');
  echo "<form method=\"post\" action=\"recupera.php\">";
  echo "<table id=\"login\"><tr><td>";
  echo '<a href="estado.php">-&gt; ¡Recordé mi contraseña! &lt;-</a><br/><br/>';
  echo "Recuperar contraseña:<br/><hr/><br/>";

  echo "Nombre de usuario:<br/>";
  echo "<input type=\"text\" name=\"username\" size=\"25\" maxlength=\"14\"/><br/><br/>";

  echo "E-mail:<br/>";
  echo "<input type=\"text\" name=\"email\" size=\"25\" maxlength=\"80\"/><br/><br/>";

  echo "<input type=\"submit\" name=\"recupera\" value=\"Recordar contraseña\"/><br/>";
  
  echo "</td></tr></table></form>";
}

function procesaform() {
  global $_REQUEST,$time,$conforonuevohijo,$conforonuevonieto,$confmailname,$ip,$confurl,$conftp;
  if (isset($_REQUEST['recupera'])) {
    $salirnow = 0;
    if ((isset($_REQUEST['username'])) && (isset($_REQUEST['email']))) {
      $username = check_username($_REQUEST['username']);
      $email = check_email($_REQUEST['email']);
      if ((!$username) && (!$email))
        $_SESSION['error'] = 'Tanto el usuario como el email no son válidos.';
      elseif (!$username)
        $_SESSION['error'] = 'El usuario no es válido.';
      elseif (!$email)
        $_SESSION['error'] = 'El email no es válido.';
      else {
        if (antifloodcheck('recupfalla',"{$username}.{$ip}",2,600)) {
          //sistema desactivado
        }
	else
	  $_SESSION['error'] = 'Demasiados intentos fallidos, intentelo dentro de diez minutos.';
      }
    }
    if ($salirnow)
      header('Location: estado.php');
    else
    header('Location: recupera.php');
    return 1;

  }
}

?>
