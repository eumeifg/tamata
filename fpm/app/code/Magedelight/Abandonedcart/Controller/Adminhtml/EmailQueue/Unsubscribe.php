<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;

class Unsubscribe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;
    
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $blacklistFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->blacklistFactory = $blacklistFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        if ($data) {
            $model = $this->blacklistFactory->create();
            $ifExist = $model->getCollection()->addFieldToFilter('email', $data['email'])->getFirstItem();
            if (count($ifExist) > 0) {
                $this->messageManager->addNotice(__('You have already unsubscribed.'));
                return $resultRedirect->setPath('/');
            }
            
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You have unsubscribed successfully.'));
                $this->dataPersistor->clear('magedelight_abandonedcart_blacklist');

                $resultRedirect->setPath('/');
            } catch (\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
        }
        return $resultRedirect->setPath('/');
    }
}
