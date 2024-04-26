<?php

namespace Ktpl\GoogleMapAddress\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class PickupPointsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Ktpl\GoogleMapAddress\Helper\Data
     */
    private $helper;

    /**
     * @param \Ktpl\GoogleMapAddress\Helper\Data $helper
     */
    public function __construct(
        \MDC\PickupPoints\Model\PickupsFactory $pickupsModelFactory,
        \MDC\PickupPoints\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Theme\Model\Favicon\Favicon $faviconIcon
    ) {
        $this->pickupsModelFactory = $pickupsModelFactory;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->assetRepo = $assetRepo;
        $this->faviconIcon = $faviconIcon;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $pickupPointsEnabled = $this->helper->isPikcupPointsEnabled();

        $pickupPointsCollection = $this->pickupsModelFactory->create()
                                    ->getCollection()
                                    ->setOrder('pickup_point_id','DESC');

        $pickupPointsConfigArray = [];

        foreach ($pickupPointsCollection as $key => $value) {
            
            $pickupPointsConfigArray[$key]['id'] = $value->getPickupPointId();
            $pickupPointsConfigArray[$key]['country'] = $value->getPickupCountry();
            $pickupPointsConfigArray[$key]['location'] = $value->getPickupPointName();
            $pickupPointsConfigArray[$key]['addressline'] = $value->getPickupAddress();
            $pickupPointsConfigArray[$key]['city'] = $value->getPickupCity();
            $pickupPointsConfigArray[$key]['lat'] = $value->getPickupPointLat();
            $pickupPointsConfigArray[$key]['long'] = $value->getPickupPointLong();                 
        }

        if(!$pickupPointsEnabled){
            $pickupPointsConfigArray = [];
        }

        $config['pickup_points_provider'] = [
            'locations' => $pickupPointsConfigArray,
            'tamta_icon' => $this->assetRepo->getUrl("Ktpl_GoogleMapAddress::images/pickup_marker.png")
        ];
        
        return $config;
    }
}
