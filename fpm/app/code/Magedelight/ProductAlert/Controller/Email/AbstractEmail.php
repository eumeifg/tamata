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
namespace Magedelight\ProductAlert\Controller\Email;

abstract class AbstractEmail extends \Magento\Framework\App\Action\Action
{
    protected $_scopeConfig;
    protected $_customerSession;
    protected $_storeManager;
    protected $_redirectFactory;
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_redirectFactory = $context->getResultRedirectFactory();
        $this->_productRepository = $productRepository;
    }

    public function preDispatch()
    {
        parent::preDispatch();
        if ($this->_scopeConfig->isSetFlag('rbnotification/stock/disable_guest')) {
            if (!$this->_customerSession->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
                if (!$this->_customerSession->getBeforeUrl()) {
                    $this->_customerSession->setBeforeUrl($this->_getRefererUrl());
                }
            }
        }
    }
}
