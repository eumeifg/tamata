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
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

/**
 * @author Rocket Bazaar Core Team
 */
class Checkbusinessname extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $business_name = $this->getRequest()->getParam('business_name');
        $collection = $this->vendorFactory->create()->getCollection()->addFieldToSelect('vendor_id');
        $collection->getSelect()->join(
            ['rvwd' => 'md_vendor_website_data'],
            "rvwd.vendor_id = main_table.vendor_id AND rvwd.website_id = "
            . $this->storeManager->getStore()->getWebsiteId() . " AND business_name = '" . $business_name . "'",
            ['business_name']
        );
        if (!($collection->getFirstItem()->getId())) {
            $result = 'ok';
        } else {
            $result = 'error';
        }
        return $this->getResponse()->setBody($result);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
