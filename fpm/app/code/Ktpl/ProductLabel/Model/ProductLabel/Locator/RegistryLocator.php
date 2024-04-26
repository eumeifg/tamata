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

class RegistryLocator implements LocatorInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var null
     */
    private $productLabel = null;

    /**
     * RegistryLocator constructor.
     *
     * @param \Magento\Framework\Registry $registry Registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    public function getProductLabel()
    {
        if (null !== $this->productLabel) {
            return $this->productLabel;
        }

        if ($productLabel = $this->registry->registry('current_productlabel')) {
            return $this->productLabel = $productLabel;
        }

        return null;
    }
}
