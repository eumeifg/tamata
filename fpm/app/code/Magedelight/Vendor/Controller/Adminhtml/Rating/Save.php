<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Rating;

class Save extends \Magento\Review\Controller\Adminhtml\Rating\Save
{

    protected function initEntityId()
    {
        $this->coreRegistry->register(
            'entityId',
            $this->getRequest()->getParam('entity_id')
        );
    }
}
