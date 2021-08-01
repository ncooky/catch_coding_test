<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Services\ExportCsvService;
use AppBundle\Services\ExportXmlService;


class AppHelloCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('order:export')
            ->setDescription('export an order data from cloud to csv formatted');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orders = array();
        $file = fopen('https://s3-ap-southeast-2.amazonaws.com/catch-code-challenge/challenge-1-in.jsonl', 'r');
        while (!feof($file)) {
            $line = fgets($file);
            $row = array();
            $obj = json_decode($line);
            $cust_state = $obj->customer->shipping_address->state;
            $avg_price = $total_order = $total_unit =  $unique_unit = $unit_id = 0;

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
                $row[] = $obj->order_id;
                $row[] = date("Y-m-d H:i:s", strtotime($obj->order_date));
                $row[] = number_format($total_order, 2, '.', '');
                $row[] = number_format($avg_price, 2, '.', '');
                $row[] = $unique_unit;
                $row[] = $total_unit;
                $row[] = ucwords(strtolower($cust_state));
                array_push($orders, $row);
            }
        }

        ExportCsvService::build($orders);
        ExportXmlService::build($orders);
    }
}
