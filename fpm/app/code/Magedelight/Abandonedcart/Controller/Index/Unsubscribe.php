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
namespace Magedelight\Abandonedcart\Controller\Index;

class Unsubscribe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;
    
    /**
     * @var \Magedelight\Abandonedcart\Model\BlacklistFactory
     */
    private $blacklistFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->blacklistFactory = $blacklistFactory;
        $this->_storeManager = $storeManager;
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
            $ifExist = $this->blacklistFactory->create()->getCollection()
                ->addFieldToFilter('email', $data['email']);
            if (count($ifExist) > 0) {
                $this->messageManager->addNotice(__('You have already been unsubscribed!'));
                return $resultRedirect->setPath('home');
            }
            $model = $this->blacklistFactory->create();
            $postData['email']=$data['email'];
            $postData['website_id']=1;
            $model->setData($postData);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You have unsubscribed successfully!'));
                $this->dataPersistor->clear('magedelight_abandonedcart_blacklist');

                return $resultRedirect->setPath('home');
            } catch (\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
        }
        
        return $resultRedirect->setPath('home');
    }
}
