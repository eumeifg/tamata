<?php

namespace CAT\Address\Cron;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class RemoveAddress
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ResourceConnection $resourceConnection
     * @param AddressRepositoryInterface $addressRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ResourceConnection $resourceConnection, ScopeConfigInterface $scopeConfig, AddressRepositoryInterface $addressRepository)
    {
        $this->resourceConnection = $resourceConnection;
        $this->addressRepository = $addressRepository;
        $this->scopeConfig = $scopeConfig;
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function removeAddress()
    {
        if ($addresses = $this->getAddress()) {
            foreach ($addresses as $addressId) {
                if ($addressId) {
                    $this->deleteCustomerAddressById($addressId);
                }
            }
        }
    }

    /**
     * @return array|false
     */
    private function getAddress()
    {
        $sql = $this->connection->select()->from('customer_address_entity', 'entity_id')
            ->where('region_id IS NULL')
            ->orWhere('region_id=?', 0)->limit($this->getBatchCount());
        $result = $this->connection->fetchCol($sql);
        if (count($result) > 0) {
            return $result;
        }
        return false;
    }

    /**
     * @param int $addressId
     * @return void
     * @throws Exception
     */
    private function deleteCustomerAddressById(int $addressId): void
    {
        try {
            $this->addressRepository->deleteById($addressId);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return int|mixed
     */
    private function getBatchCount()
    {
        $batch = $this->scopeConfig->getValue('cat_address_city/general/batch_count', ScopeInterface::SCOPE_STORE);
        return empty($batch) ? 1000 : $batch;
    }
}
