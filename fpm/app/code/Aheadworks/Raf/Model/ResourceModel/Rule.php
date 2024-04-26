<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel as MagentoFrameworkAbstractModel;

/**
 * Class Rule
 * @package Aheadworks\Raf\Model\ResourceModel
 */
class Rule extends AbstractResourceModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_raf_rule', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function save(MagentoFrameworkAbstractModel $object)
    {
        $object->beforeSave();
        return parent::save($object);
    }
}
