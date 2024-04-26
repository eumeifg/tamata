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
namespace Magedelight\User\Controller\Adminhtml\Manage;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $collectionFactory;
        
    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
    }
    
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    
    public function execute()
    {
        $vendorCollection = $this->filter->getCollection($this->collectionFactory->create());
        $vendorCollectionSize = $vendorCollection->getSize();
        
        foreach ($vendorCollection->getItems() as $vendor) {
            $vendor->delete();
        }
        $eventParams = ['vendor_ids' => $vendorCollection->getAllIds()];
        $this->_eventManager->dispatch('vendor_status_mass_delete_after', $eventParams);
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $vendorCollectionSize));
        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Magedelight_User::delete_vendor_user");
    }
}
