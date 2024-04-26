<?php

namespace Magedelight\Vendor\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InstallDataPatch implements DataPatchInterface, PatchRevertableInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * Page factory
     *
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $pageFactory
     * @param BlockFactory $blockFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PageFactory $pageFactory,
        BlockFactory $blockFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        /**
        * If before, we pass $setup as argument in install/upgrade function, from now we start
        * inject it with DI. If you want to use setup, you can inject it, with the same way as here
        */
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /* Rating Type script Start. */
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('rating_entity'),
            ['entity_code' => 'vendor']
        );
        /* Rating Type script ends. */
        /*The code that you want apply in the patch
        Please note, that one patch is responsible only for one setup version
        So one UpgradeData can consist of few data patches*/
        $this->moduleDataSetup->getConnection()->endSetup();

        $fQuesiton = <<<EOD
            <div class="footer-links col-md-6">
                 <p>Lorem Ipsum is simply dummy text of the printing and
				typesetting industry. Lorem Ipsum has been the industry's
				standard dummy text ever since the 1500s, when an unknown
				printer took a galley of type and scrambled it to make a type
				specimen book. It has survived not only five centuries.</p>
            </div>
EOD;

        $benifiets = <<<EOD
            <div class="footer-links col-md-6">
                 <p>Lorem Ipsum is simply dummy text of the printing and
				typesetting industry. Lorem Ipsum has been the industry's
				standard dummy text ever since the 1500s, when an unknown
				printer took a galley of type and scrambled it to make a type
				specimen book. It has survived not only five centuries.</p>
            </div>
EOD;

        $policy = <<<EOD
            <div class="footer-links col-md-6">
                 <p>Lorem Ipsum is simply dummy text of the printing and
				typesetting industry. Lorem Ipsum has been the industry's
				standard dummy text ever since the 1500s, when an unknown
				printer took a galley of type and scrambled it to make a type
				specimen book. It has survived not only five centuries.</p>
            </div>
EOD;

        $termOfUse = <<<EOD
            <div class="footer-links col-md-6">
                 <p>Lorem Ipsum is simply dummy text of the printing and
				typesetting industry. Lorem Ipsum has been the industry's
				standard dummy text ever since the 1500s, when an unknown
				printer took a galley of type and scrambled it to make a type
				specimen book. It has survived not only five centuries.</p>
            </div>
EOD;

        $packing = <<<EOD
            <div class="footer-links col-md-6">
                 <p>Lorem Ipsum is simply dummy text of the printing and
				typesetting industry. Lorem Ipsum has been the industry's
				standard dummy text ever since the 1500s, when an unknown
				printer took a galley of type and scrambled it to make a type
				specimen book. It has survived not only five centuries.</p>
            </div>
EOD;

        $support = <<<EOD
            <div class="footer-links col-md-6">
                 <p>Lorem Ipsum is simply dummy text of the printing and
				typesetting industry. Lorem Ipsum has been the industry's
				standard dummy text ever since the 1500s, when an unknown
				printer took a galley of type and scrambled it to make a type
				specimen book. It has survived not only five centuries.</p>
            </div>
EOD;

        $cmsPages = [
            [
                'title' => 'Vendor: Frequently Asked Questions',
                'page_layout' => '1column',
                'identifier' => 'vendor-faq',
                'content_heading' => 'Frequently Asked Questions',
                'content' => $fQuesiton,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Vendor: Benefits',
                'page_layout' => '1column',
                'identifier' => 'vendor-benefits',
                'content_heading' => 'Benefits',
                'content' => $benifiets,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Vendor: Privacy Policy',
                'page_layout' => '1column',
                'identifier' => 'vendor-privacy-policy',
                'content_heading' => 'Privacy Policy',
                'content' => $policy,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Vendor: Terms of Use',
                'page_layout' => '1column',
                'identifier' => 'vendor-terms-of-use',
                'content_heading' => 'Terms of Use',
                'content' => $termOfUse,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Vendor: Packing Guidelines',
                'page_layout' => '1column',
                'identifier' => 'vendor-packing-guidelines',
                'content_heading' => 'Packing Guidelines',
                'content' => $packing,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Marketplace: Support',
                'page_layout' => '1column',
                'identifier' => 'support',
                'content_heading' => 'Marketplace Support',
                'content' => $support,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ]
        ];

        /**
         * Insert default and system pages
         */
        foreach ($cmsPages as $data) {
            $this->pageFactory->create()->setData($data)->save();
        }

        $footerLinksContent = <<<EOD
            <div class="footer-links col-md-6">
            <ul class="links">
            <li><a title="Privacy Policy" href="{{store url='vendor-privacy-policy' _nosid='true'}}">Privacy Policy</a></li>
            <li><a title="Terms of Use" href="{{store url='vendor-terms-of-use' _nosid='true'}}">Terms of Use</a></li>
            <li><a title="Packing Guidelines" href="{{store url='vendor-packing-guidelines' _nosid='true'}}">Packing Guidelines</a></li>
            <li><a title="Help &amp; FAQs" href="{{store url='vendor-faq' _nosid='true'}}">Help &amp; FAQs</a></li></ul></div>
EOD;

        $vendorTopLinks = <<<EOD
            <div class="sections vendor-static-menu-sections">
            <div class="section-items vendor-static-menu-section-items">
            <div class="section-item-content vendor-static-menu-section-item-content">
            <div class="vendor-static-menu panel">
            <div class="navigation static-menu">
            <ul class="ui-menu ui-widget ui-widget-content ui-corner-all">
            <li class="level0 nav-1 first level-top ui-menu-item"><a class="level-top ui-corner-all" href="{{store url=''}}"><span>Home</span></a></li>
            <li class="level0 nav-1 first level-top ui-menu-item"><a class="level-top ui-corner-all" href="{{store url='vendor-faq'}}"><span>FAQs</span></a></li>
            <li class="level0 nav-1 first level-top ui-menu-item"><a class="level-top ui-corner-all" href="{{store url='vendor-benefits'}}"><span>Benefits</span></a></li></ul></div></div></div></div></div>
EOD;

        $vendorLoginHeader = <<<EOD
            <div class="sections vendor-login-header-sections">
            <div class="section-items vendor-login-header-section-items">
            <div class="section-item-content vendor-login-header-section-item-content">
            <div class="vendor-login-header panel">
            <div class="vendor-login-logo-container"><img src="{{view url="images/logo.svg"}}" alt="" /></div>
            <div class="vendor-login-form-container">{{block class="Magedelight\Vendor\Block\Sellerhtml\Form\Login" name="vendor_form_login" template="Magedelight_Vendor::form/login.phtml" }}</div></div></div></div></div>
EOD;

        $cmsBlocks = [
            [
                'title' => 'Vendor: Footer Links',
                'identifier' => 'vendor-footer-links',
                'content' => $footerLinksContent,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Vendor: Top Links',
                'identifier' => 'vendor-top-links',
                'content' => $vendorTopLinks,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ],
            [
                'title' => 'Vendor: Login Header',
                'identifier' => 'vendor-login-header',
                'content' => $vendorLoginHeader,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ]
        ];

        /**
         * Insert default and system pages
         */
        foreach ($cmsBlocks as $data) {
            $this->blockFactory->create()->setData($data)->save();
        }

        $orderStatusdata = [];
        $orderStatuses = [
            /*'confirmed' => __('Confirmed'),*/
            'packed' => __('Packed'),
            'shipped' => __('Shipment Generated'),
            'delivered' => __('Delivered')
        ];
        foreach ($orderStatuses as $code => $info) {
            $orderStatusdata[] = ['status' => $code, 'label' => $info];
        }
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            $orderStatusdata
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        /**
        * This is dependency to another patch. Dependency should be applied first
        * One patch can have few dependencies
        * Patches do not have versions, so if in old approach with Install/Ugrade data scripts you used
        * versions, right now you need to point from patch with higher version to patch with lower version
        * But please, note, that some of your patches can be independent and can be installed in any sequence
        * So use dependencies only if this important for you
        */
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $whwe = "entity_code = 'vendor'";
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('rating_entity'),
            $whwe
        );
        /*Here should go code that will revert all operations from `apply` method
        Please note, that some operations, like removing data from column, that is in role of foreign key reference
        is dangerous, because it can trigger ON DELETE statement*/
        $this->moduleDataSetup->getConnection()->endSetup();

        $cmsPages = [
            ['identifier' => 'vendor-faq'],
            ['identifier' => 'vendor-benefits'],
            ['identifier' => 'vendor-privacy-policy'],
            ['identifier' => 'vendor-terms-of-use'],
            ['identifier' => 'vendor-packing-guidelines'],
            ['identifier' => 'support']
        ];

        /**
         * Insert default and system pages
         */
        foreach ($cmsPages as $data) {
            $newPage = $this->pageFactory->create()->load($data['identifier'], 'identifier');
            $newPage->delete();
        }

        $cmsBlocks = [
            ['identifier' => 'vendor-footer-links'],
            ['identifier' => 'vendor-top-links'],
            ['identifier' => 'vendor-login-header']
        ];

        /**
         * Insert default and system pages
         */
        foreach ($cmsBlocks as $data) {
            $newBlock = $this->blockFactory->create()->load($data['identifier'], 'identifier');
            $newBlock->delete();
        }

        $orderStatuses = [
            /*'confirmed' => __('Confirmed'),*/
            'packed' => __('Packed'),
            'shipped' => __('Shipment Generated'),
            'delivered' => __('Delivered')
        ];
        foreach ($orderStatuses as $code => $info) {
            /* $orderStatusdata[] = ['status' => $code, 'label' => $info];*/
            $format = 'status = \'%1$s\' AND label = \'%2$s\'';
            $whw = sprintf($format, $code, $info);
            $this->moduleDataSetup->getConnection()->delete(
                $this->moduleDataSetup->getTable('sales_order_status'),
                $whw
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        /**
        * This internal Magento method, that means that some patches with time can change their names,
        * but changing name should not affect installation process, that's why if we will change name of the patch
        * we will add alias here
        */
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
