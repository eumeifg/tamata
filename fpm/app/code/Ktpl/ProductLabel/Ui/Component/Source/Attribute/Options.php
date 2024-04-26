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

namespace Ktpl\ProductLabel\Ui\Component\Source\Attribute;

use \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\Attributes\CollectionFactory;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    private $attributesCollectionFactory;

    private $attributesList;

    public function __construct(CollectionFactory $attributesCollectionFactory)
    {
        $this->attributesCollectionFactory = $attributesCollectionFactory;
    }

    public function toOptionArray()
    {
        return $this->getAttributesList();
    }

    private function getAttributesList()
    {
        if (null === $this->attributesList) {
            $this->attributesList = [];

            $collection = $this->attributesCollectionFactory->create();

            foreach ($collection as $attribute) {
                $this->attributesList[$attribute->getId()] = [
                    'value' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                ];
            }
        }

        return $this->attributesList;
    }
}
