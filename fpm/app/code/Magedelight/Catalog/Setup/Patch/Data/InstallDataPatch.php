<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallDataPatch implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    private $catalogSetupFactory;

    private $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->catalogSetupFactory = $categorySetupFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'thumbnail',
            [
                'group'         => 'General Information',
                'input'         => 'image',
                'type'          => 'varchar',
                'label'         => 'Thumbnail',
                'backend'       => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'visible'       => true,
                'required'      => false,
                'visible_on_front' => true,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'user_defined' => true,
            ]
        );

        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'small_image',
            [
                'group' => 'General Information',
                'input' => 'image',
                'type' => 'varchar',
                'label' => 'Category Icon',
                'backend' => Image::class,
                'visible' => true,
                'required' => false,
                'visible_on_front' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'user_defined' => true
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();

        $guideline = <<<EOD
            <h3><u>Product images style guideline</u></h3>
                <p>
                Listings that are missing a main image will not appear in search or browse until you fix the listing.
                </p>
                <p>Choose images that are clear, information-rich, and attractive.</p>
                <p><strong>Images must meet the following requirements:</strong></p>
                <ul>
                <li>Images must be of following dimensions, 
                <strong>
                {{config path="vendor_product/validation/image_width"}}px x 
                {{config path="vendor_product/validation/image_height"}}px
                </strong>
                </li>
                <li>Maximum allowed size<strong>{{config path="vendor_product/validation/image_size"}} KB</strong></li>
                <li>Products must fill at least 85% of the image. Images must show only the product that is for sale,
                 with few or no props and with no logos, watermarks, or inset images.
                  Images may only contain text that is a part of the product.</li>
                <li>Main images must have a pure white background, must be a photo (not a drawing), 
                and must not contain excluded accessories.</li>
                <li>JPEG is the preferred image format, JPG, JPEG and PNG are the acceptable image types</li>
                </ul>
EOD;
        $cmsBlocks = [
            [
                'title' => 'Vendor: Product Image Guideline',
                'identifier' => 'product-image-guideline',
                'content' => $guideline,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ]
        ];

        foreach ($cmsBlocks as $data) {
            $this->blockFactory->create()->setData($data)->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    public function revert()
    {
        $cmsBlocks = [
            ['identifier' => 'product-image-guideline']
        ];

        foreach ($cmsBlocks as $data) {
            $newBlock = $this->blockFactory->create()->load($data['identifier'], 'identifier');
            $newBlock->delete();
        }

        $this->moduleDataSetup->getConnection()->startSetup();

        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $catalogSetup->removeAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'thumbnail'
        );

        $catalogSetup->removeAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'small_image'
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getVersion()
    {
        return '1.0.0';
    }
}
