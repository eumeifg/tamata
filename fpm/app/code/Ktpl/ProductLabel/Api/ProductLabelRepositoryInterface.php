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

namespace Ktpl\ProductLabel\Api;

interface ProductLabelRepositoryInterface
{
    /**
     * Retrieve a product label by its id
     *
     * @param int $objectId Id of the product label
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelInterface
     */
    public function getById($objectId);

    /**
     * Retrieve a product label by its identifier.
     *
     * @param string $objectIdentifier Identifier of the product label
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelInterface
     */
    public function getByIdentifier($objectIdentifier);

    /**
     * Retrieve list of product label
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria Search criteria
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Save a product label
     *
     * @param \Ktpl\ProductLabel\Api\Data\ProductLabelInterface $plabel product label
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelInterface
     */
    public function save(\Ktpl\ProductLabel\Api\Data\ProductLabelInterface $plabel);

    /**
     * Delete a product label by given ID
     *
     * @param int $plabelId Id of the product label.
     */
    public function deleteById($plabelId);

    /**
     * Delete a product label by its identifier.
     *
     * @param string $plabel Identifier of the product label
     */
    public function deleteByIdentifier($plabel);
}
