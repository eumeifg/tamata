<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\PrintHtml\Controller\Adminhtml\Invoice;

use Ktpl\Tookan\Model\ResourceModel\OrderExport\Grid\Collection;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Framework\App\Action\Action
{

	/**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $request;

    protected $_scopeConfig;

    protected $storeManager;

    protected $information;

    protected $invoiceRepository;

    protected $timezone;

    protected $vendorOrderFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Information $information,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->information = $information;
        $this->invoiceRepository = $invoiceRepository;
        $this->timezone = $timezone;
        $this->vendorOrderFactory = $vendorOrderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
    	$resultPage = $this->_resultPageFactory->create();
    	$invoiceId =  $this->request->getParam('invoice_id');
        //echo $invoiceId;die;

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

        /*....TO set store address on shipment pdf....*/
        $storeData = $this->information->getStoreInformationObject($this->storeManager->getStore('admin'));

        /*....To set shipment collection object....*/
        $invoiceData = $this->invoiceRepository->get($invoiceId);
        $orderDate = $this->timezone->date(new \DateTime($invoiceData->getOrder()->getCreatedAt()))->format('d-m-Y');

        /*....To set Order collection object....*/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($invoiceData->getOrder()->getIncrementId());

        /*....To set vendorOrder collection....*/
        $vendorOrderId = $invoiceData->getOrder()->getId();
        $vendorId      = $invoiceData->getVendorId();
        $vendorOrder=$this->vendorOrderFactory->create()->getByOriginOrderId($vendorOrderId, $vendorId);

        /*....To get baseurl of store....*/
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $baseUrl = parse_url($baseUrl);
        $baseUrl = $baseUrl['host'];

        $block = $resultPage->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Ktpl_PrintHtml::invoice.phtml')
                ->setData('invoicelogo',$invoiceLogoPath)
                ->setData('storeaddressdata',$storeData)
                ->setData('invoice',$invoiceData)
                ->setData('orderdate',$orderDate)
                ->setData('orderData',$order)
                ->setData('vendorOrder',$vendorOrder)
                ->setData('baseurl',$baseUrl)
                ->toHtml();
                echo $block;die;
    }
}
