<?php

namespace AppBundle\Services;

class ExportCsvService
{
    static function build($arr)
    {
        $fp = fopen(__DIR__ . '/../../../var/export/csv/export_order_' . date('YmdHis') . '.csv', 'a+');

        // Header
        $row = array(
            "order_id", "order_datetime", "total_order_value", "average_unit_price",
            "distinct_unit_count", "total_units_count", "customer_state"
        );
        fputcsv($fp, $row);
        
        foreach ($arr as $data) {
            $row = array();
            $row[] = $data[0];
            $row[] = $data[1];
            $row[] = $data[2];
            $row[] = $data[3];
            $row[] = $data[4];
            $row[] = $data[5];
            $row[] = $data[6];

            fputcsv($fp, $row);
        }

        rewind($fp);
        fclose($fp);
    }
}
