#!/usr/bin/php
<?php
$stop = 1;
$_SERVER['REMOTE_ADDR'] = '0';
error_reporting(E_ALL);
include('../w_aux.php');
include('../w_config.php');
include('../w_database.php');
include('../w_check.php');
include('../w_ini.php');

echo "<html><body>";
echo "Estableciendo conexión con el servidor MySQL.<br/>";
db_connect();

$engine = 'MYISAM';

db_select_db();

echo "Borrando base de datos antigua si existe.<br/>";
db_query("DELETE FROM {$conftp}trabajos");
db_query("DELETE FROM {$conftp}textos");
db_query("DELETE FROM {$conftp}jugadores");
db_query("DELETE FROM {$conftp}objetos");
db_query("DELETE FROM {$conftp}claninsignia");
db_query("DELETE FROM {$conftp}tiene");
db_query("DELETE FROM {$conftp}ofertas");
db_query("DELETE FROM {$conftp}ofertasesp");
db_query("DELETE FROM {$conftp}mensajes");
db_query("DELETE FROM {$conftp}clandata");
db_query("DELETE FROM {$conftp}lasttime");
db_query("DELETE FROM {$conftp}loginlog");
db_query("DELETE FROM {$conftp}aprendido");
db_query("DELETE FROM {$conftp}flood");
db_query("DELETE FROM {$conftp}forohebras");
db_query("DELETE FROM {$conftp}foromensajes");
db_query("DELETE FROM {$conftp}recetas");
db_query("DELETE FROM {$conftp}contiene");
db_query("DELETE FROM {$conftp}ingrediente");
db_query("DELETE FROM {$conftp}conoce");
db_query("DELETE FROM {$conftp}explorado");
db_query("DELETE FROM {$conftp}exploracion");
db_query("DELETE FROM {$conftp}contador");
db_query("DELETE FROM {$conftp}fix");
db_query("DELETE FROM {$conftp}fix2");
db_query("DELETE FROM {$conftp}trueques");
db_query("DELETE FROM {$conftp}itemstrueques");
db_query("DELETE FROM {$conftp}silencio");
db_query("DELETE FROM {$conftp}mascotas");
db_query("DELETE FROM {$conftp}tienemascotas");
db_query("DROP TABLE IF EXISTS {$conftp}trabajos");
db_query("DROP TABLE IF EXISTS {$conftp}textos");
db_query("DROP TABLE IF EXISTS {$conftp}jugadores");
db_query("DROP TABLE IF EXISTS {$conftp}objetos");
db_query("DROP TABLE IF EXISTS {$conftp}claninsignia");
db_query("DROP TABLE IF EXISTS {$conftp}tiene");
db_query("DROP TABLE IF EXISTS {$conftp}ofertas");
db_query("DROP TABLE IF EXISTS {$conftp}ofertasesp");
db_query("DROP TABLE IF EXISTS {$conftp}mensajes");
db_query("DROP TABLE IF EXISTS {$conftp}clandata");
db_query("DROP TABLE IF EXISTS {$conftp}lasttime");
db_query("DROP TABLE IF EXISTS {$conftp}loginlog");
db_query("DROP TABLE IF EXISTS {$conftp}aprendido");
db_query("DROP TABLE IF EXISTS {$conftp}flood");
db_query("DROP TABLE IF EXISTS {$conftp}forohebras");
db_query("DROP TABLE IF EXISTS {$conftp}foromensajes");
db_query("DROP TABLE IF EXISTS {$conftp}recetas");
db_query("DROP TABLE IF EXISTS {$conftp}contiene");
db_query("DROP TABLE IF EXISTS {$conftp}ingrediente");
db_query("DROP TABLE IF EXISTS {$conftp}conoce");
db_query("DROP TABLE IF EXISTS {$conftp}explorado");
db_query("DROP TABLE IF EXISTS {$conftp}exploracion");
db_query("DROP TABLE IF EXISTS {$conftp}contador");
db_query("DROP TABLE IF EXISTS {$conftp}fix");
db_query("DROP TABLE IF EXISTS {$conftp}fix2");
db_query("DROP TABLE IF EXISTS {$conftp}trueques");
db_query("DROP TABLE IF EXISTS {$conftp}itemstrueques");
db_query("DROP TABLE IF EXISTS {$conftp}silencio");
db_query("DROP TABLE IF EXISTS {$conftp}mascotas");
db_query("DROP TABLE IF EXISTS {$conftp}tienemascotas");

echo "Creando base de datos nueva.<br/>";
db_query("CREATE DATABASE {$confdbname}");

echo "Seleccionando base de datos recién creada.<br/>";
db_select_db();

db_query("CREATE TABLE {$conftp}mascotas (
  nombremascota VARCHAR(40) NOT NULL PRIMARY KEY,
  nombreobj VARCHAR(40) NOT NULL,
  img VARCHAR(30) NOT NULL DEFAULT 'none',
  alimento VARCHAR(40) NOT NULL,
  ataquebase INT(20) NOT NULL,
  defensabase INT(20) NOT NULL,
  ataquenivel INT(20) NOT NULL,
  defensanivel INT(20) NOT NULL,
  expbase INT(20) NOT NULL,
  expmult INT(20) NOT NULL,
  expgana INT(20) NOT NULL,
  maxnivel INT(20) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}tienemascotas (
  nombrejug VARCHAR(30) NOT NULL,
  nombremascota VARCHAR(40) NOT NULL,
  nivel INT(20) NOT NULL,
  experiencia INT(20) NOT NULL,
  usado INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}trueques (
  idtrueque INT(14) PRIMARY KEY AUTO_INCREMENT,
  inicia VARCHAR(30) NOT NULL,
  recibe VARCHAR(30) NOT NULL,
  ultimocambio INT(1) NOT NULL,
  tiempo INT(14) NOT NULL,
  estado INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}itemstrueques (
  idtrueque INT(14),
  nombrejug VARCHAR(30) NOT NULL,
  nombreobj VARCHAR(40) NOT NULL,
  cantidad INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}trabajos (
  segundos INT(14) PRIMARY KEY,
  nombre VARCHAR(70) NOT NULL UNIQUE,
  puntos INT(20) NOT NULL,
  oro BIGINT(30) NOT NULL,
  premium INT(1) NOT NULL DEFAULT 0
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}textos (
  nombre VARCHAR(20) NOT NULL UNIQUE,
  texto TEXT
) ENGINE = {$engine} CHARACTER SET latin1;");
db_query("INSERT INTO {$conftp}textos (nombre,texto) VALUES ('noticias','No hay noticias actualmente')");
db_query("INSERT INTO {$conftp}textos (nombre,texto) VALUES ('noticiaspag','')");


db_query("CREATE TABLE {$conftp}jugadores (
  nombrejug VARCHAR(30) PRIMARY KEY,
  password VARCHAR(40) NOT NULL,
  email VARCHAR(80) NOT NULL,
  emailnuevo VARCHAR(80) NOT NULL DEFAULT '',
  emailnuevocambio INT(14) NOT NULL DEFAULT 0,
  creado INT(14) NOT NULL,
  login INT(14) NOT NULL DEFAULT 0,

  premiumhasta INT(14) NOT NULL DEFAULT 0,
  clan VARCHAR(30) NOT NULL DEFAULT '(ninguno)',
  baneadohasta INT(14) NOT NULL DEFAULT 0,

  emailreg VARCHAR(80) NOT NULL,
  ipcreado VARCHAR(15) NOT NULL,
  iplogin VARCHAR(15) NOT NULL,

  nivel INT(10) NOT NULL DEFAULT 1,

  insignia VARCHAR(30) NOT NULL DEFAULT 'none',

  puntos INT(20) NOT NULL DEFAULT 0,
  puntossuma INT(20) NOT NULL DEFAULT 0,
  puntosnivel INT(20) NOT NULL DEFAULT 0,
  puntosobjeto INT(20) NOT NULL DEFAULT 0,

  oro BIGINT(30) NOT NULL DEFAULT 0,
  energia INT(20) NOT NULL DEFAULT 100,

  trabajando INT(14) NOT NULL DEFAULT 0,
  fintrabajo INT(14) NOT NULL DEFAULT 0,
  puntostrabajo INT(20) NOT NULL DEFAULT 0,
  trabajopremium INT(1) NOT NULL DEFAULT 0,
  trabajado INT(14) NOT NULL DEFAULT 0,
  orotrabajo INT(10) NOT NULL DEFAULT 0,

  protegidohasta INT(14) NOT NULL DEFAULT 0,
  noatacarhasta INT(14) NOT NULL DEFAULT 0,
  noexplorarhasta INT(14) NOT NULL DEFAULT 0,
  nocomerhasta INT(14) NOT NULL DEFAULT 0,

  combates INT(10) NOT NULL DEFAULT 0,
  vencedor INT(10) NOT NULL DEFAULT 0,
  vencido INT(10) NOT NULL DEFAULT 0,

  visitashijos INT(10) NOT NULL DEFAULT 0,
  visitasnietos INT(10) NOT NULL DEFAULT 0,
  hijos INT(10) NOT NULL DEFAULT 0,
  nietos INT(10) NOT NULL DEFAULT 0,
  padre VARCHAR(30) NOT NULL DEFAULT '(desconocido)',

  ultimoforo INT(14) NOT NULL DEFAULT 0,
  zonahoraria INT(3) NOT NULL DEFAULT -15,

  ultimaact INT(14) NOT NULL DEFAULT 0,

  admin INT(1) NOT NULL DEFAULT 0,
  moderador INT(1) NOT NULL DEFAULT 0,
  enchufado INT(1) NOT NULL DEFAULT 0
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}objetos (
  nombreobj VARCHAR(40) PRIMARY KEY,
  tipo VARCHAR(10) NOT NULL,
  img VARCHAR(30) NOT NULL UNIQUE,
  nivelcomprar INT(10) NOT NULL,
  nivelencontrar INT(10) NOT NULL,
  niveluso INT(10) NOT NULL,
  valor BIGINT(30) NOT NULL,
  prot INT(20) NOT NULL,
  ataq INT(20) NOT NULL,
  posibilidad INT(10) NOT NULL,
  puntosencontrar INT(20) NOT NULL,
  usos INT(1) NOT NULL DEFAULT 0
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}claninsignia (
  clan VARCHAR(30) PRIMARY KEY,
  insignia VARCHAR(30) NOT NULL UNIQUE
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}tiene (
  nombrejug VARCHAR(30) NOT NULL,
  nombreobj VARCHAR(40) NOT NULL,
  cantidad INT(10) NOT NULL,
  usado INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (nombrejug,nombreobj)
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}ofertas (
  nombreobj VARCHAR(40) NOT NULL,
  cantidad INT(10) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}ofertasesp (
  nombreobj VARCHAR(40) NOT NULL,
  preciojoyas INT(10) NOT NULL,
  unico INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}mensajes (
  idmensaje INT(20) PRIMARY KEY AUTO_INCREMENT,
  nombrejug VARCHAR(30),
  remitente VARCHAR(30) NOT NULL,
  hora INT(14) NOT NULL,
  visto INT(1) NOT NULL DEFAULT 0,
  reportado INT(1) NOT NULL DEFAULT 0,
  mensaje TEXT
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}clandata (
  nombrejug VARCHAR(30) NOT NULL,
  clan VARCHAR(30) NOT NULL,
  solicita INT(1) NOT NULL DEFAULT 0,
  lider INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (nombrejug,clan)
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}contador (
  contador INT(14)
) ENGINE = {$engine} CHARACTER SET latin1;");
db_query("INSERT INTO {$conftp}contador VALUES (0)");

db_query("CREATE TABLE {$conftp}fix (
  contador INT(14)
) ENGINE = {$engine} CHARACTER SET latin1;");
db_query("INSERT INTO {$conftp}fix VALUES (0)");

db_query("CREATE TABLE {$conftp}fix2 (
  contador INT(14)
) ENGINE = {$engine} CHARACTER SET latin1;");
db_query("INSERT INTO {$conftp}fix2 VALUES (0)");

db_query("CREATE TABLE {$conftp}lasttime (
  lasttime INT(14)
) ENGINE = {$engine} CHARACTER SET latin1;");
db_query("INSERT INTO {$conftp}lasttime VALUES (0)");

db_query("CREATE TABLE {$conftp}flood (
  evento VARCHAR(20),
  actor VARCHAR(60),
  tiempo INT(14)
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}forohebras (
  id INT(14) PRIMARY KEY AUTO_INCREMENT,
  nombrejug VARCHAR(30) NOT NULL,
  nombre VARCHAR(80) NOT NULL,
  hora INT(14) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}foromensajes (
  idmensaje INT(14) PRIMARY KEY AUTO_INCREMENT,
  idhebra INT(14),
  nombrejug VARCHAR(30) NOT NULL,
  hora INT(14) NOT NULL,
  texto TEXT
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}recetas (
  resultado VARCHAR(40) NOT NULL,
  cantidadresultado INT(10) NOT NULL,
  tipo VARCHAR(30) NOT NULL,
  energia INT(20) NOT NULL,
  dificultadhacer INT(4) NOT NULL,
  perderingredientes INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}contiene (
  nombreobj VARCHAR(40) NOT NULL,
  resultado VARCHAR(40) NOT NULL,
  dificultadaprender INT(4) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}ingrediente (
  resultado VARCHAR(40) NOT NULL,
  nombreobj VARCHAR(40) NOT NULL,
  cantidad INT(10) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}conoce (
  nombrejug VARCHAR(30) NOT NULL,
  resultado VARCHAR(40) NOT NULL,
  cantidad INT(10) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}explorado (
  nombrejug VARCHAR(30) NOT NULL,
  mapa VARCHAR(40) NOT NULL,
  cantidad INT(10) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}exploracion (
  mapa VARCHAR(40) NOT NULL,
  nombreobj VARCHAR(40) NOT NULL,
  probabilidad INT(10) NOT NULL,
  exito INT(5) NOT NULL,
  vez INT(10) NOT NULL,
  exacto INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}aprendido (
  nombrejug VARCHAR(30) NOT NULL,
  resultado VARCHAR(40) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}loginlog (
  nombrejug VARCHAR(30) NOT NULL,
  hora INT(14) NOT NULL,
  ip VARCHAR(30) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}silencio (
  string VARCHAR(30) NOT NULL,
  stringip VARCHAR(30) NOT NULL,
  isip INT(1) NOT NULL,
  autor VARCHAR(30) NOT NULL,
  eliminado VARCHAR(30) NOT NULL,
  horapuesto INT(20) NOT NULL,
  horafin INT(20) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");


db_query("CREATE INDEX {$conftp}objetostipo ON {$conftp}objetos (tipo);");
db_query("CREATE INDEX {$conftp}objetosimg ON {$conftp}objetos (img);");
db_query("CREATE INDEX {$conftp}objetosnivelcomprar ON {$conftp}objetos (nivelcomprar);");
db_query("CREATE INDEX {$conftp}objetosnivelencontrar ON {$conftp}objetos (nivelencontrar);");
db_query("CREATE INDEX {$conftp}objetosniveluso ON {$conftp}objetos (niveluso);");
db_query("CREATE INDEX {$conftp}objetosvalor ON {$conftp}objetos (valor);");
db_query("CREATE INDEX {$conftp}objetosprot ON {$conftp}objetos (prot);");
db_query("CREATE INDEX {$conftp}objetosataq ON {$conftp}objetos (ataq);");

db_query("CREATE INDEX {$conftp}recetasresultado ON {$conftp}recetas (resultado);");
db_query("CREATE INDEX {$conftp}recetastipo ON {$conftp}recetas (tipo);");
db_query("CREATE INDEX {$conftp}recetasdificultadhacer ON {$conftp}recetas (dificultadhacer);");

db_query("CREATE INDEX {$conftp}contienenombreobj ON {$conftp}contiene (nombreobj);");
db_query("CREATE INDEX {$conftp}contieneresultado ON {$conftp}contiene (resultado);");

db_query("CREATE INDEX {$conftp}ingredienteresultado ON {$conftp}ingrediente (resultado);");
db_query("CREATE INDEX {$conftp}ingredientenombreobj ON {$conftp}ingrediente (nombreobj);");


echo "Inicializando datos.<br/>";
include('e_datos.php');

echo "Cerrando conexión con el servidor MySQL.<br/>";
db_close();

echo "Mundo creado corréctamente en la base de datos `{$confdbname}`.<br/>";
echo "<font color=\"red\"><b>IMPORTANTE: ELIMINE EL SUBDIRECTORIO EMERGE INMEDIATAMENTE</b></font>";
echo "</body></html>";


?>
