<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Autoload files using Composer autoload
require_once '../vendor/autoload.php';

use Caixa\BoletoCaixa;

$boleto = new BoletoCaixa();

$boleto->setCodigoBeneficiario('012345');
$boleto->setUnidade('0000');
$boleto->setIdProcesso('012345');
$boleto->setCnpj('000.000.000/0000-00');
$boleto->setNossoNumero('14000000000000000');
$boleto->setNumeroDocumento('TEST0001');
$boleto->setDataVencimento('2018-09-30');
$boleto->setValor('1.0');
$boleto->setTipoEspecie('02');
$boleto->setFlagAceite('N');
$boleto->setDataEmissao('2018-09-11');
$boleto->setValorAbatimento('0');
$boleto->setNumeroDias('30');
$boleto->setCpf('000.000.000-00');
$boleto->setNome('Jean Barbosa dos Santos');
$boleto->setLogradouro('E. Paranhos, Nº 00');
$boleto->setCidade('Brasilia');
$boleto->setUf('DF');
$boleto->setCep('00.000-000');

$response = $boleto->incluirBoleto();

print_r($response);