<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Observer;

use Magedelight\ConfigurableProduct\Model\ConfigurableProductResolver;
use Magento\Framework\Event\ObserverInterface;
use Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class DeleteParentProductsWithNoChild implements ObserverInterface
{
    /**
     * @var ConfigurableProductResolver
     */
    protected $configurableProductResolver;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * DeleteParentProductsWithNoChild constructor.
     * @param ConfigurableProductResolver $configurableProductResolver
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ConfigurableProductResolver $configurableProductResolver,
        CollectionFactory $collectionFactory
    ) {
        $this->configurableProductResolver = $configurableProductResolver;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* On single offer delete */
        $ids = [$observer->getEvent()->getId()];
        /* On single offer delete */

        if ($observer->getEvent()->getIds()) {
            /* When mass delete performed. */
            $ids = $observer->getEvent()->getIds();
        }
        $collection  = $this->collectionFactory->create()
            ->addFieldToFilter('main_table.vendor_product_id', ['in' => $ids])
            ->addFieldToSelect(['parent_id']);
        $collection->getSelect()->group('parent_id');
        $this->configurableProductResolver->deleteParentProductsWithNoChild($collection->getColumnValues('parent_id'));
    }
}
