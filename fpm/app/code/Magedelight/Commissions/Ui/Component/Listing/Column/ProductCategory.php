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
namespace Magedelight\Commissions\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magedelight\Commissions\Model\Commission;
use Magento\Catalog\Model\Category as MagentoCategory;

class ProductCategory extends \Magento\Ui\Component\Listing\Columns\Column
{
    const COLUMN_NAME = 'product_category';
    const INDEX_FIELD = 'commission_id';

    /**
     * @var Commission
     */
    protected $commission;
    /**
     * MagentoCategory
     */
    protected $magentoCategory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param Commission $commission
     * @param MagentoCategory $magentoCategory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Commission $commission,
        MagentoCategory $magentoCategory,
        array $components = [],
        array $data = []
    ) {
        $this->commission = $commission;
        $this->magentoCategory = $magentoCategory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $commissionId = $item[self::INDEX_FIELD];
                $this->commission->load($commissionId);
                $catId = $this->commission->getData(self::COLUMN_NAME);
                $item[self::COLUMN_NAME] = $this->_getCategoryPathWithName($catId);
            }
        }
        return $dataSource;
    }

    private function _getCategoryPathWithName($catId)
    {
        $category = $this->magentoCategory->load($catId);
        $coll = $this->magentoCategory->getResourceCollection();
        $coll->addAttributeToSelect('name');
        $catPathIds = array_diff(explode('/', $category->getPath()), [1, 2, $category->getId()]);
        
        $coll->addAttributeToFilter('entity_id', ['in' => $catPathIds]);
        $result = '';
        foreach ($coll as $cat) {
            $result .= $cat->getName().'/';
        }
        return $result.$category->getname();
    }
}
