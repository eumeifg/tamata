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
namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\Data\OrderItemImageInterface;

class OrderItemImageData extends \Magento\Framework\DataObject implements OrderItemImageInterface
{
    /**
     * Retrieve base image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->getData(OrderItemImageInterface::IMAGE_URL);
    }

    /**
     * Set base image url
     * @param string $imagePath
     * @return $this
     */
    public function setImageUrl($imagePath)
    {
        return $this->setData(OrderItemImageInterface::IMAGE_URL, $imagePath);
    }
}
