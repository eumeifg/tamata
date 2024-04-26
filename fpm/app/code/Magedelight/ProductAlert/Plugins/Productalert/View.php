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
namespace Magedelight\ProductAlert\Plugins\Productalert;

class View
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magedelight\ProductAlert\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * View constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\ProductAlert\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magedelight\ProductAlert\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterToHtml(\Magento\Productalert\Block\Product\View $subject, $html)
    {
        $type = $subject->getNameInLayout();
        if ($type == 'productalert.stock'
            && !$this->scopeConfig->isSetFlag('rbnotification/stock/disable_guest')
        ) {
            $product = $this->registry->registry('current_product');
            if (!$product->isSaleable()) {
                $html = $this->helper->getStockAlert(
                    $product
                );

                return $html;
            }
        }

        if ($type == 'productalert.price'
            && !$this->scopeConfig->isSetFlag('rbnotification/price/disable_guest')
        ) {
            $product = $this->registry->registry('current_product');
            $html = $this->helper->getPriceAlert(
                $product,
                $this->customerSession->isLoggedIn()
            );
        }

        return $html;
    }
}
