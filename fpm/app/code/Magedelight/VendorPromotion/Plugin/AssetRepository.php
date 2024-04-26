<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Plugin;

class AssetRepository
{
    protected $_prependList = [
        'images/rule_component_apply.gif',
        'images/rule_component_add.gif',
        'images/rule_component_remove.gif',
        'images/rule_chooser_trigger.gif'
    ];
    public function beforeGetUrl(\Magento\Framework\View\Asset\Repository $subject, $fileId)
    {
        if (in_array($fileId, $this->_prependList)) {
            $fileId = 'Magedelight_VendorPromotion::'.$fileId;
        }
        return [$fileId];
    }
    public function beforeGetUrlWithParams(\Magento\Framework\View\Asset\Repository $subject, $fileId, array $params)
    {
        if (in_array($fileId, $this->_prependList)) {
            $fileId = 'Magedelight_VendorPromotion::'.$fileId;
        }
        return [$fileId, $params];
    }
}
