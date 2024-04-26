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
 * Interface providing Catalogue Upload by CSV
 */
interface CatalogCsvInterface
{
    /**
     * Upload products by CSV.
     *
     * @param int $sellerId
     * @param int $categoryId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upload($sellerId, $categoryId);
}
