<?php
namespace Magedelight\MobileInit\Model;

use Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface;

class MobilePriceFormatData extends \Magento\Framework\DataObject implements MobilePriceFormatDataInterface
{
   /**
    * @param int $precision
    * @return $this
    */
    public function setPrecision($precision)
    {
        return $this->setData('precision', $precision);
    }

   /**
    * @return int
    */
    public function getPrecision()
    {
        return $this->getData('precision');
    }

   /**
    * @param string $decimalSymbol
    * @return $this
    */
    public function setDecimalSymbol($decimalSymbol)
    {
        return $this->setData('decimalsymbol', $decimalSymbol);
    }

   /**
    * @return string
    */
    public function getDecimalSymbol()
    {
        return $this->getData('decimalsymbol');
    }
}
