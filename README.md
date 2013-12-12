LifoSource
==========

Código fuente del juego de rol online Lifo.

Copyright (c) 2006-2013, Luis Quesada - https://github.com/lquesada

C�mo montar una partida de LifoSource...

LifoSource tiene las siguientes diferencias respecto a Lifo:
- No se soportan m�ltiples partidas, ni transferencias de insignias o joyas entre partidas.
- No se pueden enviar correos, por lo que no funciona la opci�n de recuperar contrase�a.
- Hay objetos para los que no se incluyen gr�ficos.

Paso 1. Servidores web y MySQL.
-------------------------------

Es necesario instalar los siguientes paquetes:
- apache2
- mysql-server
- libapache2-mod-php5
- php5
- php5-mysql

Paso 2. Creaci�n de la base de datos.
-------------------------------

Seguidamente, debe crearse la base de datos y un usuario en MySQL para la partida de LifoSource.

Se puede hacer conectando como administrador (mysql -uroot -pPASSWORDROOT) y con las siguientes �rdenes, cambiando los valores en may�sculas:

    mysql> create database DATABASENAME;
    mysql> grant usage on *.* to USERNAME@% identified by 'PASSWORDLIFOSOURCE';
    mysql> grant all privileges on DATABASENAME.* to USERNAME@%;

Paso 3. Configuraci�n de la partida de LifoSource
-------------------------------

Se abre el fichero web/w_config.php y se modifican, al menos, los siguientes valores:

    // direcci�n raiz de la partida, debe ser accesible desde fuera.
    $root
    
    // host del servidor MySQL
    $confdbhost 
    // puerto del servidor MySQL
    $confdbport
    // usuario para el servidor MySQL
    $confdbuser
    // contrase�a para el usuario
    $confdbpass
    // nombre de la base de datos
    $confdbname
    
    // cadena de texto con cualquier valor para mejorar el cifrado de las contrase�as
    // NO CAMBIAR despues de hacer el emerge
    $confpwdsalt
    
    // contrase�a por defecto para la cuenta Admin
    $confadminpass
    // direcci�n de correo del administrador
    $confmail = 'adminmail@localhost';
    
    // nombre del juego
    $conftitle = 'Partida de LifoSource';
    // nombre del administrador
    $confadminname = 'NOMBRE APELLIDOS REALES';
    
    // aviso legal
    $confavisolegal

Es sumamente importante que modifiques la contrase�a del administrador.

No des acceso de administrador a nadie, la p�gina de administraci�n no es segura.

Paso 4. Instalaci�n de los ficheros de la web
-------------------------------

Se copian los ficheros del directorio web al directorio p�blico del servidor, por ejemplo, /var/www.

Paso 5. Instanciaci�n de la partida
-------------------------------

Se abre desde un navegador el fichero emerge/emerge.php a trav�s del servidor apache.

Si todo est� configurado correctamente (apache, mysql, php-mysql, usuario, contrase�a y base de datos del servidor) se generar� la partida y se crear� la cuenta Admin con la contrase�a indicada en la configuraci�n anterior.

Una vez creada la partida, es MUY IMPORTANTE eliminar el directorio emerge; si no, la partida podr� ser destruido por cualquier jugador que lance el mismo fichero emerge.php.


La partida ya est� abierta y disponible.

