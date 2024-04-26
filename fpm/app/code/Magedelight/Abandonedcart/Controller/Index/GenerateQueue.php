<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Index;

class GenerateQueue extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Abandonedcart\Helper\Data $helper
    ) {
        $this->helper = $helper;
        
        parent::__construct($context);
    }

    /**
     * Generate Action
     */
    public function execute()
    {
        $this->helper->generateAbandonedcartQueue();
    }
}
