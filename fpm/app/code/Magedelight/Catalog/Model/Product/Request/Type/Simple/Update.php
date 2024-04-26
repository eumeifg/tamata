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
namespace Magedelight\Catalog\Model\Product\Request\Type\Simple;

class Update extends \Magedelight\Catalog\Model\Product\Request\Type\Simple
{
    /**
     *
     * @param integer $vendorId
     * @param array $requestData
     * @param string $productType
     * @throws \Exception
     */
    public function execute($vendorId, $requestData, $productType = 'simple')
    {
        $this->updateProductRequest($vendorId, $requestData, $productType);
    }
}
