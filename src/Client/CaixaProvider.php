<?php

namespace Caixa\Client;

use Caixa\BoletoCaixa;
use Caixa\Helpers\Parser;

class CaixaProvider
{

    private $client;

    protected $pathWsdlManutencao = 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo';
    protected $pathWsdlConsulta = 'https://barramento.caixa.gov.br/sibar/ConsultaCobrancaBancaria/Boleto';

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
    protected $usuarioServico = 'SGCBS02P';

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
    public function generatorHash($codBeneficiario, $docRegistro, $nossoNumero = 0, $dataVencimento = '', $valor = '')
    {

        // TODO validar parametros recebidos (format date e cpf or cpnj)
        $raw = preg_replace('/[^A-Za-z0-9]/', '',
            '0' . $codBeneficiario .
            $nossoNumero .
            ((!$dataVencimento) ?
                sprintf('%08d', 0) :
                strftime('%d%m%Y', strtotime($dataVencimento))) .
            sprintf('%015d', preg_replace('/[^0-9]/', '', $valor)) .
            sprintf('%014d', $docRegistro));

        $this->autenticacao = base64_encode(hash('sha256', $raw, true));

        return $this->autenticacao;
    }

    public function sendRequest($options, $type)
    {
        $options = Parser::fromXml($options, $type);
        $path = ($type == 'CONSULTA_BOLETO') ? $this->pathWsdlConsulta: $this->pathWsdlManutencao;

        try {

            $connCURL = curl_init($path);
            curl_setopt($connCURL, CURLOPT_POSTFIELDS, $options);
            curl_setopt($connCURL, CURLOPT_POST, true);
            curl_setopt($connCURL, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($connCURL, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($connCURL, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($connCURL, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/xml',
                'SOAPAction: "' . $type . '"'
            ));

            $response = curl_exec($connCURL);
            $err = curl_error($connCURL);
            curl_close($connCURL);

            return $response;

        } catch (\Exception $e) {

        }

    }


    public function consulta(BoletoCaixa $boleto)
    {
        $hashAutenticacao = $this->generatorHash(
            $boleto->getCodigoBeneficiario(),
            $boleto->getCnpj(),
            $boleto->getNossoNumero(),
            0,
            0
        );

        $arrayData = array(
            'soapenv:Body' => array(
                'consultacobrancabancaria:SERVICO_ENTRADA' => array(
                    'sibar_base:HEADER' => array(
                        'VERSAO' => '1.0',
                        'AUTENTICACAO' => $hashAutenticacao,
                        //SGCBS02P - Produção | SGCBS01D - Desenvolvimento
                        'USUARIO_SERVICO' => $boleto->isDebug() ? 'SGCBS01D' : $this->usuarioServico,
                        'OPERACAO' => 'CONSULTA_BOLETO',
                        'SISTEMA_ORIGEM' => $this->sistemaOrigem,
                        'UNIDADE' => $boleto->getUnidade(),
                        'DATA_HORA' => date('YmdHis')
                    ),
                    'DADOS' => array(
                        'CONSULTA_BOLETO' => array(
                            'CODIGO_BENEFICIARIO' => $boleto->getCodigoBeneficiario(),
                            'NOSSO_NUMERO' => $boleto->getNossoNumero(),
                        )
                    )
                )
            )
        );

        return $this->sendRequest($arrayData, 'CONSULTA_BOLETO');

    }

    public function incluir(BoletoCaixa $boleto)
    {
        $hashAutenticacao = $this->generatorHash(
            $boleto->getCodigoBeneficiario(),
            $boleto->getCnpj(),
            $boleto->getNossoNumero(),
            $boleto->getDataVencimento(),
            $boleto->getValor()
        );

        $arrayDados = array(
            'soapenv:Body' => array(
                'manutencaocobrancabancaria:SERVICO_ENTRADA' => array(
                    'sibar_base:HEADER' => array(
                        'VERSAO' => $this->versao,
                        'AUTENTICACAO' => $hashAutenticacao,
                        //SGCBS02P - Produção | SGCBS01D - Desenvolvimento
                        'USUARIO_SERVICO' => $boleto->isDebug() ? 'SGCBS01D' : $this->usuarioServico,
                        'OPERACAO' => 'INCLUI_BOLETO',
                        'SISTEMA_ORIGEM' => $this->sistemaOrigem,
                        'UNIDADE' => $boleto->getUnidade(),
                        'DATA_HORA' => date('YmdHis')
                    ),
                    'DADOS' => array(
                        'INCLUI_BOLETO' => array(
                            'CODIGO_BENEFICIARIO' => $boleto->getCodigoBeneficiario(),
                            'TITULO' => array(
                                'NOSSO_NUMERO' => $boleto->getNossoNumero(),
                                'NUMERO_DOCUMENTO' => $boleto->getNumeroDocumento(),
                                //código interdo do boleto/título
                                'DATA_VENCIMENTO' => $boleto->getDataVencimento(),
                                'VALOR' => $boleto->getValor(),
                                'TIPO_ESPECIE' => $boleto->getTipoEspecie(),
                                // Olhar no manual qual enviar
                                'FLAG_ACEITE' => $boleto->getFlagAceite(),
                                // S-Aceite | N-Não aceite (reconhecimento de dívida pelo pagador)
                                'DATA_EMISSAO' => $boleto->getDataEmissao(),
                                'JUROS_MORA' => array(
                                    'TIPO' => $boleto->getTipo(),
                                    //'DATA' => $informacoes['dataJuros'],
                                    'PERCENTUAL' => $boleto->getJurosValor(),
                                ),
                                'VALOR_ABATIMENTO' => $boleto->getValorAbatimento(),
                                'POS_VENCIMENTO' => array(
                                    'ACAO' => $boleto->getAcao(),
                                    'NUMERO_DIAS' => $boleto->getNumeroDias(),
                                ),
                                'CODIGO_MOEDA' => $boleto->getCodigoMoeda(),
                                //Real
                                'PAGADOR' => array(
                                    'CPF' => $boleto->getCpf(),
                                    'NOME' => $boleto->getNome(),
                                    'ENDERECO' => array(
                                        'LOGRADOURO' => $boleto->getLogradouro(),
                                        'BAIRRO' => $boleto->getBairro(),
                                        'CIDADE' => $boleto->getCidade(),
                                        'UF' => $boleto->getUf(),
                                        'CEP' => $boleto->getCep()
                                    ),
                                ),
                                'FICHA_COMPENSACAO' => array(
                                    'MENSAGENS' => array(
                                        'MENSAGEM' => implode(',', $boleto->getMensagemFichaCompesacao()),
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $response = $this->sendRequest($arrayDados, 'INCLUI_BOLETO');

        return Parser::fromArray($response);
    }

    public function baixa(BoletoCaixa $boleto)
    {
        $hashAutenticacao = $this->generatorHash(
            $boleto->getCodigoBeneficiario(),
            $boleto->getCnpj(),
            $boleto->getNossoNumero(),
            0,
            0
        );

        $arrayDados = array(
            'soapenv:Body' => array(
                'manutencaocobrancabancaria:SERVICO_ENTRADA' => array(
                    'sibar_base:HEADER' => array(
                        'VERSAO' => $this->versao,
                        'AUTENTICACAO' => $hashAutenticacao,
                        //SGCBS02P - Produção | SGCBS01D - Desenvolvimento
                        'USUARIO_SERVICO' => $boleto->isDebug() ? 'SGCBS01D' : $this->usuarioServico,
                        'OPERACAO' => 'BAIXA_BOLETO',
                        'SISTEMA_ORIGEM' => $this->sistemaOrigem,
                        'UNIDADE' => $boleto->getUnidade(),
                        'DATA_HORA' => date('YmdHis')
                    ),
                    'DADOS' => array(
                        'BAIXA_BOLETO' => array(
                            'CODIGO_BENEFICIARIO' => $boleto->getCodigoBeneficiario(),
                            'NOSSO_NUMERO' => $boleto->getNossoNumero()
                        )
                    )
                )
            )
        );

        $response = $this->sendRequest($arrayDados, 'BAIXA_BOLETO');

        return Parser::fromArray($response);
    }


}