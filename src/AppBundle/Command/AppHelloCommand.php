<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class AppHelloCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('order:export')
            ->setDescription('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $name = $input->getArgument('name');

        // if ($input->getOption('option')) {
        //     $name .= $input->getOption('option');
        // }
        $fp = fopen(__DIR__.'/../../../var/export_order_'.date('YmdHis').'.csv', 'a+');

        // Header
        $row = array(
            "order_id", "order_datetime", "total_order_value", "average_unit_price",
            "distinct_unit_count", "total_units_count", "customer_state"
        );
        fputcsv($fp, $row);

        $file = fopen('https://s3-ap-southeast-2.amazonaws.com/catch-code-challenge/challenge-1-in.jsonl', 'r');
        while (!feof($file)) {
            $line = fgets($file);
            $row = array();
            $obj = json_decode($line);
            $avg_price = 0;
            $cust_state = $obj->customer->shipping_address->state;
            $total_order = 0;
            $total_unit = 0;
            $unique_unit = 0;
            $unit_id = 0;
            if (sizeof($obj->items) > 0) {
                foreach ($obj->items as $item) {
                    $total_order += $item->quantity * $item->unit_price;
                    $total_unit += $item->quantity;
                    if ($item->product->brand->id == $unit_id) {
                        $unique_unit += 1;
                    }
                    $unit_id = $item->product->brand->id;
                }
                $totalItem =  intval(sizeof($obj->items));

                $avg_price = $total_order / $totalItem;
            }

            if (sizeof($obj->discounts) > 0) {
                foreach ($obj->discounts as $discount) {
                    switch ($discount->type) {
                        case 'PERCENTAGE':
                            $total_order -= $discount->value / 100;
                            break;
                        default:
                            $total_order -= $discount->value;
                            break;
                    }
                }
            }

            if ($total_order != 0) {
                $export = $obj->order_id . ', "' . date("Y-m-d H:i:s", strtotime($obj->order_date)) . '", ' . $total_order . ', '  . $avg_price . ', ' . $unique_unit . ', ' . $total_unit . ', "' . ucwords(strtolower($cust_state)) . '"';
                $output->writeln($export);
                $row[] = $obj->order_id;
                $row[] = date("Y-m-d H:i:s", strtotime($obj->order_date));
                $row[] = number_format($total_order, 2, '.', '');
                $row[] = number_format($avg_price, 2, '.', '');
                $row[] = $unique_unit;
                $row[] = $total_unit;
                $row[] = ucwords(strtolower($cust_state));
                fputcsv($fp, $row);
            }
        }
        rewind($fp);
        fclose($fp);
    }
}
