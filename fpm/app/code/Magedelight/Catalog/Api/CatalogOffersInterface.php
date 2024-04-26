<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api;

/**
 * Interface providing Catalogue Offer update from csv by Seller
 */
interface CatalogOffersInterface
{
    /**
     * Upload csv of product's offer update by seller ID.
     *
     * @param int $sellerId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upload($sellerId);
}
