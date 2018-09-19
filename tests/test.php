<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Autoload files using Composer autoload
require_once '../vendor/autoload.php';

use Caixa\BoletoCaixa;

$boleto = new BoletoCaixa();

$boleto->setCodigoBeneficiario('045864');
$boleto->setUnidade('2301');
$boleto->setIdProcesso('045864');
$boleto->setCnpj('013.971.668/0001-28');
$boleto->setNossoNumero('14000000105283192');
$boleto->setNumeroDocumento('TEST0008');
$boleto->setDataVencimento('2018-09-30');
$boleto->setValor('1.0');
$boleto->setTipoEspecie('02');
$boleto->setFlagAceite('N');
$boleto->setDataEmissao('2018-09-11');
$boleto->setValorAbatimento('0');
$boleto->setNumeroDias('30');
$boleto->setCpf('04878901160');
$boleto->setNome('Jean Barbosa dos Santos');
$boleto->setLogradouro('E. Paranhos, Nº 10');
$boleto->setCidade('Paranoa');
$boleto->setUf('DF');
$boleto->setCep('73255050');
$boleto->setMensagemFichaCompesacao('Pagamento referente ao evento da Ajufe: 1º Encontro Internacional da AJUFE');

$boleto->setDebug(true);

$response = $boleto->incluirBoleto();

print_r($response); die;