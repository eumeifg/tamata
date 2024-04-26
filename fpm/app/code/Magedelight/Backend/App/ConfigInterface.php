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
namespace Magedelight\Backend\App;

/**
 *
 * @author Rocket Bazaar Core Team
 */
interface ConfigInterface
{
    /**
     * Retrieve config value by path
     *
     * Path should looks like keys imploded by "/". For example scopes/stores/admin
     *
     * @param string $path
     * @return mixed
     * @api
     */
    public function getValue($path);

    /**
     * Set config value
     *
     * @deprecated
     * @param string $path
     * @param mixed $value
     * @return void
     * @api
     */
    public function setValue($path, $value);

    /**
     * Retrieve config flag
     *
     * Path should looks like keys imploded by "/". For example scopes/stores/admin
     *
     * @param string $path
     * @return bool
     * @api
     */
    public function isSetFlag($path);
}
