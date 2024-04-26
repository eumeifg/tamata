<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Sellerhtml\Transaction;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of exportPost
 *
 * @author Rocket Bazaar Core Team
 */
class ExportPost extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magedelight\Commissions\Model\Source\PaymentStatus
     */
    protected $_paymentStatus;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->_paymentStatus = $paymentStatus;
        $this->_date = $date;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Export action from import/export vendor order transaction
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        /** start csv content and set template */
        $headers = new \Magento\Framework\DataObject(
            [
            'increment_id' => __('Order No'),
            'created_at' => __('Created Date'),
            'grand_total' => __('Order Value'),
            'shipping_amount' => __('Shipping Charges'),
            'total_refunded' => __('Total Refunded'),
            'total_commission' => __('Commission'),
            'marketplace_fee' => __('Marketplace Fee'),
            'cancellation_fee' => __('Cancellation Fee'),
            'service_tax' => __('Service Tax'),
            'total_amount' => __('Net Payable'),
            'status' => __('Status'),
            ]
        );
        $template = '"{{increment_id}}","{{created_at}}","{{grand_total}}","{{shipping_amount}}","{{total_refunded}}",
        "{{total_commission}}","{{marketplace_fee}}"' .
            ',"{{cancellation_fee}}","{{service_tax}}","{{total_amount}}","{{status}}"';
        $content = $headers->toString($template);

        unset($store);
        $content .= "\n";
        /*
          $collection = $this->_objectManager->create(
          'Magedelight\Commissions\Model\ResourceModel\Commission\Payment\Collection'
          );
          $collection->getSelect()->join(['rvo' => 'md_vendor_order'],
         "main_table.vendor_order_id = rvo.vendor_order_id",['increment_id','vendor_order_id','grand_total']);
          $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $this->_auth->getUser()->getVendorId()]);
         */
        $collection = $this->getTransactionSummary();
        while ($transaction = $collection->fetchItem()) {
            $transaction->setStatus($this->_paymentStatus->getOptionText($transaction->getStatus()));
            $content .= $transaction->toString($template) . "\n";
        }
        return $this->fileFactory->create('transactions_summary.csv', $content, DirectoryList::VAR_DIR);
    }

    public function getTransactionSummary()
    {
        $collection = $this->_collectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            "main_table.vendor_order_id = rvo.vendor_order_id",
            ['increment_id', 'vendor_order_id', 'grand_total', 'total_refunded', 'shipping_amount']
        );

        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $this->_auth->getUser()->getVendorId()]);

        if ($q = $this->getRequest()->getParam('q', false)) {

            $collection->addFieldToFilter(
                ['commission_invoice_id', 'purchase_order_id'],
                [
                ['like' => '%' . $q . '%'],
                ['like' => '%' . $q . '%']
                ]
            );
        }

        $data = $this->getRequest()->getParams();

        if (!empty($data)) {
            if ($this->getRequest()->getParam('commission')['from']) {
                $collection->addFieldToFilter(
                    'total_commission',
                    ['gteq' => $this->getRequest()->getParam('commission')['from']]
                );
            }
            if ($this->getRequest()->getParam('commission')['to']) {
                $collection->addFieldToFilter(
                    'total_commission',
                    ['lteq' => $this->getRequest()->getParam('commission')['to']]
                );
            }
            if ($this->getRequest()->getParam('marketplace_fee')['from']) {
                $collection->addFieldToFilter(
                    'marketplace_fee',
                    ['gteq' => $this->getRequest()->getParam('marketplace_fee')['from']]
                );
            }
            if ($this->getRequest()->getParam('marketplace_fee')['to']) {
                $collection->addFieldToFilter(
                    'marketplace_fee',
                    ['lteq' => $this->getRequest()->getParam('marketplace_fee')['to']]
                );
            }
            if ($this->getRequest()->getParam('cancellation_fee')['from']) {
                $collection->addFieldToFilter(
                    'cancellation_fee',
                    ['gteq' => $this->getRequest()->getParam('cancellation_fee')['from']]
                );
            }
            if ($this->getRequest()->getParam('cancellation_fee')['to']) {
                $collection->addFieldToFilter(
                    'cancellation_fee',
                    ['lteq' => $this->getRequest()->getParam('cancellation_fee')['to']]
                );
            }
            if ($this->getRequest()->getParam('service_tax')['from']) {
                $collection->addFieldToFilter(
                    'service_tax',
                    ['gteq' => $this->getRequest()->getParam('service_tax')['from']]
                );
            }
            if ($this->getRequest()->getParam('service_tax')['to']) {
                $collection->addFieldToFilter(
                    'service_tax',
                    ['lteq' => $this->getRequest()->getParam('service_tax')['to']]
                );
            }
            if ($this->getRequest()->getParam('total_amount')['from']) {
                $collection->addFieldToFilter(
                    'total_amount',
                    ['gteq' => $this->getRequest()->getParam('total_amount')['from']]
                );
            }
            if ($this->getRequest()->getParam('total_amount')['to']) {
                $collection->addFieldToFilter(
                    'total_amount',
                    ['lteq' => $this->getRequest()->getParam('total_amount')['to']]
                );
            }
            if ($this->getRequest()->getParam('created_at')['from']) {
                $dateFrom = $this->_date->gmtDate(null, $this->getRequest()->getParam('created_at')['from']);
                $collection->addFieldToFilter(
                    'main_table.created_at',
                    ['gteq' => $dateFrom]
                );
            }
            if ($this->getRequest()->getParam('created_at')['to']) {
                $dateTo = $this->_date->gmtDate(null, $this->getRequest()->getParam('created_at')['to']);
                $collection->addFieldToFilter(
                    'main_table.created_at',
                    ['lteq' => $dateTo]
                );
            }
            if ($this->getRequest()->getParam('order_number', false)) {
                $orderId = $this->getRequest()->getParam('order_number');
                $collection->addFieldToFilter(
                    'purchase_order_id',
                    ['like' => '%' . trim($orderId) . '%']
                );
            }
            if (!($this->getRequest()->getParam('status') === null) &&
                $this->getRequest()->getParam('status') != '') {
                $status = $this->getRequest()->getParam('status');
                $collection->addFieldToFilter(
                    'main_table.status',
                    ['eq' => $status]
                );
            }
        }

        $sortOrder = $this->getRequest()->getParam('sort_order', 'created_at');
        $direction = $this->getRequest()->getParam('dir', 'DESC');
        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);

        return $collection;
    }

    protected function _addSortOrderToCollection($collection, $sortOrder, $direction)
    {
        $collection->getSelect()->order($sortOrder . ' ' . $direction);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::financial');
    }
}
