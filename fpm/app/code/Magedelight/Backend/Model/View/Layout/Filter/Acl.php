<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\View\Layout\Filter;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;

class Acl
{
    /**
     * Authorization
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Delete elements that have "acl" attribute but value is "not allowed"
     * In any case, the "acl" attribute will be unset
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     */
    public function filterAclElements(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        foreach ($scheduledStructure->getElements() as $name => $data) {
            list(, $data) = $data;
            if (isset($data['attributes']['acl']) && $data['attributes']['acl']) {
                if (!$this->authorization->isAllowed($data['attributes']['acl'])) {
                    $this->removeElement($scheduledStructure, $structure, $name);
                }
            }
        }
    }

    /**
     * Remove scheduled element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return $this
     */
    protected function removeElement(
        ScheduledStructure $scheduledStructure,
        Structure $structure,
        $elementName,
        $isChild = false
    ) {
        $elementsToRemove = array_keys($structure->getChildren($elementName));
        $scheduledStructure->unsetElement($elementName);
        foreach ($elementsToRemove as $element) {
            $this->removeElement($scheduledStructure, $structure, $element, true);
        }
        if (!$isChild) {
            $structure->unsetElement($elementName);
        }
        return $this;
    }
}
