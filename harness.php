<?php

require 'vendor/autoload.php';

use mcordingley\LinearAlgebra\Matrix;

$matrix = new Matrix([
    [1, 400],
    [1, 600],
    [1, 800],
    [1, 1200],
    [1, 1600],
    [1, 2400],
    [1, 3200],
    [1, 4800],
    [1, 6400],
    [1, 9600],
    [1, 12800],
    [1, 19200],
    [1, 25600],
    [1, 38400],
    [1, 51200],
    [1, 76800],
    [1, 102400],
    [1, 153600],
    [1, 204800],
    [1, 307200],
    [1, 409600],
    [1, 614400],
    [1, 819200],
    [1, 1228800],
    [1, 1638400],
]);

var_dump($matrix->transpose()->multiply($matrix)->inverse()->toArray());//->inverse();
