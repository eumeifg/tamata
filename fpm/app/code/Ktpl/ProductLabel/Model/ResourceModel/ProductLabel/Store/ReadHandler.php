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

namespace Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\Store;

class ReadHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    public function execute($entity, $arguments = [])
    {
        $resource = $entity->getResource();

        $stores = $resource->getStoreIds($entity);
        $entity->setData('store_id', $stores);
        $entity->setData('stores', $stores);

        return $entity;
    }
}
