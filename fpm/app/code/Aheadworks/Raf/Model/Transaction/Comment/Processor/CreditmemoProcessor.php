<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Comment\Processor;

use Aheadworks\Raf\Model\Source\Transaction\EntityType;
use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase\Renderer\Placeholder;

/**
 * Class CreditmemoProcessor
 *
 * @package Aheadworks\Raf\Model\Transaction\Comment\Processor
 */
class CreditmemoProcessor implements ProcessorInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Placeholder
     */
    private $placeholder;

    /**
     * @param UrlInterface $urlBuilder
     * @param Placeholder $placeholder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Placeholder $placeholder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->placeholder = $placeholder;
    }

    /**
     * {@inheritdoc}
     */
    public function renderComment($entities, $isUrl)
    {
        $arguments = [];
        foreach ($entities as $entity) {
            if ($entity->getEntityType() != EntityType::CREDIT_MEMO_ID) {
                continue;
            }

            $creditmemoIncrementId = '#' . $entity->getEntityLabel();
            if ($isUrl) {
                $url = $this->urlBuilder->getUrl(
                    'sales/order_creditmemo/view',
                    ['creditmemo_id' => $entity->getEntityId()]
                );
                $creditmemoIncrementId = $this->placeholder->render(
                    ['<a href="%creditmemo_url">%creditmemo_id</a>'],
                    ['creditmemo_id' => $creditmemoIncrementId, 'creditmemo_url' => $url]
                );
            }
            $arguments['creditmemo_id'] = $creditmemoIncrementId;
        }

        return $arguments;
    }
}
