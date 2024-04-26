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

namespace Ktpl\ProductLabel\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

class ProductLabelStoreFilter implements \Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface
{
    public function apply(\Magento\Framework\Api\Filter $filter, \Magento\Framework\Data\Collection\AbstractDb $collection)
    {
        /**
         * @var \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\Collection $collection
        */
        $collection->addStoreFilter($filter->getValue());

        return true;
    }
}
