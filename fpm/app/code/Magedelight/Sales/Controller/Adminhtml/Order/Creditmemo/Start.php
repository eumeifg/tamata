<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Controller\Adminhtml\Order\Creditmemo;

class Start extends \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Start
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * Start create creditmemo action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /**
         * Clear old values for creditmemo qty's
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('rbsales/*/new', ['_current' => true]);
        return $resultRedirect;
    }
}
