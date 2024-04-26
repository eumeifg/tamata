<?php
/**
* Magedelight
* Copyright (C) 2018 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_SocialLogin
* @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/

namespace Magedelight\SocialLogin\Cron;

class Mdkv 
{
    protected $helper;

    public function __construct(
        \Magedelight\SocialLogin\Helper\Mddata $helper
    ) 
    {
        $this->helper = $helper;
    }

    public function execute()
    {
        $mappedDomains = $this->helper->getAllowedDomainsCollection();
    }
}

