<?php

namespace Ktpl\Helpdesk\Api\Data;

interface AttachmentInterface
{
    const KEY_NAME = 'name';
    const KEY_LINK  = 'link';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link);
}