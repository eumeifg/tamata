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
namespace Magedelight\Commissions\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeCatalogModelSave implements ObserverInterface
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $obj = $observer->getEvent()->getObject();
        if ($obj->getId() && $obj instanceof \Magento\Catalog\Model\Product && $obj->getTypeId() !=
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $obj1 = $this->productRepository->getById($obj->getId());
            if ($obj1->getId() == $obj->getId() && $obj1->getMdCommission()) {
                $id = $obj1->getMdCommission();
                $this->registry->unregister('commission_value');
                $this->registry->register('commission_value', $id);
            }
            unset($obj1);
        }
    }
}
