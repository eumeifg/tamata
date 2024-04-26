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

class AttributeOptions implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    private $locator;

    private $attributeRepository;

    public function __construct(
        \Ktpl\ProductLabel\Model\ProductLabel\Locator\LocatorInterface $locator,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->locator             = $locator;
        $this->attributeRepository = $attributeRepository;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $productLabel = $this->locator->getProductLabel();

        $options = [];
        if ($productLabel && $productLabel->getAttributeId()) {
            $options = $this->getAttributeOptions((int) $productLabel->getAttributeId());
        }

        $meta['general']['children']['option_id']['arguments']['data']['options']    = $options;
        $meta['general']['children']['option_label']['arguments']['data']['options'] = $options;

        $isNew          = (!$productLabel || !$productLabel->getId());
        $optionFieldVisible = $isNew && $productLabel && $productLabel->getAttributeId();

        $meta['general']['children']['option_id']['arguments']['data']['config']['disabled'] = !$isNew;
        $meta['general']['children']['option_id']['arguments']['data']['config']['visible']  = $optionFieldVisible;

        $meta['general']['children']['option_label']['arguments']['data']['config']['disabled'] = $isNew;
        $meta['general']['children']['option_label']['arguments']['data']['config']['visible']  = !$isNew;

        return $meta;
    }

    private function getAttributeOptions($attributeId)
    {
        $attribute = $this->attributeRepository->get($attributeId);
        $options   = [];

        if ($attribute && $attribute->getAttributeId() && $attribute->getSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }

        return $options;
    }
}
