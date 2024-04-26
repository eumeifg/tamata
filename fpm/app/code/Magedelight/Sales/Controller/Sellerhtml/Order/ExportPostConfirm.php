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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of exportPost
 *
 * @author Rocket Bazaar Core Team
 */
class ExportPostConfirm extends \Magedelight\Backend\App\Action
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
        if ($this->getRequest()->getParam('tab') == "2,0") {
            $filename = "active_confirm_orders.csv";
            $orderstatus = "pending";
            $block = $this->layoutFactory->create()->createBlock(\Magedelight\Sales\Block\Vendor\Order::class);
            $flag = 1;
            $collection = $block->getConfirmOrders();
        } else {
            $filename = "orders.csv";
            $orderstatus = "complete";
            $block = $this->layoutFactory->create()->createBlock(\Magedelight\Sales\Block\Vendor\Order\Complete::class);
            $collection = $block->getOrders();
        }
        /** start csv content and set template */
        if ($flag == "1") {
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

            unset($store);
            $content .= "\n";

            while ($transaction = $collection->fetchItem()) {
                $get_date = explode(",", $transaction->toString($template));
                $nblock = $this->layoutFactory->create()
                    ->createBlock(\Magedelight\Sales\Block\Vendor\Order::class);
                $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim(
                    $get_date[1],
                    '"'
                ));
                $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                $rvo_increment_id = $get_date[0];
                $ship_to_name = $get_date[2];
                $bill_to_name = $get_date[3];
                $rvo_grand_total = $get_date[4];
                $status = $get_date[5];

                $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . ","
                    . $bill_to_name . "," . $rvo_grand_total . "," . $status;
                /*echo $nblock->getCurrentLocaleDate($get_date[1]);*/

                $content .= $new_string . "\n";
                /*$content .= $transaction->toString($template) . "\n";*/
            }
        } else {
            $headers = new \Magento\Framework\DataObject(
                [
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship to Name'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                ]
            );
            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{rvo_grand_total}}","{{status}}"';
            $content = $headers->toString($template);

            unset($store);
            $content .= "\n";

            while ($transaction = $collection->fetchItem()) {
                $get_date = explode(",", $transaction->toString($template));
                $nblock = $this->layoutFactory->create()
                    ->createBlock(\Magedelight\Sales\Block\Vendor\Order::class);
                $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim(
                    $get_date[1],
                    '"'
                ));
                $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                $rvo_increment_id = $get_date[0];
                $ship_to_name = $get_date[2];
                $rvo_grand_total = $get_date[3];
                $status = $get_date[4];

                $new_string = $rvo_increment_id . "," . $rvo_created_at . ","
                    . $ship_to_name . "," . $rvo_grand_total . "," . $status;
                /*echo $nblock->getCurrentLocaleDate($get_date[1]);*/

                $content .= $new_string . "\n";
                /*$content .= $transaction->toString($template) . "\n";*/
            }
        }

        return $this->fileFactory->create(
            $filename,
            $content,
            DirectoryList::VAR_DIR
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
