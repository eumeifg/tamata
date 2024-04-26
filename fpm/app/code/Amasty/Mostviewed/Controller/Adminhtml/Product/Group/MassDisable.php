<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Product\Group;

use Amasty\Mostviewed\Api\Data\GroupInterface;

/**
 * Class MassDelete
 */
class MassDisable extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    protected function itemAction(GroupInterface $group)
    {
        $group->setStatus(0);
        $this->repository->save($group);
    }
}
