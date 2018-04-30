<?php

namespace Panace9i\File\Download;

interface FileInterface
{
    /**
     * @return string
     */
    public function getMime();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @return string
     */
    public function getPath();
}