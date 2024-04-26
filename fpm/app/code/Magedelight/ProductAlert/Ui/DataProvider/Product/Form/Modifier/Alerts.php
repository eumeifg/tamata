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
namespace Magedelight\ProductAlert\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form;

class Alerts extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;


    /**
     * Alerts constructor.
     * @param ArrayManager $arrayManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        ArrayManager $arrayManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->arrayManager = $arrayManager;
        $this->_scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function canShowTab()
    {
        $alertPriceAllow = $this->_scopeConfig->getValue('catalog/productalert/allow_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $alertStockAllow = $this->_scopeConfig->getValue('catalog/productalert/allow_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ($alertPriceAllow || $alertStockAllow);
    }

    public function modifyMeta(array $meta)
    {
        if (!$this->canShowTab()) {
            return $meta;
        }
        $panelConfig['arguments']['data']['config'] = [
            'componentType' => 'fieldset',
            'label' => 'Product Alerts',
            'additionalClasses' => 'admin__fieldset-section',
            'collapsible' => true,
            'opened' => false,
            'dataScope' => 'data',
        ];

        $information['arguments']['data']['config'] = [
            'componentType' => 'container',
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => $this->layoutFactory->create()->createBlock(
                'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock'
            )->toHtml() . '<br />',
        ];

        $panelConfig = $this->arrayManager->set(
            'children',
            $panelConfig,
            [
                'information_links' => $information,
            ]
        );


        return $this->arrayManager->set('product_alerts', $meta, $panelConfig);
    }
}
