<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Adminhtml\Page;

use Magento\Backend\Block\Template;

/**
 * Class Menu
 *
 * @method Menu setTitle(string $title)
 * @method string getTitle()
 *
 * @package Aheadworks\Raf\Block\Adminhtml\Page
 */
class Menu extends Template
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Aheadworks_Raf::page/menu.phtml';
}
