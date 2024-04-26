<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Observer;

class RegisterUserEntry implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Model\VendorWebsiteFactory
     */
    protected $vendorWebsiteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    protected $resourceConnection;
    
    /**
     * @var \Magedelight\Vendor\Model\VendorWebsiteRepository
     */
    protected $vendorWebsiteRepository;
    
    protected $resources = null;

    /**
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magedelight\Vendor\Model\VendorWebsiteFactory $vendorWebsiteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Model\VendorWebsiteRepository $vendorWebsiteRepository
     */
    public function __construct(
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magedelight\Vendor\Model\VendorWebsiteFactory $vendorWebsiteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\VendorWebsiteRepository $vendorWebsiteRepository
    ) {
        $this->authSession = $authSession;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->resourceConnection = $resourceConnection;
        $this->vendorWebsiteFactory = $vendorWebsiteFactory;
        $this->storeManager = $storeManager;
    }
   
    /**
     * Insert link entry
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $loggedVendorId = ($this->authSession->getUser())?$this->authSession->getUser()->getVendorId():'';
        $vendor = $observer->getEvent()->getVendor();
        $status = $observer->getEvent()->getStatus();
        $postData = $observer->getEvent()->getPostData();
        $this->resources = $this->resourceConnection;
        $connection = $this->resources->getConnection();
        
        $table = $this->resources->getTableName('md_vendor_user_link');
        $where = ['vendor_id = ?' => (int) $vendor->getId()];
        $connection->delete($table, $where);
        
        $data = [];
        $data[] = ['vendor_id' => (int) $vendor->getId(), 'parent_id' => (int) $loggedVendorId, 'role_id' => (int) $postData['roles'][0]];
        $connection->insertMultiple($table, $data);
        
        // Insert into md_vendor_website_data
        $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($vendor->getId(), $websiteId);
        if ($vendorWebsite && $vendorWebsite->getId()) {
             $vendorWebsite->setData('vendor_website_id', $vendorWebsite->getId());
        } else {
            $vendorWebsite = $this->vendorWebsiteFactory->create();
            $vendorWebsite->setData('store_id', $storeId);
            $vendorWebsite->setData('website_id', $websiteId);
            $vendorWebsite->setData('vendor_id', $vendor->getId());
            $vendorWebsite->setData('is_user', '1');
        }
        $vendorWebsite->setData('status', $status);
        $vendorWebsite->setData('name', $vendor->getName());
        $vendorWebsite->save();
    }
}
