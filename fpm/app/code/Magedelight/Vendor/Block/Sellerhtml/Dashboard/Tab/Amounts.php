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
namespace Magedelight\Vendor\Block\Sellerhtml\Dashboard\Tab;

class Amounts extends \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Graph
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->getDataHelper()->setParam('website', $this->getRequest()->getParam('website'));
        $this->getDataHelper()->setParam('group', $this->getRequest()->getParam('group'));

        $block = $this->getLayout()->createBlock(\Magedelight\Vendor\Block\Sellerhtml\Dashboard\Graph::class);
        $block->setAxisMaps('range', 'revenue');
        $block->setDataRows('revenue');
        $this->setChild('amount_graph', $block);
    }
}
