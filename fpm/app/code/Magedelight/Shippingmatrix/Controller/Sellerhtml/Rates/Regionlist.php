<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Sellerhtml\Rates;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Regionlist extends \Magedelight\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
    }
        
    public function execute()
    {
        $countryId = (string)$this->getRequest()->getParam('country_id');
        
        $regionId = (string)$this->getRequest()->getParam('region_id');

        $state = "<option value='0'>All</option>";
        
        if ($countryId != "") {
            $states =$this->_countryFactory->create()->setId(
                "$countryId"
            )->getLoadedRegionCollection()->toOptionArray();
            foreach ($states as $_state) {
                if ($_state['value']) {
                    if ($regionId != '' && $_state['value']==$regionId) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                    $state .= "<option value=".$_state['value']." $selected>" . $_state['label'] . "</option>";
                }
            }
        }
        
        $result['htmlconent']=$state;
         $this->getResponse()->representJson(
             $this->jsonHelper->jsonEncode($result)
         );
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }
}
