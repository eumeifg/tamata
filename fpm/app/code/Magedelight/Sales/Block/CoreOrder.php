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
namespace Magedelight\Sales\Block;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class CoreOrder implements ArgumentInterface
{

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     */
    public function __construct(
        \Magedelight\Sales\Helper\Data $salesHelper
    ) {
        $this->salesHelper = $salesHelper;
    }
    
    /**
     *
     * @return bool
     */
    public function isMagentoOrderStatusDisplayed()
    {
        return $this->salesHelper->showMainOrderStatus();
    }
}
