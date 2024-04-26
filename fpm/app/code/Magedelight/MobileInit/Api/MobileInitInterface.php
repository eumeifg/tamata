<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Mobile_Connector
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MobileInit\Api;

/**
 * @api
 */
interface MobileInitInterface
{

    /**
     * Return mobile app initial screen data.
     *
     * @api
     * @param  $storeId
     * @return \Magedelight\MobileInit\Api\Data\MobileInitDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initApp();

    /**
     * Return mobile app initial screen data.
     *
     * @api
     * @return \Magedelight\MobileInit\Api\Data\MobileInitInterface
     */
    /*public function getHomeScreen();*/
}
