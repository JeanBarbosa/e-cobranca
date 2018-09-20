<?php

namespace Caixa\Helpers2;

class XmlParser
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

}
