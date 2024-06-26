<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Base
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Base\Plugin\Magento\Framework\Data\Form\Element;

class Factory
{
    public function beforeCreate($subject, $elementType, array $config = [])
    {
        switch ($elementType) {
            case 'md_colorpicker':
                $elementType = 'Magedelight\Base\Block\Adminhtml\System\Config\Field\Colorpicker';
                break;
            
            default:
                # code...
                break;
        }
        
        return [$elementType, $config];
    }
}
