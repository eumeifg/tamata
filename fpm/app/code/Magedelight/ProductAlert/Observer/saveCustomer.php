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
namespace Magedelight\ProductAlert\Observer;

use Magento\Framework\Event\ObserverInterface;

class saveCustomer implements ObserverInterface
{

    protected $_registry;
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_registry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_registry->register('customerIsLoggedIn', $this->_customerSession->isLoggedIn());
    }
}
