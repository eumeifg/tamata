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
namespace Magedelight\Catalog\Model\Config\Source\DefaultVendor;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;

class DefaultCriteria extends Criteria
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions(false);
    }
}
