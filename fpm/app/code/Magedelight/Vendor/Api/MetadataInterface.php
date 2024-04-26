<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Api;

/**
 * Interface for getting attributes metadata. Note that this interface should not be used directly, use its children.
 */
interface MetadataInterface extends \Magento\Framework\Api\MetadataServiceInterface
{
    /**
     * Retrieve all attributes filtered by form code
     *
     * @api
     * @param string $formCode
     * @return \Magedelight\Vendor\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributes($formCode);

    /**
     * Retrieve attribute metadata.
     *
     * @api
     * @param string $attributeCode
     * @return \Magedelight\Vendor\Api\Data\AttributeMetadataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeMetadata($attributeCode);

    /**
     * Get all attribute metadata.
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllAttributesMetadata();

    /**
     *  Get custom attributes metadata for the given data interface.
     *
     * @api
     * @param string $dataInterfaceName
     * @return \Magedelight\Vendor\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomAttributesMetadata($dataInterfaceName = '');
}
