<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Sellerhtml\Microsite;

/**
 * Description of Microsite
 *
 * @author Rocket Bazaar Core Team
 */
class Form extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    protected $_micrositeCollectionFactory;
    
    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $micrositeHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     *
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Vendor\Helper\Data $helperData
     * @param \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory $micrositeCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory $micrositeCollectionFactory,
        array $data = []
    ) {
        $this->micrositeHelper = $micrositeHelper;
        $this->_micrositeCollectionFactory = $micrositeCollectionFactory;
        $this->authSession = $authSession;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }
    
    public function getMicrositeInfo()
    {
        $vendorId = $this->authSession->getUser()->getVendorId();

        $collection = $this->_micrositeCollectionFactory->create();

        $collection->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        $storeId = $this->storeManager->getStore()->getId();
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        if (!empty($collection->getData())) {
            return $collection->getData()[0];
        }
        return [];
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl("rbvendor/microsite/save");
    }

    /**
     * @param string $banner
     * @param string $subPath
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMicrositeFileUrl($banner = '', $subPath = 'microsite')
    {
        return $this->micrositeHelper->getMicrositeFileUrl($banner, $subPath);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }
}
