<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Controller;

use Magento\Framework\App\RequestInterface;

abstract class AbstractIndex extends AbstractController
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->initLayout();
        $navigationBlock = $resultPage->getLayout()->getBlock(
            'customer_account_navigation'
        );
        if ($navigationBlock) {
            $navigationBlock->setActive('productalert/' . static::TYPE . '/index');
        }
        $resultPage->getConfig()->getTitle()->prepend(__(static::TITLE));
        return $resultPage;
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
