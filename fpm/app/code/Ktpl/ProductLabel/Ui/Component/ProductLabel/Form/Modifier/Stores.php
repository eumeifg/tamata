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

class Stores implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
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

        if ($productLabel
            && $productLabel->getId()
            && !empty($productLabel->getStores())
            && empty($data[$productLabel->getId()]['store_id'])
        ) {
            $data[$productLabel->getId()]['store_id'] = $productLabel->getStores();
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
