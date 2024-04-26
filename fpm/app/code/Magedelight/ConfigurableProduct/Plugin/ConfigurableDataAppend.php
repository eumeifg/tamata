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
namespace Magedelight\ConfigurableProduct\Plugin;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ConfigurableDataAppend
{
    
    public function __construct(
        \Magedelight\ConfigurableProduct\Model\ConfigurableMobileData $configurableMobileData,
        \Magedelight\ConfigurableProduct\Api\Data\ConfigurableDataInterface $configurableDataInterface
    ) {
        $this->configurableMobileData = $configurableMobileData;
        $this->configurableDataInterface = $configurableDataInterface;
    }

    public function afterGetById(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    ) {
        if ($entity->getTypeId() === Configurable::TYPE_CODE) {
            $configData = $this->configurableMobileData->getConfigurableData($entity);
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setConfigurableData($configData);
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $entity;
    }
}
