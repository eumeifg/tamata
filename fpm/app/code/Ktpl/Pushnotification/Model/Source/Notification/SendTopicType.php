<?php
 

namespace Ktpl\Pushnotification\Model\Source\Notification;

use Magento\Framework\Data\OptionSourceInterface;

 
class SendTopicType implements OptionSourceInterface
{
    /**#@+
     * Constants defined for "who can invite" types
     */
    const MC_STAGING = 'topicToTest1';
    const PRODUCTION_LIVE = 'promotion';
    /**#@-*/

    /**
     * Retrieve notification types as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MC_STAGING,
                'label' => __('Staging')
            ],
            [
                'value' => self::PRODUCTION_LIVE,
                'label' => __('Live (Production)')
            ],
        ];
    }
}
