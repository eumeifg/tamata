<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\PrintHtml\Controller\Adminhtml\Index;

use Ktpl\Tookan\Model\ResourceModel\OrderExport\Grid\Collection;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Framework\App\Action\Action
{

	/**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $request;

    protected $shipmentRepository;

    protected $storeManager;

    protected $_scopeConfig;

    protected $vendorOrderRepository;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Ktpl\BarcodeGenerator\Helper\Data $barcodeGenerator,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->shipmentRepository = $shipmentRepository;
        $this->barcodeGenerator = $barcodeGenerator;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context);
    }

    public function execute()
    {
    	$resultPage = $this->_resultPageFactory->create();
    	$shipmentId =  $this->request->getParam('shipment_id');

        /*....To set shipment collection object....*/
    	$shipment = $this->shipmentRepository->get($shipmentId);

        /*....To set Order collection object....*/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($shipment->getOrder()->getIncrementId());

        /*....To set barcode image....*/
    	if ($shipment) {
            $orderIncrementId = $shipment->getOrder()->getIncrementId();
            /*....Barcode number format prints from here....*/
            $subOrderId = Collection::ORDER_ID_PREFIX .$shipment->getIncrementId()."|".$orderIncrementId."-".$shipment->getVendorOrderId();

            /* Generate barcode based on shipment increment ID MAGE0913 START */
            $shipmentIncrementId = $shipment->getIncrementId();
            $code = [];
            $code['shipment_id'] = $shipmentIncrementId;
            $code['suborder_id'] = $subOrderId;
            $imageData = $this->barcodeGenerator->generateBarcode($code);
            //$imageData = $this->barcodeGenerator->generateBarcode($subOrderId);
            /* Generate barcode based on shipment increment ID MAGE0913 END */

            $barcodeImage = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath("$subOrderId.png");
            $this->barcodeGenerator->createImage($barcodeImage, $imageData);
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $barcodeImageUrl = $mediaUrl.$subOrderId.".png";
        }

        /*....To set invoice logo....*/
        $store = null;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $imagePath = '/sales/store/logo/' . $image;
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if ($image) {
	        $invoiceLogoPath = $mediaUrl.$imagePath;
        }

        /*....To set vendorOrder collection....*/
        $vendorOrderId = $shipment->getVendorOrderId();
        $vendorId      = $shipment->getVendorId();
        $vendorOrder=$this->vendorOrderRepository->getById($vendorOrderId);

    	$block = $resultPage->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Ktpl_PrintHtml::shipment.phtml')
                ->setData('shipmentData',$shipment)
                ->setData('orderData',$order)
                ->setData('vendorOrder',$vendorOrder)
                ->setData('barcodeimageUrl',$barcodeImageUrl)
                ->setData('invoiceLogo',$invoiceLogoPath)
                ->toHtml();
                echo $block;die;
    }
}
