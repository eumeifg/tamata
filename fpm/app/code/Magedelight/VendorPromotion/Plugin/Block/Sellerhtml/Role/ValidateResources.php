<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Plugin\Block\Sellerhtml\Role;

/**
 * @author Rocket Bazaar Core Team
 */
class ValidateResources
{

    /**
     * @var \Magedelight\VendorPromotion\Helper\Data
     */
    private $helper;

    /**
     * 
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magedelight\VendorPromotion\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * 
     * @param \Magedelight\User\Block\Sellerhtml\Role\Edit $subject
     * @param array $result
     * @return array
     */
    public function afterGetTree(\Magedelight\User\Block\Sellerhtml\Role\Edit $subject, $result) {
        if(!$this->helper->isEnabled()){
            foreach ($result as $key => $value) {
                if($value['attr']['data-id'] == 'Magedelight_Vendor::promotion'){
                    /* Remove role based on configuration.*/
                    unset($result[$key]);
                    $result = array_merge($result);
                }
            }
        }
        return $result;
    }
}
