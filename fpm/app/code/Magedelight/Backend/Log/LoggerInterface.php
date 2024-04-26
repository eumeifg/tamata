<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Log;

/**
 *
 * @author Rocket Bazaar Core Team
 */
interface LoggerInterface
{
    /**
     * @param string $label
     * @param mixed $param
     */
    public function debug($label = '', $param = '');
    
    /**
     * @param string $label
     * @param mixed $param
     */
    public function info($label = '', $param = '');
}
