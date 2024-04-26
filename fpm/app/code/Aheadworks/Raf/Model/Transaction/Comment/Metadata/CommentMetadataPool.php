<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Comment\Metadata;

/**
 * Class CommentMetadataPool
 *
 * @package Aheadworks\Raf\Model\Transaction\Comment\Metadata
 */
class CommentMetadataPool
{
    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @var CommentMetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var CommentMetadataInterface[]
     */
    private $metadataInstances = [];

    /**
     * @param CommentMetadataInterfaceFactory $metadataFactory
     * @param array $metadata
     */
    public function __construct(
        CommentMetadataInterfaceFactory $metadataFactory,
        $metadata = []
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->metadata = $metadata;
    }

    /**
     * Retrieves metadata for comment type
     *
     * @param string $commentType
     * @return CommentMetadataInterface
     * @throws \Exception
     */
    public function getMetadata($commentType)
    {
        if (empty($this->metadataInstances[$commentType])) {
            $commentMetadata = $this->getCommentMetadata($commentType);
            $commentMetadataInstance = $this->getMetadataInstance($commentMetadata);
            $this->metadataInstances[$commentType] = $commentMetadataInstance;
        }
        return $this->metadataInstances[$commentType];
    }

    /**
     * Retrieve metadata for specified comment type
     *
     * @param string $commentType
     * @return array
     * @throws \Exception
     */
    private function getCommentMetadata($commentType)
    {
        if (!isset($this->metadata[$commentType])) {
            throw new \Exception(sprintf('Unknown comment metadata: %s requested', $commentType));
        }
        return $this->metadata[$commentType];
    }

    /**
     * Retrieve metadata instance from specified data
     *
     * @param $commentMetadata
     * @return mixed
     * @throws \Exception
     */
    private function getMetadataInstance($commentMetadata)
    {
        $metadataInstance = $this->metadataFactory->create(['data' => $commentMetadata]);
        if (!$metadataInstance instanceof CommentMetadataInterface) {
            throw new \Exception(
                sprintf('Metadata instance does not implement required interface.')
            );
        }
        return $metadataInstance;
    }
}
