<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Sellerhtml\Rates;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;

class Edit extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;
    
    protected $_matrixRateCollectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $matrixRateCollectionFactory
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $matrixRateCollectionFactory,
        PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_matrixRateCollectionFactory = $matrixRateCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }
        
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helper->getConfigValue('carriers/rbmatrixrate/active')) {
            $resultRedirect->setPath('rbvendor/account/dashboard', ['_secure' => true]);
            return $resultRedirect;
        }
        $matrixRateId = (int)$this->getRequest()->getParam('id');

        $collection = $this->_matrixRateCollectionFactory->create();

        $collection->addFieldToFilter('pk', ['eq'=>$matrixRateId]);

        $matrixRateCollection = $collection->getData();

        $matrixRated = '';

        foreach ($matrixRateCollection as $matrixrate) {
            $matrixRated = $matrixrate;
        }
        
        if ($this->_auth->isLoggedIn()) {
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($matrixRated)
            );
        } else {
            $response['redirectUrl'] = $this->_url->getUrl(\Magedelight\Backend\Model\Url::ROUTE_VENDOR_LOGIN);
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($response)
            );
        }
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }
}
