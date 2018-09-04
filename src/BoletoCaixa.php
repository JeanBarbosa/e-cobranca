<?php

namespace Caixa;

use Caixa\Client\CaixaProvider;

class BoletoCaixa
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

    protected $numeroDocumento;
    protected $dataVencimento;
    protected $valor;
    protected $tipoEspecie = '99';
    protected $flagAceite = 'S';
    protected $dataEmissao;

    //Juros Moura
    protected $tipo = 'ISENTO';
    protected $data;
    protected $jurosValor = 0;
    protected $percentual;

    protected $valorAbatimento;

    //Pos vencimento
    protected $acao = 'DEVOLVER';
    protected $numeroDias;

    protected $codigoMoeda = '09';

    //pagador
    protected $cpf;
    protected $nome;
    protected $cnpj;
    protected $razaoSocial;

    //Endereço
    protected $logradouro;
    protected $bairro;
    protected $cidade;
    protected $uf;
    protected $cep;

    //Sacador Avalista
    protected $cpfAvalista;
    protected $nomeAvalista;
    protected $cnpjAvalista;
    protected $razaoSocialAvalista;

    //Multa
    protected $dataDaMulta;
    protected $valorDaMulta;
    protected $percentualDaMulta;

    //Descontos
    protected $dataDoDesconto;
    protected $valorDoDesconto;
    protected $percentualDoDesconto;

    protected $valorIOF;
    protected $identificacaoEmpresa;

    //Ficha de compesação
    protected $mensagemFichaCompesacao;

    //Recibo Pagador
    protected $mensagamReciboPagador;

    //Pagamento
    protected $quantidadePermitida;
    protected $tipoPagamento;
    protected $valorMinimo;
    protected $valorMaximo;
    protected $percentualMinimo;
    protected $percentualMaximo;

    //Dados de retorno em caso de sucesso (operacao incluir boleto)
    protected $codigoDeBarras;
    protected $linhaDigitavel;
    protected $ulrBoleto;

    protected $errors = [];

    public function consultarBoleto()
    {
        $caixa = new CaixaProvider();
        $caixa->consulta($this);
    }

    public function incluirBoleto()
    {
        $caixa = new CaixaProvider();
        $caixa->incluir($this);
    }

    public function alterarBoleto()
    {
        //TODO alterar boleto
    }

    public function baixaBoleto()
    {
        //TODO baixa boleto
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
        $this->codigoBeneficiario = $codigoBeneficiario;
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

        } elseif (strlen($nossoNumero) <> 17) {

            $this->setErrors('Cod: 0002 - NOSSO NUMERO INVALIDO - 17 POSICOES');

        } elseif (strpos($nossoNumero, '14') === false) {

            $this->setErrors('Cod: 0002 - NOSSO NUMERO INVALIDO - INICIAR COM DIGITO 14');

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
        $this->valor = $valor;
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
        $this->cpf = $cpf;
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
        $this->nome = $nome;
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
        $this->cnpj = $cnpj;
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
        $this->logradouro = $logradouro;
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
        $this->bairro = $bairro;
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
        $this->cidade = $cidade;
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
        $this->uf = $uf;
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
        $this->cep = $cep;
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
        $this->mensagemFichaCompesacao = $mensagemFichaCompesacao;
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
        $this->mensagamReciboPagador = $mensagamReciboPagador;
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