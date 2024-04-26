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
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Model\Microsite;

class MicrositeUrlPathGenerator
{

    /** @var \Magento\Framework\Filter\FilterManager */
    protected $filterManager;
    
    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $helper;
    
    /**
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magedelight\Vendor\Helper\Microsite\Data $helper
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Vendor\Helper\Microsite\Data $helper
    ) {
        $this->filterManager = $filterManager;
        $this->helper = $helper;
    }

    /**
     * @param Microsite $microsite
     *
     * @return string
     * @api
     */
    public function getUrlPath(Microsite $microsite)
    {
        return $microsite->getUrlKey();
    }

    /**
     * Get canonical product url path
     *
     * @param Microsite $microsite
     * @return string
     * @api
     */
    public function getCanonicalUrlPath(Microsite $microsite)
    {
        return $this->helper->getUrlParams('rbvendor/microsite_vendor/index/vid/' . $microsite->getVendorId());
    }

    /**
     * Generate Microsite url key based on url_key entered by merchant or page title
     *
     * @param Microsite $microsite
     * @return string
     * @api
     */
    public function generateUrlKey($identifier)
    {
        $urlKey = $identifier;
        return $this->filterManager->translitUrl($urlKey === '' || $urlKey === null ? '' : $urlKey);
    }
}
