<?php
namespace CAT\VIP\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Model\GroupFactory;

class InstallData implements InstallDataInterface
{

    protected $groupFactory;

    /**
     * Constructor
     *
     * @param Magento\Customer\Model\GroupFactory $groupFactory
     */
    public function __construct(

        GroupFactory $groupFactory
    ) {

        $this->groupFactory = $groupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {


        /** Create a customer Group */
        /** @var \Magento\Customer\Model\Group $group */
        $setup->startSetup();

	    /* Create a multiple customer group */
        $setup->getConnection()->insertForce($setup->getTable('customer_group'),['customer_group_code' => 'VIP Customer', 'tax_class_id' => 3]);

        $setup->endSetup();
    }
}