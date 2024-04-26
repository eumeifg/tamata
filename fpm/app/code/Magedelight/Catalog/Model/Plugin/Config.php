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
namespace Magedelight\Catalog\Model\Plugin;

class Config
{
    /**
     * @var \Magedelight\Catalog\Helper\Method
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;

    /**
     * @param \Magedelight\Catalog\Helper\Method                      $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magedelight\Catalog\Helper\Method $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectFactory = $objectFactory;
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @return array
     */
    public function afterGetAttributesUsedForSortBy($subject, $options)
    {
        return $this->addNewOptions($options);
    }

    /**
     * @param array $arr
     *
     * @return array
     */
    protected function addNewOptions($arr)
    {
        $methods = $this->_helper->getMethods();
        foreach ($methods as $className => $method) {
            if (!isset($arr[$method['code']])) {
                $arr[$method['code']] = $this->_objectFactory->create(
                    ['data' => [
                        'attribute_code' => $method['code'],
                        'frontend_label' => $method['name']
                    ]]
                );
            }
        }

        return $arr;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray($subject, $result)
    {
        $options = [];
        if (!$this->_scopeConfig->isSetFlag(
            'rbsort/general/hide_best_value',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            $options['position'] = __('Position');
        }
        foreach ($subject->getAttributesUsedForSortBy() as $attribute) {
            $options[$attribute['attribute_code']] = $attribute['frontend_label'];
        }
        return $this->addNewOptions($options);
    }
}
