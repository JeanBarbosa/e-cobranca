<?php

namespace Caixa\Helpers;

class Parser
{
    /**
     * Recebe o array de dados e faz a geração do XML conforme layout da CAIXA
     * será armazenado em $this->dadosXml para envio posterior
     *
     * @param array $arrayDados
     * @param $tipo
     * @return string
     */
    public static function fromXml(array $arrayDados, $tipo)
    {
        $xml_root = 'soapenv:Envelope';
        $xml = new XmlDomConstruct('1.0', 'utf-8');
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xml->convertArrayToXml(array($xml_root => $arrayDados));
        $xml_root_item = $xml->getElementsByTagName($xml_root)->item(0);
        $xml_root_item->setAttribute(
            'xmlns:soapenv',
            'http://schemas.xmlsoap.org/soap/envelope/'
        );

        if ($tipo == 'CONSULTA_BOLETO') {
            $xml_root_item->setAttribute(
                'xmlns:consultacobrancabancaria',
                'http://caixa.gov.br/sibar/consulta_cobranca_bancaria/boleto'
            );
        } else {
            $xml_root_item->setAttribute(
                'xmlns:manutencaocobrancabancaria',
                'http://caixa.gov.br/sibar/manutencao_cobranca_bancaria/boleto/externo'
            );
        }

        $xml_root_item->setAttribute(
            'xmlns:sibar_base',
            'http://caixa.gov.br/sibar'
        );

        return $xml->saveXML();
    }
    
    public static function fromArray($xmlString)
    {
        $p = xml_parser_create();
        xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($p, $xmlString, $vals, $index);
        xml_parser_free($p);

        return self::assocValuesForTags($vals);
    }

    private static function assocValuesForTags($values)
    {
        $nodes = [];

        foreach ($values as $key => $value)
        {
            if (isset($value['value']))
            {
                $nodes[$value['tag']] = $value['value'];
            }
        }

        return $nodes;
    }

    /**
     * Formata string de acordo com o requerido pelo webservice
     *
     * @see https://stackoverflow.com/a/3373364/513401
     */
    public static function cleanString($str)
    {
        $replaces = array(
            'S' => 'S', 's' => 's', 'Z' => 'Z', 'z' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y'
        );

        return preg_replace('/[^0-9A-Za-z;,.\- ]/', '', strtoupper(strtr(trim($str), $replaces)));
    }

}
