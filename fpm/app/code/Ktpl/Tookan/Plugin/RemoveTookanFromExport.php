<?php

namespace Ktpl\Tookan\Plugin;

use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Framework\Api\Search\DocumentInterface;

class RemoveTookanFromExport
{

    /**
     * @var \MDC\Sales\Model\Source\Order\PickupStatus
     */
    protected $pickupStatuses;

    /**
     * @param \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses
     */
    public function __construct(
        \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses
    ) {
        $this->pickupStatuses = $pickupStatuses;
    }

    public function afterGetFields(MetadataProvider $subject, $result)
    {
        if (($key = array_search('tookan_status', $result)) !== false) {
            unset($result[$key]);
        }
        return $result;
    }

    public function afterGetHeaders(MetadataProvider $subject, $result)
    {
        if (($key = array_search('Tookan Status', $result)) !== false) {
            unset($result[$key]);
        }
        return $result;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function afterGetRowData(MetadataProvider $subject, $result, DocumentInterface $document, $fields, $options)
    {
         $i = 0;
        foreach ($fields as $column) {
            if ($column === 'pickup_status') {
                $result[$i] = $this->pickupStatuses->getOptionText($result[$i]);
                break;
            }
            $i++;
            continue;
        }
        return $result;
    }
}
