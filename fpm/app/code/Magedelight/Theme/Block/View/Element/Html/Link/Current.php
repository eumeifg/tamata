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
namespace Magedelight\Theme\Block\View\Element\Html\Link;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $highlight = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        if ($this->isCurrent()) {
            $html = '<li class="nav item current">';
            $html .= '<span class="'.$this->getIconClass().'" >'.$this->getIconLabel().'</span>';
            $html .= '<strong>'
                . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()))
                . '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . '">'
                    . '<a href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle()
                ? ' title="' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getTitle())) . '"'
                : '';
            $html .= $this->getAttributesHtml() . '>';
            $html .= '<span class="'.$this->getIconClass().'" >'.$this->getIconLabel().'</span>';
            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }
            
            $html .= $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()));

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }

        return $html;
    }
}
