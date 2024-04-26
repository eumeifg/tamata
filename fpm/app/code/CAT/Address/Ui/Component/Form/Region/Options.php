<?php

namespace CAT\Address\Ui\Component\Form\Region;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\Country;

class Options implements OptionSourceInterface
{
    /**
     * @var Country
     */
    protected $_country;

    /**
     * @param Country $country
     */
    public function __construct(
        Country $country
    ) {
        $this->_country = $country;
    }

    /**
     * @return array
     */
    public function toOptionArray() {
        $countryCode = 'IQ';
        $regionCollection = $this->_country->loadByCode($countryCode)->getRegions();
        return $regionCollection->loadData()->toOptionArray();
    }
}
