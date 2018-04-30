<?php
require_once __DIR__ . '/config.php';

use Panace9i\File\Download\Download;
use Panace9i\File\Download\FileInterface;

class FileDownload implements FileInterface
{
    public function getMime()
    {
        return 'text/plain';
    }

    public function getName()
    {
        return 'test.txt';
    }

    public function getExtension()
    {
        return 'txt';
    }

    public function getPath()
    {
        return __DIR__.'/test.txt';
    }
}

$file = new Download(new FileDownload());
$file
  ->asInline()
  ->execute();
