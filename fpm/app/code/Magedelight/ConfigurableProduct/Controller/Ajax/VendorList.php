<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Catalog\Helper\Data as CatalogHelper;

class VendorList extends Action
{

    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    protected $_productCondition;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * VendorList constructor.
     * @param Context $context
     * @param CatalogHelper $helper
     * @param \Magedelight\Catalog\Model\Source\Condition $productCondition
     */
    public function __construct(
        Context $context,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magedelight\Catalog\Model\Source\Condition $productCondition
    ) {
        $this->_helper = $helper;
        $this->_productCondition = $productCondition;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('productId', false)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }
        $responseData['response'] = $this->getHtml();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHtml()
    {

        $shoHideRowsNo = 3;
        $productId = $this->getRequest()->getParam('productId');
        $html = null;

        $vendorData = $this->_helper->getAvailableVendorsForProduct($productId);
        $noOfOffersOnProduct = $vendorData->getSize();

        if ($noOfOffersOnProduct) {
            $html .= '<div class="page-title-wrapper vendor">
            <h2 class="page-title">
                <span class="base">' . __(
                'Sold by (%1) Vendor',
                $noOfOffersOnProduct
            ) . '</span>    </h2>
        </div>
        <div class="vendor table-wrapper">    
            <table id="vendorlist" class="vendor-list items tablesorter data table">
            
                <thead> 
                    <tr> 
                        <th class="col vendor-name sortable">' . __('Vendor') . '</th>
                        <th class="col vendor-name sortable">' . __('Condition') . '</th>
                        <th class="col vendor-rating sortable">' . __('Rating') . '</th>
                        <th class="col vendor-price sortable">' . __('Price') . '</th>
                        <th class="col vendor-action">&nbsp;</th>
                    </tr> 
                </thead>';

            $html .= '<tbody class="vendor-list item">';


            $defaultVendorData = $vendorData->getFirstItem();
            foreach ($vendorData as $data):
                if ($data->getVendorId() == $defaultVendorData->getVendorId()) {
                    continue;
                }

                $html .= '<tr class="item-info">
                            <td class="col vendor-name">';
                $html .= $data->getBusinessName();
                $html .= '</td>';
                $html .= '<td class="col item-condition">' . $this->getConditionOptionText($data->getCondition()) .
                    '</td>';
                if ($data->getRatingAvg() == 0):
                    $html .= '<td class="col vendor-rating">
                                        <div class="rating-summary vender-display-list">
                                            <div class="seller-rating-container">
                                                <span class="seller-rating">' . __("<span>N</span>/A") . '</span>
                                            </div>
                                        </div>
                                    </td>';
                else:
                        $averageRatings = $this->getRating($data->getRatingAvg()) ;
                        $html .= '<td class="col vendor-rating">
                                    <div class="rating-summary">
                                    <div class="rating-result" title="'.$averageRatings.'%">
                                             <span style="width:'.$averageRatings.'%">
                                                 <span>
                                                     <span itemprop="ratingValue">'.$averageRatings.'</span>% of <span itemprop="bestRating">100</span>
                                                 </span>
                                             </span> 
                                        </div>
                                    </div>
                                </td>';
                endif;

                    $html .= '<td class="col vendor-price">';
                    $html .= $this->_helper->getPriceHtml($data, $productId);
                    $html .= '</td>';
                    $html .= '<td class="col vendor-action">';
            /* $html .= '<form data-role="tocart-form" action="product/9/" method="post">
                           <input type="hidden" name="product" value="9">
                           <input type="hidden" name="uenc" value="">
                           <input name="form_key" type="hidden" value="">
                           <button type="submit" title="Add to Cart" class="action tocart primary">
                               <span>Add to Cart</span>
                           </button>
                       </form>';
            */
                    $html .= '<button data-id="' . $data->getVendorId() . '" type="button" title="' .
                        __('Add To Cart') . '" class="action button primary product-view"> <span> ' .
                        __('Add To Cart') . '</span> </button>';
                    $html .= '</td>';

                    $html .= '</tr>';
            endforeach;

            $html .= '</tbody>';
            $html .= '</table>';
            if ($noOfOffersOnProduct > $shoHideRowsNo):
                $html .= '<div class="action-toolbar view-sellers">
                    <button type="button" id="view_all_sellers"
                            name="view_all_sellers"
                            class="action view-all-sellers">
                        <span>' . __('View All (%1) Vendors', $noOfOffersOnProduct) . '</span>
                    </button>        
                </div>';
            endif;
            $html .= '</div>';

            $html .= '<script>
        require([\'jquery\', \'tablesorter\'], function ($) {
        
                var vendorId;
            $("#vendorlist").tablesorter({
                sortList: [[2, 0]]
            });
            $("#vendorlist button").click(function () {
               vendorId = $(this).attr("data-id");
               $("#vendor-id").val($(this).attr("data-id"));               
               $("#product_addtocart_form").submit();        
            });            
        });
    </script>
';
        }
        return $html;
    }

    /**
     *
     * @param type $value
     * @return get optins value text
     */
    public function getConditionOptionText($value)
    {
        return $this->_productCondition->getOptionText($value);
    }

    /**
     * @param $rating
     * @return float|int
     */
    public function getRating($rating)
    {
        $totalRating = number_format($rating, 2, '.', '');
        return ($totalRating * 100)/5;
    }
}
