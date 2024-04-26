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
 * Interface for retrieval information about vendor attributes metadata.
 */
interface VendorMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_VENDOR = 1;

    const ENTITY_TYPE_VENDOR = 'vendor';

    const DATA_INTERFACE_NAME = \Magedelight\Vendor\Api\Data\VendorInterface::class;
}
