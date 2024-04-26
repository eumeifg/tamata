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
namespace Magedelight\Vendor\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassStatus extends AbstractMassAction
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::edit_request');
    }

    /**
     * @var \Magedelight\Vendor\Model\VendorWebsiteRepository
     */
    protected $vendorWebsiteRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Magedelight\Vendor\Model\VendorWebsiteRepository $vendorWebsiteRepository,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context, $filter, $collectionFactory);
    }

    /**
     * Vendor mass status action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $status = (int) $this->getRequest()->getParam('status');
        $request = $this->getRequest();
        foreach ($collection->_addWebsiteData(['status']) as $item) {
            $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $item->getVendorId(),
                $item->getWebsiteId()
            );
            $vendorOb = $this->vendorRepository->getById($item->getVendorId());
            if ($vendorWebsite && $vendorWebsite->getId()) {
                $vendorWebsite->setStatus($status);
                $vendorWebsite->save();
                $this->_eventManager->dispatch(
                    'adminhtml_vendor_save_after',
                    ['vendor' => $vendorOb, 'request' => $request,'status' => $status ,'action' => 'mass']
                );
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $collection->getSize()));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
