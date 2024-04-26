<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Commissions\Model;

use Magedelight\Commissions\Api\CommissionInvoiceRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magedelight\Sales\Api\Data\CustomMessageInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CommissionInvoiceRepository implements CommissionInvoiceRepositoryInterface
{
    const COMMMISSION_INVOICE = 'commission_invoice/';

    protected $_amountBalance = [];

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \RB\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomMessageInterface
     */
    protected $customMessageInterface;

    /**
     * CommissionInvoiceRepository constructor.
     * @param ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param Commission\Pdf\Invoice $invoice
     * @param \Magedelight\Commissions\Api\Data\CommissionInvoiceInterfaceFactory $commissionInvoiceFactory
     * @param \Magedelight\Commissions\Api\Data\CommissionInvoiceItemsInterfaceFactory $commissionInvoiceItemsFactory
     * @param \Magedelight\Commissions\Api\Data\InvoiceDownloadInterfaceFactory $invoiceDownload
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CustomMessageInterface $customMessageInterface
     */
    public function __construct(
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magedelight\Commissions\Model\Commission\Pdf\Invoice $invoice,
        \Magedelight\Commissions\Api\Data\CommissionInvoiceInterfaceFactory $commissionInvoiceFactory,
        \Magedelight\Commissions\Api\Data\CommissionInvoiceItemsInterfaceFactory $commissionInvoiceItemsFactory,
        \Magedelight\Commissions\Api\Data\InvoiceDownloadInterfaceFactory $invoiceDownload,
        CollectionProcessorInterface $collectionProcessor,
        CustomMessageInterface $customMessageInterface
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->userContext = $userContext;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
        $this->_request = $request;
        $this->pdfCommission = $invoice;
        $this->commissionInvoice = $commissionInvoiceFactory;
        $this->commissionInvoiceItems = $commissionInvoiceItemsFactory;
        $this->invoiceDownload = $invoiceDownload;
        $this->collectionProcessor = $collectionProcessor;
        $this->customMessageInterface = $customMessageInterface;
    }

    /**
     * @inheritdoc
     */
    public function downloadCommissionInvoice(int $paymentId)
    {
        $invoiceDownld = $this->invoiceDownload->create();
        if ($paymentId) {
            $vendorId = $this->userContext->getUserId();
            $collection = $this->getCommissionCollection($vendorId, $paymentId);

            if (!$collection->getSize()) {
                if ($invoiceDownld instanceof CustomMessageInterface) {
                    $this->customMessageInterface->setMessage(__('There are no printable documents related to selected payment.'));
                    return $this->customMessageInterface->setStatus(false);
                }
            }
            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $isFile = false;
            $file = null;
            $fileName =  self::COMMMISSION_INVOICE .
                sprintf('commission_invoice_%s.pdf', $vendorId.'_'.$paymentId);
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $content = $this->pdfCommission->getPdf($collection)->render();
            if (is_array($content)) {
                if (!isset($content['type']) || !isset($content['value'])) {
                    throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
                }
                if ($content['type'] == 'filename') {
                    $isFile = true;
                    $file = $content['value'];
                    if (!$dir->isFile($file)) {
                        throw new \Exception((string)new \Magento\Framework\Phrase('File not found'));
                    }
                    $contentLength = $dir->stat($file)['size'];
                }
            }

            if ($content !== null) {
                $dir->writeFile($fileName, $content);
                $invoiceDownld->setStatus(__('Invoice Pdf Generation Completed.'));
                $invoiceDownld->setFilePath($mediaUrl . $fileName);
            } else {
                $invoiceDownld->setStatus(__('Invoice Pdf Generation Failed.'));
            }
        }

        return $invoiceDownld;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $vendorId = $this->userContext->getUserId();
        $store = $this->storeManager->getStore();
        $collection = $this->getCommissionCollection($vendorId);
        $totalFees = new \Zend_Db_Expr('(total_commission + marketplace_fee + cancellation_fee + service_tax)');
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $filters = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'total_fees') {
                    $collection->addFieldToFilter(
                        $totalFees,
                        [$filter->getConditionType() => $filter->getValue()]
                    );
                    continue;
                }
                if ($filter->getField() == 'paid_at' &&
                    $filter->getConditionType() == 'lteq' || $filter->getConditionType() == 'to') {
                    $filter->setValue(date('Y-m-d H:i:s', strtotime(
                        '+23 hours 59 minutes 59 seconds',
                        strtotime($filter->getValue())
                    )));
                }
                $filters[] = $filter;
            }
            $filterGroup->setFilters($filters);
        }

        $this->collectionProcessor->process($searchCriteria, $collection);

        $total = $collection->getSize();

        $hasmoreData = true;

        if ($searchCriteria->getPageSize() * $searchCriteria->getCurrentPage() >= $total) {
            $hasmoreData = false;
        }

        $result = [];

        foreach ($collection as $commissionData) {
            $commissionInv = $this->commissionInvoice->create();
            $commissionInv->setVendorPaymentId((int)$commissionData->getVendorPaymentId());
            $commissionInv->setVendorOrderId((int)$commissionData->getVendorOrderId());
            $commissionInv->setPurchaseOrderId($commissionData->getPurchaseOrderId());
            $commissionInv->setPaidAt($commissionData->getPaidAt());
            $commissionInv->setCommissionInvoiceId($commissionData->getCommissionInvoiceId());
            $commissionInv->setTotalFees($this->formatAmount($commissionData->getTotalFees(), $store));
            $result[] = $commissionInv->getData();
        }

        $commissionInvoiceItems = $this->commissionInvoiceItems->create();
        $commissionInvoiceItems->setItems($result);
        $commissionInvoiceItems->setHasMore($hasmoreData);
        $commissionInvoiceItems->setTotalItems($total);

        return $commissionInvoiceItems;
    }

    protected function formatAmount($amount, $store)
    {
        return $this->priceHelper->currencyByStore($amount, $store, true, false);
    }

    protected function getCommissionCollection($vendorId, $paymentId = null)
    {
        $collection = $this->collectionFactory->create();
        $collection->addExpressionFieldToSelect(
            'total_fees',
            '(total_commission + marketplace_fee + cancellation_fee + service_tax)',
            ['total_commission'=> 'main_table.total_commission','marketplace_fee'=>'main_table.marketplace_fee',
                'cancellation_fee'=>'main_table.cancellation_fee']
        );
        $collection->addFieldToFilter(
            'vendor_id',
            ['eq' => $vendorId]
        )->addFieldToFilter('status', ['eq' => 1]);

        if ($paymentId != null) {
            $collection->addFieldToFilter('vendor_payment_id', $paymentId);
        }

        return $collection;
    }
}
