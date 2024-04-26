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
namespace Magedelight\Abandonedcart\Controller\Adminhtml\BlackList;

use Magedelight\Abandonedcart\Controller\Adminhtml\Blacklist;
 
class Save extends Blacklist
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Helper\Data $helper,
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
     * @param \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
    ) {
        $this->helper = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->blacklistFactory = $blacklistFactory;
        parent::__construct($context, $coreRegistry);
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
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $blacklistArr = $this->helper->getBlacklistArray($data['website_id'], $data['email']);
            
            if (in_array($data['email'], $blacklistArr)) {
                $this->messageManager->addNotice(__('This email was already added to blacklist!'));
                return $resultRedirect->setPath('*/*/');
            }

            $id = $this->getRequest()->getParam('id');
            $model = $this->blacklistFactory->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This blacklist no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Blacklist was saved successfully.'));
                $this->dataPersistor->clear('magedelight_abandonedcart_blacklist');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
            $this->dataPersistor->set('magedelight_abandonedcart_blacklist', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
