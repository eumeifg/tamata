<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

if (isset($_SERVER)
    && is_array($_SERVER)
    && isset($_SERVER['REQUEST_URI'])
    && strpos($_SERVER['REQUEST_URI'], 'searchautocomplete/ajax/suggest') !== false
) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/autocomplete.json';
    if (!@file_exists($configFile)) { //file_exists may not work if open_basedir restriction in effect..
        //module is installed in app/code/Ktpl
        $configFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/app/etc/autocomplete.json';
    }
    if (@file_exists($configFile)) {
        require_once 'autocomplete.php';
    }
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Ktpl_SearchElastic',
    __DIR__
);
