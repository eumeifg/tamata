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

class SaveImport extends Blacklist
{
    /**
     * @var \Magedelight\Abandonedcart\Model\BlacklistFactory
     */
    private $blacklistFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
    ) {
        $this->blacklistFactory = $blacklistFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Import action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        /*echo '<pre>';
        print_r($data);
        die();*/
        if (!isset($data['csvfile'][0]) || current($data['csvfile'])['name'] == '') {
            $this->messageManager->addErrorMessage(__('Unable to get CSV File'));
            return $resultRedirect->setPath('*/*/import');
        }
        try {
            $model = $this->blacklistFactory->create();
            $import = $model->importBlacklist($data);
            if ($import) {
                $this->messageManager->addNotice(__('Some duplicate records were skipped!'));
            }
            $this->messageManager->addSuccessMessage(__('Blacklist successfully imported'));
            return $resultRedirect->setPath('*/*/');
        } catch (\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            //$this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            $this->messageManager->addErrorMessage(__('Email id already exist in list'));
        }
        return $resultRedirect->setPath('*/*/import');
    }
}
