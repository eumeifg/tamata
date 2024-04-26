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
namespace Magedelight\ProductAlert\Block\Product\View\Type;

class Configurable
{
    protected $_moduleManager;
    protected $_jsonEncoder;
    protected $_registry;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $registry
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_registry = $registry;
    }

    public function beforeGetAllowProducts($subject)
    {
        if (!$subject->hasAllProducts()) {
            $allProducts = $subject->getProduct()->getTypeInstance(true)
                ->getUsedProducts($subject->getProduct());
            $subject->setAllowProducts($allProducts);
            $subject->setAllProducts(true);

            $subject->hasAllowProducts();
        }
        return $subject->getData('allow_products');
    }

    public function afterFetchView($subject, $html)
    {
        if (in_array($subject->getNameInLayout(), ['product.info.options.configurable', 'product.info.options.swatches'])
            && !$this->_registry->registry('Magedelight_ProductAlert_initialization')) {
            $this->_registry->register('Magedelight_ProductAlert_initialization', 1);

            /*move creating code to Magedelight\ProductAlert\Plugins\ConfigurableProduct\Data */
            $aStockStatus = $this->_registry->registry('Magedelight_ProductAlert_data');
            $aStockStatus['changeConfigurableStatus'] = true;
            $data = $this->_jsonEncoder->encode($aStockStatus);

            $html
                = '<script type="text/x-magento-init">
                    {
                        ".product-options-wrapper": {
                                    "amnotification": {
                                        "productalert": ' . $data . '
                                    }
                         }
                    }
                   </script>' . $html;

        }
        return $html;
    }
}
