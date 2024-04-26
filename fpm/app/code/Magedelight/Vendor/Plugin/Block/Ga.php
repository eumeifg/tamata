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
namespace Magedelight\Vendor\Plugin\Block;

class Ga
{
    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Vendor\Model\MicrositeFactory $microsite
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Model\MicrositeFactory $microsite
    ) {
        $this->request = $request;
        $this->microsite = $microsite;
    }

    public function aftergetPageTrackingCode(\Magento\GoogleAnalytics\Block\Ga $subject, $native)
    {

        $vid = $this->request->getParam('vid');
        $vendorData = $this->microsite->create()->getCollection()->addFieldToFilter('vendor_id', $vid)->getFirstItem();
        if ($vid && isset($vendorData['google_analytics_account_number'])) {
            $pageName = 'microsite_landing';
            $optPageURL = '';
            if ($pageName && substr($pageName, 0, 1) == '/' && strlen($pageName) > 1) {
                $optPageURL = ", '{$this->escapeJsQuote($pageName)}'";
            }
            $microGaId = $vendorData["google_analytics_account_number"];

            return "\nga('create', '$microGaId'), 'auto');\nga('send', 'pageview'{$optPageURL});\n";
        } else {
            return $native;
        }
    }
}
