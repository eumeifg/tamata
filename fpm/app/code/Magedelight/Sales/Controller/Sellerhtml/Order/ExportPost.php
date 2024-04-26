<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Description of exportPost
 *
 * @author Rocket Bazaar Core Team
 */
class ExportPost extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Export action from import/export vendor order transaction
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $flag = 0;
        if ($this->getRequest()->getParam('sfrm') == "new") {
            $filename = "tobe_confirmed_orders.csv";
            $orderstatus = "pending";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order::class);
            $collection = $block->getNewOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "confirmed") {
            $filename = "new_orders.csv";
            $orderstatus = "processing";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Confirmedorder::class);
            $collection = $block->getConfirmOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "packed") {
            $filename = "packed_orders.csv";
            $orderstatus = "packed";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Pack::class);
            $collection = $block->getPackedOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "handover") {
            $filename = "handover_orders.csv";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Handover::class);
            $collection = $block->getHandoverOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "intransit") {
            $filename = "in_transit_orders.csv";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Intransit::class);
            $collection = $block->getIntransitOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "complete") {
            $filename = "complete_orders.csv";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Complete::class);
            $collection = $block->getCompleteOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "cancelled") {
            $filename = "cancelled_orders.csv";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Cancelorder::class);
            $collection = $block->getOrders();
            $flag = 1;
        } else {
            $filename = "closed_orders.csv";
            $block = $this->layoutFactory->create()
                ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Closed::class);
            $collection = $block->getClosedOrders();
            $flag = 2;
        }
        /** start csv content and set template */
        if ($flag == 1) {
            $headers = new \Magento\Framework\DataObject(
                [
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship To Name'),
                'bill_to_name' => __('Bill To Name'),
                'rvp_cancellation_fee' => __('Cancellation Fee'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                ]
            );
            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{bill_to_name}}","{{rvp_cancellation_fee}}","{{rvo_grand_total}}","{{status}}"';
            $content = $headers->toString($template);
            $content .= "\n";

            while ($transaction = $collection->fetchItem()) {
                $get_date = explode(",", $transaction->toString($template));
                $nblock = $this->layoutFactory->create()
                    ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order::class);
                $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim($get_date[1], '"'));
                $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                $rvo_increment_id = $get_date[0];
                $ship_to_name = $get_date[2];
                $bill_to_name = $get_date[3];
                $rvp_cancellation_fee = $get_date[4];
                $rvo_grand_total = $get_date[5];
                $status = $get_date[6];
                $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . "," .
                    $bill_to_name . "," . $rvp_cancellation_fee . "," . $rvo_grand_total . "," . $status;
                $content .= $new_string . "\n";
            }
        } elseif ($flag == 2) {
            $headers = new \Magento\Framework\DataObject(
                [
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship To Name'),
                'bill_to_name' => __('Bill To Name'),
                'total_refunded' => __('Total Refunded'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                ]
            );
            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{bill_to_name}}","{{total_refunded}}","{{rvo_grand_total}}","{{status}}"';
            $content = $headers->toString($template);
            $content .= "\n";

            while ($transaction = $collection->fetchItem()) {
                $get_date = explode(",", $transaction->toString($template));
                $nblock = $this->layoutFactory->create()
                    ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order::class);
                $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim($get_date[1], '"'));
                $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                $rvo_increment_id = $get_date[0];
                $ship_to_name = $get_date[2];
                $bill_to_name = $get_date[3];
                $total_refunded = $get_date[4];
                $rvo_grand_total = $get_date[5];
                $status = $get_date[6];
                $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . "," .
                    $bill_to_name . "," . $total_refunded . "," . $rvo_grand_total . "," . $status;
                $content .= $new_string . "\n";
            }
        } else {
            $headers = new \Magento\Framework\DataObject(
                [
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship To Name'),
                'bill_to_name' => __('Bill To Name'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                ]
            );
            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{bill_to_name}}","{{rvo_grand_total}}","{{status}}"';
            $content = $headers->toString($template);

            $content .= "\n";

            if ($collection) {
                while ($transaction = $collection->fetchItem()) {
                    $get_date = explode(",", $transaction->toString($template));
                    $nblock = $this->layoutFactory->create()
                        ->createBlock(\Magedelight\Sales\Block\Sellerhtml\Vendor\Order::class);
                    $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim($get_date[1], '"'));
                    $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                    $rvo_increment_id = $get_date[0];
                    $ship_to_name = $get_date[2];
                    $bill_to_name = $get_date[3];
                    $rvo_grand_total = $get_date[4];
                    $status = $get_date[5];

                    $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . "," .
                        $bill_to_name . "," . $rvo_grand_total . "," . $status;
                    $content .= $new_string . "\n";
                }
            }
        }

        return $this->fileFactory->create(
            $filename,
            $content,
            DirectoryList::VAR_DIR
        );
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
