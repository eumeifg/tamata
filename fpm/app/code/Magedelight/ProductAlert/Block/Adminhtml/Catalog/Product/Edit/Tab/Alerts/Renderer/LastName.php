<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer;

use Magento\Framework\DataObject;

class LastName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        if (!$row->getEntityId()) {
            $row->setLastname(__('Guest'));
        }
        return $row->getLastname();
    }
}
