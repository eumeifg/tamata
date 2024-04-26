<?php
namespace MDC\Vendor\Plugin;

class HtmlAttribute extends \Magedelight\Vendor\Plugin\HtmlAttribute
{
    public function afterGetHtmlAttributes(\Magento\Framework\Data\Form\Element\Text $subject, $result)
    {
        $result[] = 'autofocus';
        return $result;
    }
}
