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
namespace MDC\Vendor\Block\Microsite\Sellerdirectory;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

/**
 * Description of Seller
 *
 * @author Rocket Bazaar Core Team
 */
class Seller extends \Magedelight\Vendor\Block\Microsite\Sellerdirectory\Seller
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _addBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        /*$breadcrumbs->addCrumb(
            'md_microsite',
            [
                'label' => 'Seller Directory',
                'title' => 'Seller Directory',
                'link' => $baseUrl . 'microsite/sellerdirectory'
            ]
        );*/
    }
}
