<?php

namespace Caixa\Helpers;

class XMLParser
{
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
