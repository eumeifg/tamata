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
namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\InvoiceBuilderInterface;
use Magedelight\Sales\Api\Data\InvoiceDataInterfaceFactory;
use Magedelight\Sales\Api\Data\CustomMessageInterface;
use Magedelight\Sales\Model\Order\InvoiceLoader;
use Magento\Authorization\Model\UserContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magedelight\Sales\Api\OrderRepositoryInterface as VendorOrderRepository;

class InvoiceBuilder implements InvoiceBuilderInterface
{
    CONST INVOICES_PATH = 'invoices/';

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var CustomMessageInterface
     */
    protected $customMessageInterface;

    /**
     * @var InvoiceDataInterfaceFactory
     */
    protected $invoiceDataInterfaceFactory;

    /**
     * @var InvoiceLoader
     */
    protected $invoiceLoader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var VendorOrderRepository
     */
    protected $vendorOrderRepository;

    /**
     * InvoiceBuilder constructor.
     * @param CustomMessageInterface $customMessageInterface
     * @param InvoiceDataInterfaceFactory $invoiceDataInterfaceFactory
     * @param InvoiceLoader $invoiceLoader
     * @param LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice
     * @param Sales\Order\Pdf\Invoice $pdfInvoice
     * @param \Magedelight\Sales\Api\Data\FileDownloadInterfaceFactory $fileDownloadFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomMessageInterface $customMessageInterface,
        InvoiceDataInterfaceFactory $invoiceDataInterfaceFactory,
        InvoiceLoader $invoiceLoader,
        LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice,
        \Magedelight\Sales\Model\Sales\Order\Pdf\Invoice $pdfInvoice,
        \Magedelight\Sales\Api\Data\FileDownloadInterfaceFactory $fileDownloadFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        VendorOrderRepository $vendorOrderRepository
    ) {
        $this->customMessageInterface = $customMessageInterface;
        $this->invoiceDataInterfaceFactory = $invoiceDataInterfaceFactory;
        $this->invoiceLoader = $invoiceLoader;
        $this->logger = $logger;
        $this->_invoice = $invoice;
        $this->pdfInvoice = $pdfInvoice;
        $this->fileDownloadFactory = $fileDownloadFactory;
        $this->dateTime = $dateTime;
        $this->_filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->vendorOrderRepository = $vendorOrderRepository;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createInvoiceFormData($orderId, $vendorOrderId)
    {
        try {
            $this->invoiceLoader->setOrderId($orderId);
            $invoice = $this->invoiceLoader->load($vendorOrderId);

            if ($invoice instanceof CustomMessageInterface) {
                $this->customMessageInterface->setMessage($invoice->getMessage());
                return $this->customMessageInterface->setStatus($invoice->getStatus());
            } else {
                $items = [];
                $invoiceData = $this->invoiceDataInterfaceFactory->create();
                foreach ($invoice->getItems() as $item) {
                    $items[] = $item;
                }
                $invoiceData->setItems($items);
                $invoiceData->setOrder($invoice->getOrder());
                return $invoiceData;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->customMessageInterface->setMessage(__('Invoice cannot be generated.'));
            return $this->customMessageInterface->setStatus(false);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function printInvoice($vendorOrderId)
    {
        $fileDownload = $this->fileDownloadFactory->create();
        try{
            $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
        }catch (\Exception $exception){
            $fileDownload->setStatus(__('Specified order does not exists.'));
        }
        $collection = $this->_invoice->create();
        $collection->addFieldToFilter('vendor_order_id', ['in' => $vendorOrderId]);

        if ($collection->count() > 0) {
            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $file = null;
            $fileName = self::INVOICES_PATH.'invoice_' . $vendorOrder->getIncrementId() . '.pdf';
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $content = $this->pdfInvoice->getPdf($collection)->render();
            if (is_array($content)) {
                if (!isset($content['type']) || !isset($content['value'])) {
                    throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
                }
                if ($content['type'] == 'filename') {
                    $file = $content['value'];
                    if (!$dir->isFile($file)) {
                        throw new \Exception((string)new \Magento\Framework\Phrase('File not found'));
                    }
                }
            }
            if ($content !== null) {
                $dir->writeFile($fileName, $content);
                $fileDownload->setStatus(__('Invoice Generation Completed.'));
                $fileDownload->setFilePath($mediaUrl.$fileName);
            } else {
                $fileDownload->setStatus(__('Invoice Generation Failed.'));
            }
        } else {
             $fileDownload->setStatus(__('Invoice Generation Failed.'));
        }
        
        return $fileDownload;
    }
}
