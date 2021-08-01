<?php

namespace AppBundle\Services;

class ExportXmlService
{
    static function build($arr)
    {
        // CREATING XML OBJECT

        // "order_id", "order_datetime", "total_order_value", "average_unit_price",
        // "distinct_unit_count", "total_units_count", "customer_state";
        $xml = "<export_order>";

        foreach ($arr as $data) {
            $xml .= '<order order_id="'.$data[0].'">';
            $xml .= '<order_datetime>' . $data[1] . '</order_datetime>';
            $xml .= '<total_order_value>' . $data[2] . '</total_order_value>';
            $xml .= '<average_unit_price>' . $data[3] . '</average_unit_price>';
            $xml .= '<distinct_unit_count>' . $data[4] . '</distinct_unit_count>';
            $xml .= '<total_units_count>' . $data[5] . '</total_units_count>';
            $xml .= '<customer_state>' . $data[6] . '</customer_state>';
            $xml .= '</order>';
        }
        $xml .= "</export_order>";

        $sxe = new \SimpleXMLElement($xml);
        $dom = new \DOMDocument('1,0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($sxe->asXML());

        echo $dom->saveXML();

        $dom->save(__DIR__ . '/../../../var/export/xml/export_order_' . date('YmdHis') . '.xml');
    }
}
