<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class AccountNav extends Template
{

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magedelight\Theme\Helper\Data
     */
    public $themeHelper;

    public function __construct(
        Session $customerSession,
        Template\Context $context,
        \Magedelight\Theme\Helper\Data $themeHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->themeHelper = $themeHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
