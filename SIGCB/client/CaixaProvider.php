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

    /**
     * Cálculo do Hash de autenticação segundo o manual.
     * CÓDIGO DO BENEFICIÁRIO (7 POSIÇÕES) + NOSSO NÚMERO (17 POSIÇÕES) + DATA DE VENCIMENTO (DDMMAAAA) + VALOR (15 POSIÇÕES) + CPF OU CNPJ DO BENEFICIÁRIO (14 POSIÇÕES)
     *
     * @param $codigoBeneficiario   Código fornecido pela CAIXA, através da agência de relacionamento do cliente. Deve ser preenchido com o código do Beneficiário, até 7 posições, da esquerda para direita.
     * @param $docRegistro          CPF ou CNPJ
     * @param $nossoNumero          Nosso Número – Se informado zeros, o nosso número será gerado pelo banco. Caso contrário deverá ser informado número iniciando com 14. Exemplo: 14000000000000001.
     * @param $dataVencimento       Data de vencimento do título de cobrança no formato YYYY-MM-DD. //CONSULTA_BOLETO e BAIXA_BOLETO. Para Data de Vencimento e Valor, informar zeros.
     * @param $valor                Valor original do Título. Valor expresso em moeda corrente, utilizar 2 casas decimais. Exemplo: 0000000000000.00
     * @return string               retorna o hash dos dados fornecidos
     */
    public function hashAutenticacao($codigoBeneficiario, $docRegistro, $nossoNumero = 0, $dataVencimento = '', $valor = '')
    {
        // TODO validar parametros recebidos (format date e cpf or cpnj)
        $raw = preg_replace('/[^A-Za-z0-9]/', '',
            '0' . $codigoBeneficiario .
            $nossoNumero .
            ((!$dataVencimento) ?
                sprintf('%08d', 0) :
                strftime('%d%m%Y', strtotime($dataVencimento))) .
            sprintf('%015d', preg_replace('/[^0-9]/', '', $valor)) .
            sprintf('%014d', $docRegistro));

        $this->autenticacao = base64_encode(hash('sha256', $raw, true));

        return $this->autenticacao;
    }

}