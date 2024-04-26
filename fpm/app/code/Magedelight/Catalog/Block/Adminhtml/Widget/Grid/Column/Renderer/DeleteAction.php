<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magedelight\Catalog\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Grid column widget for rendering action grid cells
 *
 */
class DeleteAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Renders column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $html .= '<div class="admin__control-table">';
        $url = $this->getUrl('rbcatalog/offer/delete', ['id' => $row->getId()]);
        $html .= '<a class="action-delete" href="' . $url . '">';
        $html .= '</a>';
        $html .= '</div>';
        return $html;
    }
}
