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

echo "Estableciendo conexión con el servidor MySQL.\n";
db_connect();

echo "Borrando base de datos antigua si existe.\n";
db_query("DROP DATABASE IF EXISTS {$confdbname};");


?>
