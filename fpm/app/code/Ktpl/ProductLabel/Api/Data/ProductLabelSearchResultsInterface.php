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

namespace Ktpl\ProductLabel\Api\Data;

interface ProductLabelSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Product label list.
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelInterface[]
     */
    public function getItems();

    /**
     * Set Product label list.
     *
     * @param \Ktpl\ProductLabel\Api\Data\ProductLabelInterface[] $items list of products labels

     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelSearchResultsInterface
     */
    public function setItems(array $items);
}
