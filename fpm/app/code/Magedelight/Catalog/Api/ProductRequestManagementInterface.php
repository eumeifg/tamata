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

interface ProductRequestManagementInterface
{
    /**#@-*/

    /**
     * Create vendor product request. Perform necessary business operations like sending email.
     *
     * @api
     * @param int $vendorId
     * @param array $requestData
     * @param type $isNew
     * @return \Magedelight\Catalog\Api\ProductManagementInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\ValidatorException
     * @throws \Exception
     */
    
    public function createProductRequest($vendorId, $requestData, $isNew = true);
}
