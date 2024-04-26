<?php
namespace MDC\Sales\Model\Source\Order;

class PickupStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    const PENDING = '0';

    const READY_TO_PICK = '1';

    const PICKED = '2';

    /**
     * @var array
     */
    protected $pickupStatuses = [];

    public function __construct()
    {
        $this->pickupStatuses = [
            self::PENDING => __('Pending'),
            self::READY_TO_PICK => __('Ready To Pick'),
            self::PICKED => __('Picked')
        ];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return $this->pickupStatuses;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     *
     * @return available status aarray
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
