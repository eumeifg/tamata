<?php
namespace Magedelight\MobileInit\Model;

use Magedelight\MobileInit\Api\Data\MobileConfigModuleInterface;

class MobileConfigModule extends \Magento\Framework\DataObject implements MobileConfigModuleInterface
{
    /**
     * @param string $language
     * @return $this
     */
    public function setModuleName($moduleName)
    {
           return $this->setData('module_name', $moduleName);
    }

   /**
    * @return string.
    */
    public function getModuleName()
    {
           return $this->getData('module_name');
    }

   /**
    * @param bool $moduleStatus
    * @return $this
    */
    public function setModuleStatus($moduleStatus)
    {
           return $this->setData('module_status', $moduleStatus);
    }

   /**
    * @return bool.
    */
    public function getModuleStatus()
    {
           return $this->getData('module_status');
    }
}
