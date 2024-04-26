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

class handleBlockAlert implements ObserverInterface
{
    protected $_scopeConfig;
    protected $_registry;
    protected $_helper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magedelight\ProductAlert\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $layout = $event->getLayout();
        $name = $event->getElementName();
        $block = $layout->getBlock($name);
        $transport = $event->getTransport();
        $html = $transport->getData('output');
        $pos = strpos($html, 'alert stock');

        if ($block instanceof \Magento\Productalert\Block\Product\View && $pos
            && !$this->_scopeConfig->isSetFlag('rbnotification/stock/disable_guest')
        ) {
            if (!$this->_registry->registry('customerIsLoggedIn')) {
                $res = preg_match('/product_id\\\\\\/([0-9]+)\\\\\\//', $html, $result);
                if ($result) {
                    $result = [];
                    $product = $this->_registry->registry('current_product');
                    if (!$product->isSaleable()) {
                        $blockHtml = $this->_helper->getStockAlert(
                            $product,
                            $this->_registry->registry('customerIsLoggedIn'),
                            1
                        );
                        $html = $blockHtml;
                        $transport->setData('output', $html);
                    }
                }
            }
        }

        $pos = strpos($html, 'alert price');
        if ($block instanceof \Magento\Productalert\Block\Product\View && $pos
            && !$this->_scopeConfig->isSetFlag('rbnotification/price/disable_guest')
        ) {
            preg_match('/product_id\\\\\\/([0-9]+)\\\\\\//', $html, $result);
            if ($result && !$this->_registry->registry('customerIsLoggedIn')) {
                $result = [];
                $product = $this->_registry->registry('current_product');
                $blockHtml = $this->_helper->getPriceAlert(
                    $product,
                    $this->_registry->registry('customerIsLoggedIn')
                );
                $html = $blockHtml;
                $transport->setData('output', $html);
            }

        }
    }
}
