<?php

namespace Caixa;

use Caixa\Client\CaixaProvider;
use Caixa\Helpers\Parser;

class Boleto
{
    protected $banco = 'caixa';

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '104';

    /**
     * Numero da Agência de Relacionamento com 4 posições, sem dígito verificador
     * @var int
     */
    protected $unidade;

    /**
     * Código do Beneficiário, sem dígito verificador
     * @var integer
     */
    protected $idProcesso;


    /**
     * Código do Convênio no Banco (Código do Beneficiário)
     * Código fornecido pela CAIXA, através da agência de relacionamento do cliente.
     * Deve ser preenchido com o código do Beneficiário, até 7 posições, da esquerda para direita.
     *
     * @var string
     */
    protected $codigoBeneficiario;

    protected $cedente;
    protected $usuario;
    protected $indice;

    //Titulo

    /**
     * Código utilizado pelo banco para controle da carteira de cobrança.
     * Quando informado pelo cliente (iniciado em 14), é retornado na resposta da CAIXA com valor ‘0’.
     * @var string
     */
    protected $nossoNumero;


    /**
     * Número do Documento de Cobrança
     * Número utilizado e controlado pelo Cliente, para identificar o título de cobrança.
     * Poderá conter número de duplicata, no caso de cobrança de duplicatas; número da apólice,
     * no caso de cobrança de seguros, etc. Campo de preenchimento obrigatório.
     *
     * @var string
     */
    protected $numeroDocumento;

    /**
     * Data de Vencimento do Título
     * Data de vencimento do título de cobrança no formato YYYY-MM-DD
     *
     * @var string
     */
    protected $dataVencimento;

    /**
     * Valor Nominal do Título
     * Valor original do Título. Valor expresso em moeda corrente, utilizar 2 casas decimais. Exemplo: 0000000000000.00
     * Para Espécie do Boleto igual 31 – Cartão de Crédito ou 32 – Boleto de Proposta:
     * É permitido informar o valor nominal do título igual a 0.00, conforme os respectivos parâmetros de Identificação
     * do Tipo de Pagamento para cada espécie.
     *
     * @var float
     */
    protected $valor;

    /**
     * Espécie do Título
     * Código adotado para identificar o tipo de título de cobrança: Cód ID Descrição
     *
     * 01 CH Cheque
     * 02 DM Duplicata Mercantil
     * 03 DMI Duplicata Mercantil p/ Indicação 04 DS Duplicata de Serviço
     * 05 DSI Duplicata de Serviço p/ Indicação 06 DR Duplicata Rural
     * 07 LC Letra de Câmbio
     * 08 NCC Nota de Crédito Comercial
     * 09 NCE Nota de Crédito à Exportação 10 NCI Nota de Crédito Industrial
     * 11 NCR Nota de Crédito Rural
     * 12 NP Nota Promissória
     * 13 NPR Nota Promissória Rural
     * 14 TM Triplicata Mercantil
     * 15 TS Triplicata de Serviço
     * 16 NS Nota de Seguro
     * 17 RC Recibo
     * 18 FAT Fatura
     * 19 ND Nota de Débito
     * 20 AP Apólice de Seguro
     * 21 ME Mensalidade Escolar
     * 22 PC Parcela de Consórcio
     * 23 NF Nota Fiscal
     * 24 DD Documento de Dívida
     * 25 CPR Cédula de Produto Rural
     * 31 CC Cartão de Crédito
     * 32 BP Boleto de Proposta
     * 99 OU Outros
     *
     * As espécies 31 – CC Cartão de Crédito e 32 – BP Boleto de Proposta só poderão ser utilizadas caso autorizadas e
     * parametrizadas pela CAIXA para o código do beneficiário.
     * Para a espécie 31 – CC Cartão de Crédito, não é permitida aplicação de desconto, abatimento, juros e multa.
     * Para a espécie 32 – BP Boleto de Proposta, não é permitida aplicação de desconto, juros e multa.
     *
     * @var string
     */
    protected $tipoEspecie = '99';

    /**
     * Identificação de Título Aceito / Não Aceito
     * Código adotado para identificar se o título de cobrança foi aceito (reconhecimento da dívida pelo Pagador):
     * ‘S’ = Aceite
     * ‘N’ = Não Aceite
     *
     * @var string
     */
    protected $flagAceite = 'S';

    /**
     * Data da Emissão do Título
     * Data de emissão do Título. Utilizar o formato yyyy-MM-dd, onde:
     *  yyyy = ano
     *  MM = mês
     *  dd= dia
     *
     * @var string
     */
    protected $dataEmissao;

    /**
     * Código do Juros de Mora
     * Código para identificação do tipo de pagamento de juros de mora. Valores admissíveis:
     * VALOR_POR_DIA, TAXA_MENSAL ou ISENTO
     *
     * @var string defult ISENTO
     */
    protected $tipo = 'ISENTO';

    /**
     * Data do Juros de Mora
     * Data indicativa do início da cobrança de Juros de Mora de um título de cobrança,
     * deverá ser maior que a Data de Vencimento do título de cobrança.
     * Utilizar o formato yyyy-MM-dd, onde:
     * yyyy = ano
     * MM = mês
     * dd= dia
     *
     * Se Código do Juros de Mora = ISENTO, não enviar a tag <DATA></DATA> para Juros de Mora.
     *
     * @var string
     */
    protected $data;

    /**
     * Juros de Mora por Dia / Taxa
     * Valor ou porcentagem sobre o valor do título a ser cobrado de juros de mora. Utilizar conforme tag utilizada:
     * <VALOR></VALOR> 0000000000000.00
     * <PERCENTUAL></PERCENTUAL> 0000000000.00000
     *
     * Se Código do Juros de Mora = ISENTO, informar VALOR ou PERCENTUAL com zeros.
     *
     * @var float
     */
    protected $jurosValor = 0;

    /**
     * porcentagem sobre o valor do título a ser cobrado de juros de mora.
     * @var float
     */
    protected $percentual = 0;

    /**
     * Valor do Abatimento
     * Valor do abatimento (redução do valor do documento, devido a algum problema), expresso em moeda corrente.
     *
     * Utilizar conforme:
     * <VALOR_ABATIMENTO></VALOR_ABATIMENTO> = 0000000000000.00
     *
     * @var float
     */
    protected $valorAbatimento;

    /**
     * Instrução Protesto/Devolução
     * Código de Instrução de Protesto ou Devolução. Valores admissíveis:
     *
     * PROTESTAR
     * DEVOLVER
     *
     * Se informado PROTESTAR, o grupo <ENDERECO> deverá ser informado.
     *
     * @var string
     */
    protected $acao = 'DEVOLVER';

    /**
     * Número de Dias para Protesto/Devolução
     * Número de dias para o protesto ou baixa por devolução do título não pago após o vencimento. Valores admissíveis:
     *
     * PROTESTAR = 02 A 90 DIAS DEVOLVER = 00 A 999 DIAS
     * Se informado 00, será considerado D+0 perante a Data de Vencimento do Título, ou seja, o título será baixado na mesma Data do Vencimento.
     *
     * @var int
     */
    protected $numeroDias;

    /**
     * Código da Moeda
     * Código adotado pela FEBRABAN para identificar a moeda referenciada no Título. Informar fixo: ‘09’ = REAL
     *
     * @var string default 09 - REAL
     */
    protected $codigoMoeda = '09';

    /**
     * Pessoa Física (CPF).
     * @var string
     */
    protected $cpf;

    protected $nome;

    /**
     * Número de inscrição da Empresa (CNPJ)
     * @var string
     */
    protected $cnpj;
    protected $razaoSocial;

    /**
     * Endereço
     * Texto referente a localização da rua / avenida, número, complemento e bairro
     * utilizado para entrega de correspondência.
     * Se <ACAO> = PROTESTAR, deverá ser informado.
     *
     * @var string
     */
    protected $logradouro;

    /**
     * Endereço
     * Texto referente a localização da rua / avenida, número, complemento e bairro
     * utilizado para entrega de correspondência.
     * Se <ACAO> = PROTESTAR, deverá ser informado.
     *
     * @var string
     */
    protected $bairro;

    /**
     * Cidade
     * Texto referente ao nome do município componente do endereço utilizado para entrega de correspondência.
     * Se <ACAO> = PROTESTAR, deverá ser informado.
     * @var string
     */
    protected $cidade;

    /**
     * Estado / Unidade da Federação
     * Código do estado, unidade da federação componente do endereço utilizado para entrega de correspondência.
     * Se <ACAO> = PROTESTAR, deverá ser informado.
     * @var string
     */
    protected $uf;

    /**
     * CEP
     * Código adotado pelos CORREIOS para identificação de logradouros.
     * Se <ACAO> = PROTESTAR, deverá ser informado.
     * @var string
     */
    protected $cep;

    //Sacador Avalista
    protected $cpfAvalista;

    /**
     * Nome do Sacador / Avalista
     * Nome que identifica a entidade, pessoa física ou jurídica, Beneficiário original do título de cobrança.
     * Informação obrigatória quando se tratar de título negociado com terceiros.
     *
     * @var string
     */
    protected $nomeAvalista;
    protected $cnpjAvalista;
    protected $razaoSocialAvalista;

    /**
     * Data da Multa
     * Data a partir da qual a multa deverá ser cobrada. Na ausência, será considerada a data de vencimento.
     * Utilizar o formato yyyy-MM-dd, onde
     * yyyy = ano
     * MM = mês
     * dd= dia
     *
     * @var string
     */
    protected $dataDaMulta;

    /**
     * Valor
     * Valor de multa a ser aplicado sobre o valor do Título, por atraso no pagamento.
     *
     * Utilizar conforme tag utilizada:
     * <VALOR></VALOR>0000000000000.00
     *
     * @var float
     */
    protected $valorDaMulta;

    /**
     * Percentual a Ser Aplicado
     * Percentual de multa a ser aplicado sobre o valor do Título
     *
     * Utilizar conforme tag utilizada:
     * <PERCENTUAL></PERCENTUAL>0000000000.00000
     *
     * @var float
     */
    protected $percentualDaMulta;

    /**
     * Data do Desconto 1 / 2 / 3
     * Data limite do desconto do título de cobrança.
     * O Desconto 1 é aquele de maior valor e data de aplicação mais distante da Data de Vencimento,
     * enquanto o Desconto 3 é o de menor valor e mais próximo da Data de Vencimento.
     *
     * Utilizar o formato yyyy-MM-dd, onde:
     * yyyy = ano
     * MM = mês
     * dd= dia
     *
     * @var string
     */
    protected $dataDoDesconto;
    protected $valorDoDesconto;
    protected $percentualDoDesconto;

    /**
     * Valor do IOF a Ser Recolhido
     * Valor original do IOF - Imposto sobre Operações Financeiras - de um título prêmio de seguro na
     * sua data de emissão, expresso de acordo com o tipo de moeda.
     *
     * @var float
     */
    protected $valorIOF;

    /**
     * Identificação do Título na Empresa
     * Campo destinado para uso da Empresa Beneficiário para identificação do Título.
     *
     * @var string
     */
    protected $identificacaoEmpresa;

    /**
     * Mensagem Ficha Compensação
     * Texto de observações destinado ao envio de mensagens livres,
     * a serem impressas no campo instruções da Ficha de Compensação e na parte Recibo do Pagador do boleto.
     * Ocorre até 2 vezes.
     *
     * @var array
     */
    protected $mensagemFichaCompesacao = [];

    /**
     * Mensagem Recibo Pagador
     * Texto de observações destinado ao envio de mensagens livres,
     * a serem impressas na parte Recibo do Pagador do boleto. Ocorre até 4 vezes.
     *
     * @var array
     */
    protected $mensagamReciboPagador = [];

    /**
     * Quantidade de Pagamento Possíveis
     * Identificar a Quantidade de Pagamentos possíveis: de 1 a 99
     * Quando Tipo de Pagamento NÃO_ACEITA_VALOR_DIVERGENTE: sempre informar 1
     *
     * @var int
     */
    protected $quantidadePermitida;

    /**
     * Identificação do Tipo de Pagamento
     *
     * Registro para Identificação do Tipo de Pagamento. Caso não seja informado o <TIPO> no grupo <PAGAMENTO>,
     * será atribuído NÃO_ACEITA_VALOR_DIVERGENTE.
     *
     * Para Espécie do Título DIFERENTE de 31 e 32:
     *
     * ACEITA_QUALQUER_VALOR 1
     * Informar VALOR MAXIMO / VALOR MINIMO ou PERCENTUAL MAXIMO / PERCENTUAL MINIMO = 0.00
     * Informar VALOR NOMINAL DO TITULO = maior que 0.00
     *
     * ACEITA_VALORES_ENTRE_MINIMO_MAXIMO 1
     * Permite VALOR MAXIMO / PERCENTUAL MAXIMO = igual ou superior ao VALOR NOMINAL DO TITULO
     * Permite VALOR MINIMO / PERCENTUAL MINIMO = igual ou inferior ao VALOR NOMINAL DO TITULO.
     *
     * SOMENTE_VALOR_MINIMO 1
     * Permite VALOR MAXIMO / PERCENTUAL MAXIMO = 0.00
     * Permite VALOR MINIMO / PERCENTUAL MINIMO = a partir de 0.01 Informar VALOR NOMINAL DO TITULO = maior que 0.00
     * NAO_ACEITA_VALOR_DIVERGENTE 2
     * Informar VALOR MAXIMO / VALOR MINIMO ou PERCENTUAL MAXIMO / PERCENTUAL MINIMO = 0.00
     * Permite somente QUANTIDADE_PERMITIDA = 1
     *
     * Se Espécie do Título igual a 31 – Cartão de Crédito:
     *
     * ACEITA_QUALQUER_VALOR
     * Permite VALOR MAXIMO / PERCENTUAL MAXIMO = a partir de 0.00 Permite VALOR MINIMO / PERCENTUAL MINIMO = a partir de 0.01 Informar VALOR NOMINAL DO TITULO = igual ou maior que 0.00
     * Se Espécie do Título igual a 32 – Boleto de Proposta:
     *
     * ACEITA_VALORES_ENTRE_MINIMO_MAXIMO
     * Permite VALOR MAXIMO / PERCENTUAL MAXIMO = a partir de 0.00
     * Permite VALOR MINIMO / PERCENTUAL MINIMO = a partir de 0.01
     *
     * 1 Permite a alteração de Identificação do Tipo de Pagamento, exceto para NAO_ACEITA_VALOR_DIVERGENTE.
     * 2 Não permite alteração de Identificação do Tipo de Pagamento.
     * @var
     */
    protected $tipoPagamento;

    /**
     * Valor Máximo (2 casas decimais) / Valor Mínimo (2 casas decimais)
     * Identificar o Valor Máximo e Mínimo admissível para pagamento.
     * Utilizar: <VALOR></VALOR> 0000000000000.00
     *
     * Para Valor Máximo, não pode ser menor que Valor Nominal do Título.
     * @var
     */
    protected $valorMinimo;
    protected $valorMaximo;

    /**
     * Percentual Máximo do Título (5 casas decimais) / Percentual Mínimo do Título (5 casas decimais)
     * Identificar o Percentual Máximo e Mínimo admissível para pagamento. Utilizar: <PERCENTUAL></PERCENTUAL> 0000000000.00000
     * Para Percentual Máximo, não pode ser menor que Valor Nominal do Título.
     * @var
     */
    protected $percentualMinimo;
    protected $percentualMaximo;

    /**
     * Número Código de Barras
     * Número do código de barras gerado. Apresentado somente quando boleto com situação EM ABERTO na CAIXA.
     * @var string
     */
    protected $codigoDeBarras;

    /**
     * Número Linha Digitável
     * Representação da linha digitável gerada. Apresentado somente quando boleto com situação EM ABERTO na CAIXA.
     * @var string
     */
    protected $linhaDigitavel;

    /**
     * Nosso Número – Informação de saída
     * Quando não informado no registro, será apresentado o valor gerado pelo banco.
     * Caso informado no registro, será retornado o valor ‘0’.
     *
     * @var string
     */
    protected $nossoNumeroGerado;

    /**
     * Endereço da imagem do boleto
     * Endereço referente à imagem do boleto gerado pelo SIGCB.
     * Apresentado somente quando boleto com situação EM ABERTO na CAIXA.
     *
     * @var string
     */
    protected $ulrBoleto;

    protected $debug = false;
    protected $errors = [];

    public function consultarBoleto()
    {
        $caixa = new CaixaProvider();
        $response = $caixa->consulta($this);

        if (isset($response['COD_RETORNO'])) {
            if ($response['COD_RETORNO'] == 'X5') {
                $this->errors[] = [
                    'operation' => $response['OPERACAO'],
                    'message' => $response['MSG_RETORNO'],
                    'exception' => $response['EXCECAO'],
                ];
            }
        }

        return $response;
    }

    public function incluirBoleto()
    {
        $caixa = new CaixaProvider();
        $response = $caixa->incluir($this);

        if (isset($response['COD_RETORNO'])) {
            if ($response['COD_RETORNO'] == 'X5') {
                $this->errors[] = [
                    'operation' => $response['OPERACAO'],
                    'message' => $response['MSG_RETORNO'],
                    'exception' => $response['EXCECAO'],
                ];
            }
        }

        return $response;
    }

    public function alterarBoleto()
    {
        $caixa = new CaixaProvider();
        $response = $caixa->alterar($this);

        if (isset($response['COD_RETORNO'])) {
            if ($response['COD_RETORNO'] == 'X5') {
                $this->errors[] = [
                    'operation' => $response['OPERACAO'],
                    'message' => $response['MSG_RETORNO'],
                    'exception' => $response['EXCECAO'],
                ];
            }
        }

        return $response;
    }

    public function baixaBoleto()
    {
        $caixa = new CaixaProvider();
        $response = $caixa->baixa($this);

        if (isset($response['COD_RETORNO'])) {
            if ($response['COD_RETORNO'] == 'X5') {
                $this->errors[] = [
                    'operation' => $response['OPERACAO'],
                    'message' => $response['MSG_RETORNO'],
                    'exception' => $response['EXCECAO'],
                ];
            }
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @param string $codigoBanco
     */
    public function setCodigoBanco($codigoBanco)
    {
        $this->codigoBanco = $codigoBanco;
    }

    /**
     * @return int
     */
    public function getUnidade()
    {
        return $this->unidade;
    }

    /**
     * @param int $unidade
     */
    public function setUnidade($unidade)
    {
        $this->unidade = $unidade;
    }

    /**
     * @return int
     */
    public function getIdProcesso()
    {
        return $this->idProcesso;
    }

    /**
     * @param int $idProcesso
     */
    public function setIdProcesso($idProcesso)
    {
        $this->idProcesso = $idProcesso;
    }

    /**
     * @return mixed
     */
    public function getCedente()
    {
        return $this->cedente;
    }

    /**
     * @param mixed $cedente
     */
    public function setCedente($cedente)
    {
        $this->cedente = $cedente;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return mixed
     */
    public function getIndice()
    {
        return $this->indice;
    }

    /**
     * @param mixed $indice
     */
    public function setIndice($indice)
    {
        $this->indice = $indice;
    }


    /**
     * @return mixed
     */
    public function getBanco()
    {
        return $this->banco;
    }

    /**
     * @param mixed $banco
     */
    public function setBanco($banco)
    {
        $this->banco = $banco;
    }

    /**
     * @return mixed
     */
    public function getCodigoBeneficiario()
    {
        return $this->codigoBeneficiario;
    }

    /**
     * @param mixed $codigoBeneficiario
     */
    public function setCodigoBeneficiario($codigoBeneficiario)
    {
        $this->codigoBeneficiario = preg_replace('/\D/', '', $codigoBeneficiario);
    }

    /**
     * @return mixed
     */
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * Caso o BENEFICIÁRIO venha a controlar a geração do Nosso Número, deverá informá-lo no campo em questão,
     * iniciando com ‘14’; Caso contrário, se a CAIXA for gerá-lo, preencher o campo com zero (17 posicoes).
     * @param mixed $nossoNumero
     */
    public function setNossoNumero($nossoNumero)
    {
        // Se informado zeros, o nosso número será gerado pelo banco.
        // Caso contrário deverá ser informado número iniciando com 14. Exemplo: 14000000000000001.
        if (empty($nossoNumero) || (int)$nossoNumero == 0) {

            //Necessário para gerar o hash corretamente
            $this->nossoNumero = '00000000000000000';

        } elseif ((strpos($nossoNumero, '14') === false) || (strlen($nossoNumero) <> 17)) {

            $this->nossoNumero = '14' . Parser::zeroFill($nossoNumero, '15');

        } else {

            $this->nossoNumero = $nossoNumero;
        }

    }

    /**
     * @return mixed
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param mixed $numeroDocumento
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;
    }

    /**
     * @return mixed
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * @param mixed $dataVencimento
     */
    public function setDataVencimento($dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;
    }

    /**
     * @return mixed
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param mixed $valor
     */
    public function setValor($valor)
    {
        $this->valor = number_format((float)$valor, 2, '.', '');
    }

    /**
     * @return string
     */
    public function getTipoEspecie()
    {
        return $this->tipoEspecie;
    }

    /**
     * @param string $tipoEspecie
     */
    public function setTipoEspecie($tipoEspecie)
    {
        $this->tipoEspecie = $tipoEspecie;
    }

    /**
     * @return mixed
     */
    public function getFlagAceite()
    {
        return $this->flagAceite;
    }

    /**
     * @param mixed $flagAceite
     */
    public function setFlagAceite($flagAceite)
    {
        $this->flagAceite = $flagAceite;
    }

    /**
     * @return mixed
     */
    public function getDataEmissao()
    {
        return $this->dataEmissao;
    }

    /**
     * @param mixed $dataEmissao
     */
    public function setDataEmissao($dataEmissao)
    {
        $this->dataEmissao = $dataEmissao;
    }

    /**
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param string $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getJurosValor()
    {
        return $this->jurosValor;
    }

    /**
     * @param mixed $jurosValor
     */
    public function setJurosValor($jurosValor)
    {
        $this->jurosValor = $jurosValor;
    }

    /**
     * @return mixed
     */
    public function getPercentual()
    {
        return $this->percentual;
    }

    /**
     * @param mixed $percentual
     */
    public function setPercentual($percentual)
    {
        $this->percentual = $percentual;
    }

    /**
     * @return mixed
     */
    public function getValorAbatimento()
    {
        return $this->valorAbatimento;
    }

    /**
     * @param mixed $valorAbatimento
     */
    public function setValorAbatimento($valorAbatimento)
    {
        $this->valorAbatimento = $valorAbatimento;
    }

    /**
     * @return string
     */
    public function getAcao()
    {
        return $this->acao;
    }

    /**
     * @param string $acao
     */
    public function setAcao($acao)
    {
        $this->acao = $acao;
    }

    /**
     * @return mixed
     */
    public function getNumeroDias()
    {
        return $this->numeroDias;
    }

    /**
     * @param mixed $numeroDias
     */
    public function setNumeroDias($numeroDias)
    {
        $this->numeroDias = $numeroDias;
    }

    /**
     * @return string
     */
    public function getCodigoMoeda()
    {
        return $this->codigoMoeda;
    }

    /**
     * @param string $codigoMoeda
     */
    public function setCodigoMoeda($codigoMoeda)
    {
        $this->codigoMoeda = $codigoMoeda;
    }

    /**
     * @return mixed
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * @param mixed $cpf
     */
    public function setCpf($cpf)
    {
        $this->cpf = preg_replace('/\D/', '', $cpf);
    }

    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = Parser::cleanString(substr($nome, 0, 40));
    }

    /**
     * @return mixed
     */
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * @param mixed $cnpj
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = preg_replace('/\D/', '', $cnpj);
    }

    /**
     * @return mixed
     */
    public function getRazaoSocial()
    {
        return $this->razaoSocial;
    }

    /**
     * @param mixed $razaoSocial
     */
    public function setRazaoSocial($razaoSocial)
    {
        $this->razaoSocial = $razaoSocial;
    }

    /**
     * @return mixed
     */
    public function getLogradouro()
    {
        return $this->logradouro;
    }

    /**
     * @param mixed $logradouro
     */
    public function setLogradouro($logradouro)
    {
        $this->logradouro = Parser::cleanString(substr($logradouro, 0, 40));
    }

    /**
     * @return mixed
     */
    public function getBairro()
    {
        return $this->bairro;
    }

    /**
     * @param mixed $bairro
     */
    public function setBairro($bairro)
    {
        $this->bairro = Parser::cleanString(substr($bairro, 0, 15));
    }

    /**
     * @return mixed
     */
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * @param mixed $cidade
     */
    public function setCidade($cidade)
    {
        $this->cidade = Parser::cleanString(substr($cidade, 0, 15));
    }

    /**
     * @return mixed
     */
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * @param mixed $uf
     */
    public function setUf($uf)
    {
        $this->uf = Parser::cleanString(substr($uf, 0, 2));
    }

    /**
     * @return mixed
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @param mixed $cep
     */
    public function setCep($cep)
    {
        $this->cep = preg_replace('/\D/', '', $cep);
    }

    /**
     * @return mixed
     */
    public function getCpfAvalista()
    {
        return $this->cpfAvalista;
    }

    /**
     * @param mixed $cpfAvalista
     */
    public function setCpfAvalista($cpfAvalista)
    {
        $this->cpfAvalista = $cpfAvalista;
    }

    /**
     * @return mixed
     */
    public function getNomeAvalista()
    {
        return $this->nomeAvalista;
    }

    /**
     * @param mixed $nomeAvalista
     */
    public function setNomeAvalista($nomeAvalista)
    {
        $this->nomeAvalista = $nomeAvalista;
    }

    /**
     * @return mixed
     */
    public function getCnpjAvalista()
    {
        return $this->cnpjAvalista;
    }

    /**
     * @param mixed $cnpjAvalista
     */
    public function setCnpjAvalista($cnpjAvalista)
    {
        $this->cnpjAvalista = $cnpjAvalista;
    }

    /**
     * @return mixed
     */
    public function getRazaoSocialAvalista()
    {
        return $this->razaoSocialAvalista;
    }

    /**
     * @param mixed $razaoSocialAvalista
     */
    public function setRazaoSocialAvalista($razaoSocialAvalista)
    {
        $this->razaoSocialAvalista = $razaoSocialAvalista;
    }

    /**
     * @return mixed
     */
    public function getDataDaMulta()
    {
        return $this->dataDaMulta;
    }

    /**
     * @param mixed $dataDaMulta
     */
    public function setDataDaMulta($dataDaMulta)
    {
        $this->dataDaMulta = $dataDaMulta;
    }

    /**
     * @return mixed
     */
    public function getValorDaMulta()
    {
        return $this->valorDaMulta;
    }

    /**
     * @param mixed $valorDaMulta
     */
    public function setValorDaMulta($valorDaMulta)
    {
        $this->valorDaMulta = $valorDaMulta;
    }

    /**
     * @return mixed
     */
    public function getPercentualDaMulta()
    {
        return $this->percentualDaMulta;
    }

    /**
     * @param mixed $percentualDaMulta
     */
    public function setPercentualDaMulta($percentualDaMulta)
    {
        $this->percentualDaMulta = $percentualDaMulta;
    }

    /**
     * @return mixed
     */
    public function getDataDoDesconto()
    {
        return $this->dataDoDesconto;
    }

    /**
     * @param mixed $dataDoDesconto
     */
    public function setDataDoDesconto($dataDoDesconto)
    {
        $this->dataDoDesconto = $dataDoDesconto;
    }

    /**
     * @return mixed
     */
    public function getValorDoDesconto()
    {
        return $this->valorDoDesconto;
    }

    /**
     * @param mixed $valorDoDesconto
     */
    public function setValorDoDesconto($valorDoDesconto)
    {
        $this->valorDoDesconto = $valorDoDesconto;
    }

    /**
     * @return mixed
     */
    public function getPercentualDoDesconto()
    {
        return $this->percentualDoDesconto;
    }

    /**
     * @param mixed $percentualDoDesconto
     */
    public function setPercentualDoDesconto($percentualDoDesconto)
    {
        $this->percentualDoDesconto = $percentualDoDesconto;
    }

    /**
     * @return mixed
     */
    public function getValorIOF()
    {
        return $this->valorIOF;
    }

    /**
     * @param mixed $valorIOF
     */
    public function setValorIOF($valorIOF)
    {
        $this->valorIOF = $valorIOF;
    }

    /**
     * @return mixed
     */
    public function getIdentificacaoEmpresa()
    {
        return $this->identificacaoEmpresa;
    }

    /**
     * @param mixed $identificacaoEmpresa
     */
    public function setIdentificacaoEmpresa($identificacaoEmpresa)
    {
        $this->identificacaoEmpresa = $identificacaoEmpresa;
    }

    /**
     * @return mixed
     */
    public function getMensagemFichaCompesacao()
    {
        return $this->mensagemFichaCompesacao;
    }

    /**
     * @param mixed $mensagemFichaCompesacao
     */
    public function setMensagemFichaCompesacao($mensagemFichaCompesacao)
    {
        if (count($this->mensagemFichaCompesacao) <= 2) {
            $this->mensagemFichaCompesacao[] = substr(utf8_encode($mensagemFichaCompesacao), 0, 40);
        }
    }

    /**
     * @return mixed
     */
    public function getMensagamReciboPagador()
    {
        return $this->mensagamReciboPagador;
    }

    /**
     * @param mixed $mensagamReciboPagador
     */
    public function setMensagamReciboPagador($mensagamReciboPagador)
    {
        if (count($this->mensagamReciboPagador) <= 4) {
            $this->mensagamReciboPagador[] = Parser::cleanString(substr($mensagamReciboPagador, 0, 40));
        }
    }

    /**
     * @return mixed
     */
    public function getQuantidadePermitida()
    {
        return $this->quantidadePermitida;
    }

    /**
     * @param mixed $quantidadePermitida
     */
    public function setQuantidadePermitida($quantidadePermitida)
    {
        $this->quantidadePermitida = $quantidadePermitida;
    }

    /**
     * @return mixed
     */
    public function getTipoPagamento()
    {
        return $this->tipoPagamento;
    }

    /**
     * @param mixed $tipoPagamento
     */
    public function setTipoPagamento($tipoPagamento)
    {
        $this->tipoPagamento = $tipoPagamento;
    }

    /**
     * @return mixed
     */
    public function getValorMinimo()
    {
        return $this->valorMinimo;
    }

    /**
     * @param mixed $valorMinimo
     */
    public function setValorMinimo($valorMinimo)
    {
        $this->valorMinimo = $valorMinimo;
    }

    /**
     * @return mixed
     */
    public function getValorMaximo()
    {
        return $this->valorMaximo;
    }

    /**
     * @param mixed $valorMaximo
     */
    public function setValorMaximo($valorMaximo)
    {
        $this->valorMaximo = $valorMaximo;
    }

    /**
     * @return mixed
     */
    public function getPercentualMinimo()
    {
        return $this->percentualMinimo;
    }

    /**
     * @param mixed $percentualMinimo
     */
    public function setPercentualMinimo($percentualMinimo)
    {
        $this->percentualMinimo = $percentualMinimo;
    }

    /**
     * @return mixed
     */
    public function getPercentualMaximo()
    {
        return $this->percentualMaximo;
    }

    /**
     * @param mixed $percentualMaximo
     */
    public function setPercentualMaximo($percentualMaximo)
    {
        $this->percentualMaximo = $percentualMaximo;
    }

    /**
     * @return string
     */
    public function getCodigoDeBarras()
    {
        return $this->codigoDeBarras;
    }

    /**
     * @return string
     */
    public function getLinhaDigitavel()
    {
        return $this->linhaDigitavel;
    }

    /**
     * @return string
     */
    public function getNossoNumeroGerado()
    {
        return $this->nossoNumeroGerado;
    }

    /**
     * @return string
     */
    public function getUlrBoleto()
    {
        return $this->ulrBoleto;
    }

    public function setDebug($bool = false)
    {
        $this->debug = is_bool($bool) ? $bool : false;
    }

    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function setErrors($error)
    {
        $this->errors[] = $error;

        if (!empty($this->errors)) {
            throw new \RuntimeException($error);
        }
    }

}