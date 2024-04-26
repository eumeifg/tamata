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
namespace Magedelight\Vendor\Block\Sellerhtml\Html;

/**
 * @author Rocket Bazaar Core Team
 * Class to modify header link parameters.
 */
class HeaderLink extends \Magedelight\Backend\Block\Template
{

    /**
     *
     * @param string $param
     * @param string $alias
     * @return string
     */
    public function getLinkData($param, $alias = '')
    {
        return $this->getData($param);
    }
}
