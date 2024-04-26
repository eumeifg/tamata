<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Plugin;

/**
 * Class TemplatePlugin
 *
 * @package Ktpl\SearchAutocomplete\Plugin
 */
class TemplatePlugin
{
    /**
     * Add minimum search length to layout
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param $html
     * @return string
     */
    public function afterFetchView($subject, $html)
    {
        if ($subject->getTemplate() == 'Magento_Search::form.mini.phtml') {
            $html = str_replace(
                '"formSelector":"#search_mini_form",',
                '"formSelector":"#search_mini_form","minSearchLength": 10000,',
                $html
            );
        }

        return $html;
    }
}