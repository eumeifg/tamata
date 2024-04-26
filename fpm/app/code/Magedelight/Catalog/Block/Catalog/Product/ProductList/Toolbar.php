<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Catalog\Product\ProductList;

class Toolbar
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_catalogHelper;

    /**
     * @var null
     */
    protected $methods = null;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magedelight\Catalog\Helper\Method
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar
     */
    protected $_toolbarModel;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Catalog\Helper\Method $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Helper\Method $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel,
        \Magedelight\Catalog\Helper\Data $catalogHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_toolbarModel = $toolbarModel;
        $this->_catalogHelper = $catalogHelper;
    }

    /**
     * @param string $order
     *
     * @return bool
     */
    protected function reverse($order)
    {
        $methods = $this->_helper->getMethods();
        if (isset($methods[$order])) {
            return true;
        }
        $attr = $this->_scopeConfig->getValue(
            'rbsort/general/desc_attributes',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($attr) {
            return in_array($order, explode(',', $attr));
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param string                                             $dir
     *
     * @return string
     */
    public function afterGetCurrentDirection($subject, $dir)
    {
        if ($this->_catalogHelper->isEnabled()) {
            $selectedDirection = strtolower($this->_toolbarModel->getDirection());
            if (!$selectedDirection
                && $this->reverse($subject->getCurrentOrder())
            ) {
                $subject->setDefaultDirection('desc');
                $dir = 'desc';
            }
        }
        return $dir;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function afterSetCollection($subject)
    {
        if ($this->_catalogHelper->isEnabled()) {
            if ($subject->getFlag('rbsort')) {
                return $subject;
            }
            $this->_helper->applyMethodByToolbar($subject);
            $subject->setFlag('rbsort', 1);
        }
        return $subject;
    }
}
