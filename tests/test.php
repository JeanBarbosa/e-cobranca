<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Autoload files using Composer autoload
require_once '../vendor/autoload.php';

use Caixa\BoletoCaixa;

$boleto = new BoletoCaixa();

$boleto->setCodigoBeneficiario('123456');
// set fields ...

var_dump($boleto->incluirBoleto());
