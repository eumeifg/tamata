<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\Catalog\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class VendorList extends \Magedelight\ConfigurableProduct\Controller\Ajax\VendorList
{
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
        // $productId = $this->getRequest()->getParam('productId');
        /* $autocompleteData = $this->_helper->getAvailableVendorsForProduct($productId);
          $responseData = [];
          foreach ($autocompleteData as $resultItem) {
          $responseData[] = $resultItem->toArray();
          } */

        $responseData['response'] = $this->getHtml();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $shoHideRowsNo = 3;
        $productId = $this->getRequest()->getParam('productId');
        $html = null;
        $prices = [];
        $vendorData = $this->_helper->getAvailableVendorsForProduct($productId);
            foreach ($vendorData as $data) {
                if ($data->getVendorId()!= $this->_helper->getProductDefaultVendor($productId)->getVendorId()) {
                     $prices [] = $data->getSpecialPrice();
                }
             }
         $helper = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\MDC\Catalog\Helper\Listing\Data::class);
          
        if ($this->_helper->getProductNoOfVendors($productId)) {
            $html .= '<div class="page-title-wrapper vendor">
            <h2 class="page-title">
                <span class="base">' . __('%1 other offers from', $this->_helper->getProductNoOfVendors($productId)) . '</span>  <span>'. $helper->getMinVendorOfferedPrice($prices).'</span>  </h2>
                <a class="view-more-sellers" href="#"> '. __('view more sellers').  '</a>
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
            $vendorData = $this->_helper->getAvailableVendorsForProduct($productId);
            foreach ($vendorData as $data) :
                if ($data->getVendorId() == $this->_helper->getProductDefaultVendor($productId)->getVendorId()) {
                    continue;
                }

                $html .= '<tr class="item-info">
                            <td class="col vendor-name">';
                $html .= $data->getBusinessName();
                $html .= '</td>';
                $html .= '<td class="col item-condition">'. $this->getConditionOptionText($data->getCondition()) .'</td>';
                if ($data->getRatingAvg() == 0) :
                    $html .= '<td class="col vendor-rating">
                                        <div class="rating-summary vender-display-list">
                                            <div class="seller-rating-container">
                                                <span class="seller-rating">' . __("<span>N</span>/A") . '</span>
                                            </div>
                                        </div>
                                    </td>';
                else :
                    $ratImage = $data->getRatingAvg();
                    $html .= '<td class="col vendor-rating">
                                        <div class="rating-summary vender-display-list">
                                            <div class="seller-rating-container ">
                                                <span class="seller-rating">' . __("<span> %1 </span> / 5", (round($ratImage, 1))) . '</span>
                                            </div>
                                        </div>
                                    </td>';
                endif;

                $html .= '<td class="col vendor-price">';
                $html .= $this->_helper->getPriceHtml($data, $productId);
                $html .= '</td>';
                $html .= '<td class="col vendor-action">';
             /*$html .= '<form data-role="tocart-form" action="product/9/" method="post">
                            <input type="hidden" name="product" value="9">
                            <input type="hidden" name="uenc" value="">
                            <input name="form_key" type="hidden" value="">
                            <button type="submit" title="Add to Cart" class="action tocart primary">
                                <span>Add to Cart</span>
                            </button>
                        </form>';
             */
                $html .= '<button data-id="' . $data->getVendorId() . '" type="button" title="' . __('Add To Cart') . '" class="action button primary product-view"> <span> ' . __('Add To Cart') . '</span> </button>';
                $html .= '</td>';
            
                $html .= '</tr>';
            endforeach;

            $html .= '</tbody>';
            $html .= '</table>';
            if ($this->_helper->getProductNoOfVendors($productId) > $shoHideRowsNo) :
                $html .= '<div class="action-toolbar view-sellers">
                    <button type="button" id="view_all_sellers"
                            name="view_all_sellers"
                            class="action view-all-sellers">
                        <span>' . __('View All (%1) Vendors', $this->_helper->getProductNoOfVendors()) . '</span>
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

             $(document).ready(function(){
              $(".view-more-sellers").click(function(){
                $(".vendor.table-wrapper").slideToggle();
              });
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
}
