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
namespace Magedelight\Vendor\Plugin\Model\Microsite\Store;

use Magedelight\Vendor\Model\MicrositeFactory;
use Magedelight\Vendor\Model\MicrositeUrlRewriteGenerator;
use Magento\Framework\Model\AbstractModel;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Plugin which is listening store resource model and on save or on delete replace catalog url rewrites
 *
 * @see \Magento\Store\Model\ResourceModel\Store
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View
{

    /**
     * @var MicrositeFactory
     */
    protected $micrositeFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var MicrositeUrlRewriteGenerator
     */
    protected $micrositeUrlRewriteGenerator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Vendor\Model\Microsite\Copier
     */
    protected $micrositeCopier;

    /**
     * @var AbstractModel
     */
    private $origStore;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param MicrositeFactory $micrositeFactory
     * @param MicrositeUrlRewriteGenerator $micrositeUrlRewriteGenerator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        MicrositeFactory $micrositeFactory,
        MicrositeUrlRewriteGenerator $micrositeUrlRewriteGenerator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\Microsite\Copier $micrositeCopier
    ) {
        $this->urlPersist = $urlPersist;
        $this->micrositeFactory = $micrositeFactory;
        $this->micrositeUrlRewriteGenerator = $micrositeUrlRewriteGenerator;
        $this->storeManager = $storeManager;
        $this->micrositeCopier = $micrositeCopier;
    }

    /**
     * @param \Magento\Store\Model\ResourceModel\Store $object
     * @param AbstractModel $store
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Store\Model\ResourceModel\Store $object,
        AbstractModel $store
    ) {
        $this->origStore = $store;
    }

    /**
     * Regenerate urls on store after save
     *
     * @param \Magento\Store\Model\ResourceModel\Store $object
     * @param \Magento\Store\Model\ResourceModel\Store $store
     * @return \Magento\Store\Model\ResourceModel\Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Store\Model\ResourceModel\Store $object,
        \Magento\Store\Model\ResourceModel\Store $store
    ) {
        if ($this->origStore->isObjectNew() || $this->origStore->dataHasChangedFor('group_id')) {
            $this->urlPersist->replace(
                $this->generateMicrositeUrls(
                    $this->origStore->getId()
                )
            );
        }
        return $store;
    }

    /**
     * Generate url rewrites for microsites
     *
     * @param int $storeId
     * @return array
     */
    protected function generateMicrositeUrls($storeId)
    {
        $urls = [];
        $collection = $this->micrositeFactory->create()
            ->getCollection()
            ->addFieldToFilter('store_id', $this->getDefaultStoreId())
            ->addFieldToSelect(['*']);
        foreach ($collection as $microsite) {
            $microsite->setStoreId($storeId);
            $this->micrositeCopier->copy($microsite, $storeId);
            /** @var \Magedelight\Vendor\Model\Microsite $microsite */
            $urls = array_merge(
                $urls,
                $this->micrositeUrlRewriteGenerator->generate($microsite)
            );
        }
        return $urls;
    }

    /**
     * @return string|int
     */
    public function getDefaultStoreId()
    {
        return $this->storeManager->getDefaultStoreView()->getStoreId();
    }
}
