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
namespace Magedelight\Abandonedcart\Cron;

class GenerateQueue
{
    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Abandonedcart\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Generate Action By Cron
     *
     */
    public function execute()
    {
        if ($this->helper->isAbandonedcartEnabled()) {
            $this->helper->generateAbandonedcartQueue();
        }
    }
}
