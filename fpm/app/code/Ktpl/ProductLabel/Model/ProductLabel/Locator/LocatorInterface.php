<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ProductLabel
 * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ProductLabel\Model\ProductLabel\Locator;

interface LocatorInterface
{
    /**
     * Retrieve current product label.
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelInterface
     */
    public function getProductLabel();
}
