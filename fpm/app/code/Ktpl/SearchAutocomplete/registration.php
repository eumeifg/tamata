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

if (isset($_SERVER)
    && is_array($_SERVER)
    && isset($_SERVER['REQUEST_URI'])) {

    if (strpos($_SERVER['REQUEST_URI'], 'searchautocomplete/ajax/typeahead') !== false) {
        require_once 'typeahead.php';
    } elseif (strpos($_SERVER['REQUEST_URI'], 'search/ajax/suggest') !== false) {
        return \Zend_Json::encode([]);
    }
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Ktpl_SearchAutocomplete',
    __DIR__
);
