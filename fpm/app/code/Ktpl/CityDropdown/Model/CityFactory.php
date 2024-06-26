<?php

namespace Ktpl\CityDropdown\Model;

use Ktpl\CityDropdown\Api\Data\CityInterface;
use Magento\Framework\ObjectManagerInterface;

class CityFactory implements CityFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * CityFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = CityInterface::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}