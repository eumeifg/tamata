<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_Cms
 * @copyright 2019 (c) KrishTechnolabs (https://www.krishtechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace Ktpl\Cms\Api;

/**
 * Build home page data from different sources.
 */
interface HomePageBuilderInterface
{
    /**
     * @return \Ktpl\Cms\Api\Data\HomePageInterface
     */
    public function build();
}
