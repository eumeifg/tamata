<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;

/**
 * Class MassDelete
 */
class MassDisable extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    protected function itemAction(PackInterface $pack)
    {
        $pack->setStatus(0);
        $this->repository->save($pack);
    }
}
