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

namespace Ktpl\ProductLabel\Ui\Component\ProductLabel\Form\Modifier;

use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;

class Attribute implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    private $locator;

    public function __construct(
        \Ktpl\ProductLabel\Model\ProductLabel\Locator\LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

    public function modifyData(array $data)
    {
        $productLabel = $this->locator->getProductLabel();

        // @codingStandardsIgnoreStart
        if ($productLabel && $productLabel->getId() && isset($data[$productLabel->getId()][ProductLabelInterface::ATTRIBUTE_ID])) {
            $data[$productLabel->getId()]['attribute_label'] = $data[$productLabel->getId()][ProductLabelInterface::ATTRIBUTE_ID];
        }
        // @codingStandardsIgnoreEnd

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $productLabel = $this->locator->getProductLabel();

        $isNew = (!$productLabel || !$productLabel->getId());

        $meta['general']['children']['attribute_id']['arguments']['data']['config']['disabled'] = !$isNew;
        $meta['general']['children']['attribute_id']['arguments']['data']['config']['visible']  = $isNew;

        $meta['general']['children']['attribute_label']['arguments']['data']['config']['disabled'] = $isNew;
        $meta['general']['children']['attribute_label']['arguments']['data']['config']['visible']  = !$isNew;

        return $meta;
    }
}
