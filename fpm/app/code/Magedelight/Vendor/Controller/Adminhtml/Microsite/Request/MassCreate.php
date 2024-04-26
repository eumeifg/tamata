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
namespace Magedelight\Vendor\Controller\Adminhtml\Microsite\Request;

use Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Description of MassCreate
 *
 * @author Rocket Bazaar Core Team
 */
class MassCreate extends \Magento\Backend\App\Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Vendor\Model\MicrositeFactory
     */
    protected $micrositeFactory;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magedelight\Vendor\Model\MicrositeFactory $micrositeFactory
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magedelight\Vendor\Model\MicrositeFactory $micrositeFactory,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->micrositeFactory = $micrositeFactory;
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $model = $this->micrositeFactory->create();

        $storeId = $this->getRequest()->getParam('store_id');
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteName = $this->storeManager->getWebsite($websiteId)->getName();
        $data = [];
        $noAccountsForSelectedStore = [];
        $duplicateMicrosites = [];
        foreach ($collection as $microsite) {
            $vendorIds[] = $microsite->getVendorId();
            if ($storeId == $microsite->getStoreId()) {
                $duplicateMicrosites[] = $microsite->getBusinessName();
            }
            $collection = $this->vendorFactory->create()->getCollection()->addFieldToSelect('email');
            $collection->getSelect()->join(
                ['rvwd'=>'md_vendor_website_data'],
                'rvwd.vendor_id = main_table.vendor_id and rvwd.email_verified = 1',
                ['rvwd.vendor_id']
            );
            $collection->getSelect()->where('main_table.vendor_id = ?', $microsite->getVendorId());
            $email = $collection->getFirstItem()->getEmail();
            if (!empty($email)) {
                $collection = $this->vendorFactory->create()->getCollection()->addFieldToSelect('email');
                $collection->getSelect()->join(
                    ['rvwd'=>'md_vendor_website_data'],
                    'rvwd.vendor_id = main_table.vendor_id and rvwd.email_verified = 1',
                    ['rvwd.vendor_id']
                );
                $collection->getSelect()->where('main_table.email = ?', $email);
                $relatedVendorId = $collection->getFirstItem()->getVendorId();
                if (!empty($relatedVendorId)) {
                    $micrositeColln = $this->micrositeFactory->create()->getCollection()->addFieldToSelect('vendor_id');
                    $micrositeColln->getSelect()->where('main_table.store_id = ?', $storeId);
                    $micrositeColln->getSelect()->where('main_table.vendor_id = ?', $relatedVendorId);
                    if (!$micrositeColln->getFirstItem()->getVendorId()) {
                        $micrositeData = $microsite->getData();
                        $micrositeData['microsite_id'] = null;
                        $micrositeData['vendor_id'] = $relatedVendorId;
                        $micrositeData['store_id'] = $storeId;
                        if (array_key_exists('business_name', $micrositeData)) {
                            unset($micrositeData['business_name']);
                        }
                        $model = $this->micrositeFactory->create();
                        $model->setData($micrositeData)->save();
                        $newMicrosites[] = $model->getMicrositeId();
                        $eventParams = [
                            'microsite_id' => $model->getMicrositeId(),
                            'parent_microsite_id' => $microsite->getMicrositeId(),
                            'microsite' => $model
                        ];
                        $this->_eventManager->dispatch('microsite_save_after', $eventParams);
                    } else {
                        $duplicateMicrosites[] = $micrositeColln->getFirstItem()->getBusinessName();
                    }
                } else {
                    $noAccountsForSelectedStore[] = $microsite->getBusinessName();
                }
            }
        }
        if (!empty($noAccountsForSelectedStore)) {
            $this->messageManager->addErrorMessage(__(
                'Vendors with business name(s) %1 are not associated with the %2 site.',
                ' [ ' . implode(',', $noAccountsForSelectedStore) . ' ]',
                $websiteName
            ));
        }

        if (!empty($duplicateMicrosites)) {
            $this->messageManager->addErrorMessage(__(
                'Microsite of Vendors with business name(s) %1 already exists on %2 site.',
                ' [ ' . implode(',', $duplicateMicrosites) . ' ]',
                $websiteName
            ));
        }

        if (!empty($newMicrosites)) {
            $this->messageManager->addSuccess(__(
                'A total of %1 microsites(s) have been created.',
                count($newMicrosites)
            ));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::save_microsite');
    }
}
