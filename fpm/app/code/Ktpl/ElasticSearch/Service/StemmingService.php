<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Service;

use Ktpl\ElasticSearch\Api\Service\Stemming\StemmerInterface;
use Ktpl\ElasticSearch\Api\Service\StemmingServiceInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;

/**
 * Class StemmingService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class StemmingService implements StemmingServiceInterface
{
    /**
     * @var StemmerInterface[]
     */
    private $stemmers;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * StemmingService constructor.
     *
     * @param LocaleResolver $localeResolver
     * @param array $stemmers
     */
    public function __construct(
        LocaleResolver $localeResolver,
        array $stemmers = []
    )
    {
        $this->localeResolver = $localeResolver;
        $this->stemmers = $stemmers;
    }

    /**
     * Singularize
     *
     * @param string $string
     * @return string
     */
    public function singularize($string)
    {
        // string is too short
        if (strlen($string) < 3) {
            return $string;
        }

        $locale = strtolower(explode('_', $this->localeResolver->getLocale())[0]);

        if (array_key_exists($locale, $this->stemmers)) {
            return $this->stemmers[$locale]->singularize($string);
        }

        return $string;
    }
}
