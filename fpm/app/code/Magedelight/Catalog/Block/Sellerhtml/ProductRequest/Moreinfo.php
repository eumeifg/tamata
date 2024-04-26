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
namespace Magedelight\Catalog\Block\Sellerhtml\ProductRequest;

class Moreinfo extends \Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Edit
{

    public function getAttributesHtml()
    {
        $this->setHtmlIdPrefix('additional-');
        $this->setFieldNamePrefix('additional[');
        $this->setFieldNameSuffix(']');
        if ($this->getAdditionalAttributes()) {
            return $this->getHtml($this->getAdditionalAttributes());
        }
        return '';
    }
}
