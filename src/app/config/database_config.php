<?php
return
    [
        'host' => 'localhost',
        'name' => 'mitula',
        'user' => 'root',
        'password' => 'password',
        'port' => '3306',
        'options' => [
            \PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, //make the default fetch be an associative array
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]
    ];