<?php

namespace SIGCB\Client;

use Zend\Soap\Client;
use SIGCB\BoletoCaixa;


class CaixaProvider {

    private $client;

    protected $pathWsdlConsulta = "https://des.barramento.caixa.gov.br/sibar/ConsultaCobrancaBancaria/Boleto?wsdl";

    /**
     * Versão do SIGCB - Sistema de Gestão da Cobrança Bancária
     * @var string
     */
    protected $versao = '1.2';

    /**
     * O campo deverá ser preenchido com um hash do tipo SHA256, codificado em Base64, com as informações abaixo:
     * CÓDIGO DO BENEFICIÁRIO (7 POSIÇÕES) + NOSSO NÚMERO (17 POSIÇÕES) + DATA DE VENCIMENTO (DDMMAAAA) + VALOR (15 POSIÇÕES) + CPF OU CNPJ DO BENEFICIÁRIO (14 POSIÇÕES)
     *
     * @var string
     */
    protected $autenticacao;

    /**
     * SIGCB - Sistema de Gestão da Cobrança Bancária.
     * @var string
     */
    protected $sistemaOrigem = 'SIGCB';

    /**
     * SGCBS02P - Produção
     * SGCBS01D - Desenvolvimento
     *
     * @var string
     */
    protected $usuarioServico = 'SGCBS01D';

    /**
     * IP da máquina do pagador que requisitou o registro do boleto
     * @var string
     */
    protected $identificadorOrigem;

    //SIGCB response
    protected $response;

    /**
     * $usuarioServico setado com valor SGCBS01D - Desenvolvimento
     *
     * @var bool
     */
    protected $isDebug = false;

    /**
     * Construtor atribui e formata parâmetros em $this->args
     */

    public function __construct()
    {
        $this->client = new \Zend\Soap\Client();
        $this->client->setWSDL($this->pathWsdlConsulta);

        //TODO Pegar IP da Maquina
        $this->identificadorOrigem = '127.0.0.1'; //$_SERVER['REMOTE_ADDR'];
    }

}