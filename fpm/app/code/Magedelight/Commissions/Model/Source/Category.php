<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Source;

use Magento\Catalog\Model\Config\Source\Category as SourceCateogry;
use Magedelight\Commissions\Model\CommissionFactory;

class Category extends SourceCateogry
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        CommissionFactory $commissionFactory
    ) {
        parent::__construct($categoryCollectionFactory);
        $this->storeManager = $storeManager;
        $this->commissionFactory = $commissionFactory;
    }

    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = true, $websiteId = 1)
    {
        $categories = $this->commissionFactory->create()->getCollection()->getColumnValues('product_category');
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $availableCategories = [];
        $website = $this->storeManager->getWebsite($websiteId);
        $catIds = [];
        foreach ($website->getGroups() as $group) {
            $stores   = $group->getStores();
            foreach ($stores as $store) {
                $catIds[] = $store->getRootCategoryId();
            }
        }
        $options = [];

        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter("entity_id", ['in' => $catIds])
            ->addRootLevelFilter()
            ->load();

        if ($addEmpty) {
            $options[] = ['label' => __('-- Please Select a Category --'), 'value' => ''];
        }
        foreach ($collection as $category) {
            if ($category->hasChildren()) {
                $availableCategories = $this->getChildCategories($category, $categories);
            }
        }
        return array_merge($options, $availableCategories);
    }

    /**
     *
     * @param type $category
     * @param type array $categories
     * @return array
     */
    protected function getChildCategories($category, $categories = [])
    {
        $subCats = $category->getChildrenCategories();
        foreach ($subCats as $category) {
            $space = "";
            for ($i=0; $i < $category->getLevel()-1; $i++) {
                $space .= "--";
            }
            $disabled = (in_array($category->getId(), $categories)) ? true : false;
            $options[] = [
                'label' => $space . $category->getName(),
                'value' => $category->getId(), 'disabled' => $disabled
            ];
            if ($category->hasChildren()) {
                $options = array_merge($options, $this->getChildCategories($category, $categories));
            }
        }
        return $options;
    }
}
