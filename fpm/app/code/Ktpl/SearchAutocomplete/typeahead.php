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

// phpcs:ignoreFile
namespace Ktpl\SearchAutocomplete;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = dirname(dirname(dirname(__DIR__))) . '/etc/typeahead.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/typeahead.json';
}

if (!file_exists($configFile)) {
    return \Zend_Json::encode([]);
}

$config = \Zend_Json::decode(file_get_contents($configFile));

class TypeAheadAutocomplete
{
    private $config;

    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function process()
    {
        $query = $this->getQueryText();
        $query = substr($query, 0, 2);
        return isset($this->config[$query])?$this->config[$query]:'';
    }

    private function getQueryText()
    {
        return filter_input(INPUT_GET, 'q') !== null ? filter_input(INPUT_GET, 'q') : '';
    }
}

$result = (new \TypeAheadAutocomplete($config))->process();

//s start
exit(\Zend_Json::encode($result));
//s end
/** m start
return \Zend_Json::encode($result);
m end */ 
