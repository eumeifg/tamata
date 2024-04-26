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
namespace Magedelight\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Offer extends AbstractHelper
{

    /**
     * @return string
     */
    public function getDeleteBtnHtml()
    {
        $html = "<td>";
        $html.= "<button type='button' class='action-delete' data-action='remove_row'></button>";
        $html.= "</td>";
        return $html;
    }
    
    public function getContentStartTags()
    {
        $html  = '<div class="admin__field admin__field-wide _no-header">';
        $html .= '<div class="admin__control-table-pagination"></div>';
        $html .= '<div class="admin__field-control" data-role="grid-wrapper">';
        $html .= '<div class="admin__control-table-wrapper">';
        $html .= '<table class="admin__dynamic-rows data-grid admin__control-table" data-role="grid">';
        return $html;
    }
    
    /**
     * @return string
     */
    public function getHeaderRowHtml()
    {
        $html  = '<thead>';
        $html .= '<tr>';
        $html .= '<th class="data-grid-th">'.__('Seller').'</th>';
        $html .= '<th class="data-grid-th">'.__('SKU').'</th>';
        $html .= '<th class="data-grid-th">'.__('Price').'</th>';
        $html .= '<th class="data-grid-th">'.__('Qty').'</th>';
        $html .= '<th class="data-grid-th">'.__('Special Price').'</th>';
        $html .= '<th class="data-grid-th">'.__('Special From Date').'</th>';
        $html .= '<th class="data-grid-th">'.__('Special To Date').'</th>';
        $html .= '<th class="data-grid-th">'.__('Reorder Level').'</th>';
        $html .= '<th class="data-grid-th">Delete</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        
        return $html;
    }
    
    public function getContentEndTags()
    {
        $html  = '</table>';
        $html .= '</div></div></div>';
        return $html;
    }
    
    /**
     * @return string
     */
    public function getFieldStartTags($type = 'text')
    {
        if ($type == 'text') {
            $html = "<td>";
            $html.= "<div class='admin__field'>";
            $html.= "<div class='admin__field-control'>";
        }
        return $html;
    }
    
    public function getFieldEndTags($type = 'text')
    {
        if ($type == 'text') {
            $html = "</div></div></td>";
        }
        return $html;
    }
}
