<?php


namespace Ktpl\Productslider\Api;

interface ProductsliderManagementInterface
{

    /**
     * GET for Productslider api
     * @param string $sliderId
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function getProductslider($sliderId);

    /**
     * GET All Product sliders
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface[]
     */
    //public function getAllSliders($searchCriteria);
}
