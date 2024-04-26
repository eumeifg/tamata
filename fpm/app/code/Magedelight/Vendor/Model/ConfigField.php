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
namespace Magedelight\Vendor\Model;

use \Magedelight\Vendor\Api\Data\ConfigFieldInterface;

class ConfigField extends \Magento\Framework\DataObject implements ConfigFieldInterface
{

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->getData(self::FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field)
    {
        return $this->setData(self::FIELD, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }
}
