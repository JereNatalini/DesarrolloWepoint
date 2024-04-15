<!-- Funciones propias de la base de datos -->
<?php


//Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=wepoint_api', 'wepoint', 'W1DjSYZJ0BLP'));
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=wepoint_api', 'root', ''));
//Flight::register('db', 'PDO', array('mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=wepoint_api', 'root', ''));


?>

<!-- Aca no se si poner la cadena de coneccion a la base de datos y todos los script de SQL para hacerlo mas seguro y comodo pa nosotros  --> 