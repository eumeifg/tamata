<?php declare(strict_types=1);

/**
 * RemoveExtraTable Patch Class
 *
 * Setup Patch data class to remove
 * table image_storage_tmp
 */

namespace Magedelight\Abandonedcart\Setup\Patch\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class RemoveExtraTable
 */
class RemoveBlackListStores implements DataPatchInterface, PatchVersionInterface
{
    /**
     * table entity value
     */
    const BLACKLIST_STORES = 'md_abandonedcart_email_black_list_stores';

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.5';
    }

    public function apply(): void
    {
        $installer = $this->schemaSetup;
        $installer->startSetup();
 
        if ($installer->tableExists(self::BLACKLIST_STORES)) {
            $installer->getConnection()->dropTable($installer->getTable(self::BLACKLIST_STORES));
        }
 
        $installer->endSetup();
    }
}
