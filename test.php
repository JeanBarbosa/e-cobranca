<?php

// Autoload files using Composer autoload
require_once 'vendor/autoload.php';

use SIGCB\BoletoCaixa;

$boleto = new \SIGCB\BoletoCaixa();

var_dump($boleto->consultarBoleto());
