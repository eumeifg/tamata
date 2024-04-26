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
namespace Magedelight\Vendor\Model\Profile;

use Magedelight\Vendor\Api\Data\StatusDataInterface;
use Magento\Framework\DataObject;

class StatusData extends DataObject implements StatusDataInterface
{

    /**
     * {@inheritDoc}
     */
    public function getCurrentStatus()
    {
        return $this->getData(self::CURRENT_STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentStatus($status)
    {
        return $this->setData(self::CURRENT_STATUS, $status);
    }
}
