MySQLiExtension
===============

A perfect (IMO) MySQLi extension which I wrote a few years back that extends the PHP 5 MySQLi object.

It should be fairly straightforward to work out but if you need some asistance, feel free to Tweet me - @andymeek.

The class is already instantiated at the bottom so before you include this file, set your constants, for example:

<?php
// Database connection details
define("DB_HOST", 'localhost');
define("DB_USERNAME", 'user');
define("DB_PASSWORD", 'password');
define("DB_NAME", 'db');

require_once('MySQLiExtension.php');
?>

Enjoy!

Andy
