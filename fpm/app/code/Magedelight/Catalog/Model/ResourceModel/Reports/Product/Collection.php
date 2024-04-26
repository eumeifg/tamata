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
namespace Magedelight\Catalog\Model\ResourceModel\Reports\Product;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Reports for vendor product
 * @author Rocket Bazaar Core Team
 * Created at 15 Feb, 2016 10:58:27 AM
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magedelight\Catalog\Model\ResourceModel\Product\Collection
{
    const SELECT_COUNT_SQL_TYPE_CART = 1;

    /**
     * Product entity identifier
     *
     * @var int
     */
    protected $_productEntityId;

    /**
     * Product entity table name
     *
     * @var string
     */
    protected $_productEntityTableName;

    /**
     * @var \Magento\Reports\Model\Event\TypeFactory
     */
    private $_eventTypeFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Criteria $criteriaConfig
     * @param TimezoneInterface $timezone
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Criteria $criteriaConfig,
        TimezoneInterface $timezone,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_eventTypeFactory = $eventTypeFactory;
        $this->setProductEntityId($product->getEntityIdField());
        $this->setProductEntityTableName($product->getEntityTable());
        $this->authSession = $authSession;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $criteriaConfig,
            $timezone,
            $connection,
            $resource
        );
    }

    /**
     * Set product entity id
     * @codeCoverageIgnore
     *
     * @param string $entityId
     * @return $this
     */
    public function setProductEntityId($entityId)
    {
        $this->_productEntityId = (int)$entityId;
        return $this;
    }

    /**
     * Get product entity id
     * @codeCoverageIgnore
     *
     * @return int
     */
    public function getProductEntityId()
    {
        return $this->_productEntityId;
    }

    /**
     * Set product entity table name
     * @codeCoverageIgnore
     *
     * @param string $value
     * @return $this
     */
    public function setProductEntityTableName($value)
    {
        $this->_productEntityTableName = $value;
        return $this;
    }

    /**
     * Get product entity table name
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getProductEntityTableName()
    {
        return $this->_productEntityTableName;
    }

    /**
     * get Low inventoary products collection of logged in vendor
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateLowInventoryProducts()
    {
        $model = \Magento\Framework\App\ObjectManager::getInstance()->create($this->getModelName());
        $this->addFieldToFilter('main_table.vendor_id', ['eq' => $this->authSession->getUser()->getVendorId()]);
        $this->getSelect()->joinLeft(
            ['rbvpw' => $this->getResource()->getTable('md_vendor_product_website')],
            "main_table.vendor_product_id = rbvpw.vendor_product_id",
            ['rbvpw.website_id','rbvpw.status']
        );
        $this->addFieldToFilter('rbvpw.website_id', ['eq' => $this->storeManager->getStore()->getWebsiteId()]);

        return $this;
    }

    /**
     * Add views count
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        $eventTypes = $this->_eventTypeFactory->create()->getCollection();
        foreach ($eventTypes as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = (int)$eventType->getId();
                break;
            }
        }

        $this->getSelect()->reset()->from(
            ['report_table_views' => $this->getTable('report_event')],
            ['views' => 'COUNT(report_table_views.event_id)']
        )->join(
            ['e' => $this->getProductEntityTableName()],
            'e.entity_id = report_table_views.object_id'
        )->where(
            'report_table_views.event_type_id = ?',
            $productViewEvent
        )->group(
            'e.entity_id'
        )->order(
            'views ' . self::SORT_ORDER_DESC
        )->having(
            'COUNT(report_table_views.event_id) > ?',
            0
        );

        if ($from != '' && $to != '') {
            $this->getSelect()->where('logged_at >= ?', $from)->where('logged_at <= ?', $to);
        }
        return $this;
    }
}
