<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Model;

use Magento\CatalogRule\Model\ResourceModel\Rule as Ruleresource;

class RuleCondition extends \Magento\CatalogRule\Model\Rule
{
   /**
    * Init resource model and id field
    *
    * @return void
    */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Ruleresource::class);
        $this->setIdFieldName('rule_id');
    }
    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }
    
    public function getListProductIdsInRule()
    {
        $productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Catalog\Model\ProductFactory::class
        );
        $this->_productIds = [];
        $this->setCollectedAttributes([]);
        $this->getConditions()->collectValidatedAttributes($productCollection);
        \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Framework\Model\ResourceModel\Iterator::class
        )->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
             'attributes' => $this->getCollectedAttributes(),
             'product' => $productFactory->create()
            ]
        );
        return $this->_productIds;
    }
    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product)) {
                $this->_productIds[] = $product->getId();
            }
        }
    }
    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }
    
    public function dataConvter(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            return $arr['conditions'][1];
        }
        return null;
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (($key === 'conditions' || $key === 'actions') && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date']) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }

        return $arr;
    }
}
