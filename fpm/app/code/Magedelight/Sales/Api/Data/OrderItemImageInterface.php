<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Api\Data;

/**
 * @api
 */
interface OrderItemImageInterface
{
    /*
     * Base image url
     */
    const IMAGE_URL = 'image_url';

    /**
     * Retrieve base image url
     *
     * @return string
     */
    public function getImageUrl();

    /**
     * Set base image url
     * @param string $imagePath
     * @return $this
     */
    public function setImageUrl($imagePath);
}
