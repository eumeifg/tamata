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

namespace Ktpl\SearchAutocomplete\Plugin\Framework\UrlFactory;

use Magento\Framework\Url;

/**
 * Class UrlBuilderPlugin
 *
 * @package Ktpl\SearchAutocomplete\Plugin\Framework\UrlFactory
 */
class UrlBuilderPlugin
{
    /**
     * @var Url
     */
    private $url;

    /**
     * UrlBuilderPlugin constructor.
     *
     * @param Url $url
     */
    public function __construct(
        Url $url
    )
    {
        $this->url = $url;
    }

    /**
     * Add url
     *
     * @param $subject
     * @param $result
     * @return Url
     */
    public function afterCreate($subject, $result)
    {
        if (php_sapi_name() === 'cli') {
            return $this->url;
        }

        return $result;
    }
}