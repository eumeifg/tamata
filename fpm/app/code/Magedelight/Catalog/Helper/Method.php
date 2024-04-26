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
namespace Magedelight\Catalog\Helper;

class Method
{
    protected $_methodsNames = ['NewMethod','Bestselling'];

    protected $_methods = [];

    protected $_indexedMethods = [];

    protected $_indexedMethodsExists = true;

    protected $_objectManager;

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getMethods($isConfig = false)
    {
        if (empty($this->_methods)) {
            foreach ($this->_methodsNames as $className) {
                $class = 'Magedelight\Catalog\Model\Method\\' . $className;

                if (!$this->isEnabledMethod($className, $isConfig)) {
                    continue;
                }
                $data = [
                    'code'  => $class::CODE,
                    'name'  => $class::NAME,
                    'class' => $class
                ];
                if (is_subclass_of(
                    $class,
                    Magedelight\Catalog\Api\MethodInterface::class,
                    true
                )) {
                    $this->_methods[$class::CODE] = $data;
                }
            }
            if (empty($this->_indexedMethods)) {
                $this->_indexedMethodsExists = false;
            }
        }

        return $this->_methods;
    }

    public function isEnabledMethod($className, $isConfig = false)
    {
        $classPath = 'Magedelight\Catalog\Model\Method\\' . $className;
        $enabled = $classPath::ENABLED;
        if (!$enabled) {
            return false;
        }
        if (!$isConfig) {
            $disabled = $this->_scopeConfig->getValue(
                'rbsort/general/disable_methods',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return (false === in_array($classPath::CODE, explode(',', $disabled)));
        }
        return true;
    }

    public function applyMethodByToolbar(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject)
    {
        $order = $subject->getCurrentOrder();
        $method = $this->getMethodByCode($order);
        if ($method instanceof \Magedelight\Catalog\Api\MethodInterface) {
            $method->apply($subject->getCollection(), $subject->getCurrentDirection());
        }
    }

    public function getMethodByCode($code)
    {
        if (!isset($this->_methods[$code])) {
            return null;
        }
        if (!isset($this->_methods[$code]['object'])) {
            $class = $this->_methods[$code]['class'];
            $this->_methods[$code]['object'] = $this->_objectManager->get(
                $class
            );
        }
        return $this->_methods[$code]['object'];
    }
}
