<?php
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\StoreDataInterface;

class StoreData extends \Magento\Framework\DataObject implements StoreDataInterface
{
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
           return $this->setData('id', $id);
    }

   /**
    * @return int
    */
    public function getId()
    {
           return $this->getData('id');
    }

   /**
    * @param string $label
    * @return $this
    */
    public function setLabel($label)
    {
           return $this->setData('label', $label);
    }

   /**
    * @return string
    */
    public function getLabel()
    {
           return $this->getData('label');
    }

   /**
    * @param string $code
    * @return $this
    */
    public function setCode($code)
    {
           return $this->setData('code', $code);
    }

   /**
    * @return string
    */
    public function getCode()
    {
           return $this->getData('code');
    }

   /**
    * @param bool $isSelected
    * @return $this
    */
    public function setIsSelected($isSelected)
    {
           return $this->setData('isSelected', $isSelected);
    }

   /**
    * @return bool
    */
    public function getIsSelected()
    {
           return $this->getData('isSelected');
    }
}
