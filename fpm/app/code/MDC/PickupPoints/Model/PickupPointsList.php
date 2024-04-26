<?php

declare (strict_types=1);

namespace MDC\PickupPoints\Model;

use MDC\PickupPoints\Api\Data\PickupPointsDataInterface;
use MDC\PickupPoints\Api\PickupPointsListInterface;
use MDC\PickupPoints\Helper\Data;
use MDC\PickupPoints\Model\Data\PickupPointsDataFactory;
use MDC\PickupPoints\Model\Data\PickupPointsObjFactory;

class PickupPointsList implements PickupPointsListInterface
{

    /**
     * @var PickupsFactory
     */
    protected $pickupsModelFactory;
    /**
     * @var PickupPointsDataFactory
     */
    protected $pickupPointsData;
    /**
     * @var PickupPointsObjFactory
     */
    protected $pickupPointsObj;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param PickupsFactory $pickupsModelFactory
     * @param PickupPointsDataFactory $pickupPointsData
     * @param PickupPointsObjFactory $pickupPointsObj
     * @param Data $helper
     */
    function __construct(
        PickupsFactory          $pickupsModelFactory,
        PickupPointsDataFactory $pickupPointsData,
        PickupPointsObjFactory  $pickupPointsObj,
        Data                    $helper
    )
    {
        $this->pickupsModelFactory = $pickupsModelFactory;
        $this->pickupPointsData = $pickupPointsData;
        $this->pickupPointsObj = $pickupPointsObj;
        $this->helper = $helper;
    }


    /**
     * List of pickup points added by admin
     * @return PickupPointsDataInterface
     */
    public function getPickupPointsList(): PickupPointsDataInterface
    {
        $pickupPointsEnabled = $this->helper->isPikcupPointsEnabled();
        $pickupPointsData = $this->pickupPointsData->create();
        $pickupPointsArray = [];
        if ($pickupPointsEnabled) {
            $pickupPointsCollection = $this->pickupsModelFactory->create()
                ->getCollection()
                ->setOrder('pickup_point_id', 'DESC');

            foreach ($pickupPointsCollection as $key => $value) {
                $pickupPointsObjData = $this->pickupPointsObj->create();
                $pickupPointsObjData->setPickuppointId($value->getPickupPointId());
                $pickupPointsObjData->setCountryId($value->getPickupCountry());

                $locationData = array($value->getPickupPointName(), $value->getPickupAddress());
                $pickupPointsObjData->setStreet($locationData);
                $pickupPointsObjData->setCity($value->getPickupCity());
                $pickupPointsObjData->setLatitude($value->getPickupPointLat());
                $pickupPointsObjData->setLongitude($value->getPickupPointLong());

                $pickupPointsArray[] = $pickupPointsObjData;
            }
        }
        $pickupPointsData->setPickupPoints($pickupPointsArray);
        return $pickupPointsData;
    }
}
