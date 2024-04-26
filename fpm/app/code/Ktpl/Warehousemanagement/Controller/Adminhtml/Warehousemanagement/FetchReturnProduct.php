<?php
/**
 * A Magento 2 module that functions for Warehouse management
 * Copyright (C) 2019
 *
 * This file included in Ktpl/Warehousemanagement is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Ktpl\Warehousemanagement\Controller\Adminhtml\Warehousemanagement;

class FetchReturnProduct extends \Magento\Framework\App\Action\Action
{

    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Ktpl\BarcodeGenerator\Model\Barcode $barcode,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->barcode = $barcode;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->remoteAddress = $remoteAddress;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * New action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();

        $barcodeNumber =  $this->getRequest()->getParam('scannedbarcode');
        $deliverytype =  $this->getRequest()->getParam('deliverytype');
        $returnType =  $this->getRequest()->getParam('returntype');
        $lineItemData = $this->barcode->getItemDataFromBarcode($barcodeNumber);
        $visitoreIp = $this->remoteAddress->getRemoteAddress();
        $userId = $this->authSession->getUser()->getId();

        $block = $resultPage->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Ktpl_Warehousemanagement::customhtml/renderreturnraw.phtml')
                ->setData('data',$lineItemData)
                ->setData('deliveryType',$deliverytype)
                ->setData('returnType',$returnType)
                ->setData('barcodeNumber',$barcodeNumber)
                ->setData('visitoreIp',$visitoreIp)
                ->setData('userId',$userId)
                ->toHtml();

        $resultJson->setData(['output' => $block]);

        return $resultJson;
    }
}
