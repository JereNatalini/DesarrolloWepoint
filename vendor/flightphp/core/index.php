<?php

declare(strict_types=1);

require 'flight/Flight.php';
// require 'flight/autoload.php';

Flight::route('GET /test', function () {
    echo 'hello world!';
});

Flight::start();
