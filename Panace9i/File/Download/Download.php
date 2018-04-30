<?php

namespace Panace9i\File\Download;

class Download
{
    /** @var FileInterface */
    private $file;
    /** @var string */
    private $range;
    /** @var array */
    private $preparedRange;
    /** @var int */
    private $endByte;
    /** @var int */
    private $startByte;
    /** @var string */
    private $contentDisposition = 'attachment';

    /**
     * Download constructor.
     *
     * @param FileInterface $file
     */
    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }

    /**
     * @return Download
     */
    public function asAttachment()
    {
        $this->contentDisposition = 'attachment';

        return $this;
    }

    /**
     * @return Download
     */
    public function asInline()
    {
        $this->contentDisposition = 'inline';

        return $this;
    }

    public function execute()
    {
        try {
            header("Content-Type: {$this->file->getMime()}");
            header('Accept-Ranges: bytes');
            header('Content-Disposition: ' . $this->contentDisposition . '; filename="' . $this->file->getName() . '"');

            if ($this->isPartial()) {
                $this->runPartial();
            } else {
                $this->runFull();
            }
        } catch (\Exception $e) {
            header("HTTP/1.1 {$e->getCode()} {$e->getMessage()}");
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isPartial()
    {
        $result = (bool)$this->getRange();
        if ($result) {
            if ($this->prepareRange() && $this->prepareRange()[0] !== '' && $this->prepareRange()[1] !== '') {
                if ($this->getEnd() >= $this->getFilesize() || (!$this->getStart() && (!$this->getEnd() || $this->getEnd() == ($this->getFilesize() - 1)))) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getRange()
    {
        if ($this->range) {
            return $this->range;
        }

        $result = '';
        if (isset($_SERVER['HTTP_RANGE'])) {
            $result = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
        } elseif ($apache = $this->getAllHttpHeaders()) {
            $headers = [];
            foreach ($apache as $header => $val) {
                $headers[strtolower($header)] = $val;
            }

            if (isset($headers['range'])) {
                $result = $headers['range'];
            }
        }

        $this->range = $result;

        return $result;
    }

    /**
     * @return array|\Closure|false
     */
    private function getAllHttpHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        return function () {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        };
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function prepareRange()
    {
        if ($this->preparedRange) {
            return $this->preparedRange;
        }

        list($param, $range) = explode('=', $this->getRange());
        if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
            throw new \Exception("Invalid Request", 400);
        }

        $range = explode(',', $range);
        $range = explode('-', $range[0]); // We only deal with the first requested range

        if (count($range) != 2) { // Bad request - 'bytes' parameter is not valid
            throw new \Exception("Invalid Request", 400);
        }

        $this->preparedRange = $range;

        return $this->preparedRange;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getEnd()
    {
        if ($this->endByte) {
            return $this->endByte;
        }

        $filesize = filesize($this->file->getPath());
        if ($this->prepareRange()[0] === '') { // First number missing, return last $range[1] bytes
            $result = $filesize - 1;
        } elseif ($this->prepareRange()[1] === '') {
            $result = $filesize - 1;
        } else {
            $result = intval($this->prepareRange()[1]);
        }
        $this->endByte = $result;

        return $this->endByte;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getStart()
    {
        if ($this->startByte) {
            return $this->startByte;
        }

        if ($this->prepareRange()[0] === '') { // First number missing, return last $range[1] bytes
            $result = $this->getEnd() - intval($this->prepareRange()[0]);
        } else {
            $result = intval($this->prepareRange()[0]);
        }
        $this->startByte = $result;

        return $this->startByte;
    }

    /**
     * @return int
     */
    private function getFilesize()
    {
        return filesize($this->file->getPath());
    }

    /**
     * Full download
     */
    private function runFull()
    {
        header("Content-Length: {$this->getFilesize()}");
        readfile($this->file->getPath());
    }

    /**
     * Partial download
     * @throws \Exception
     */
    private function runPartial()
    {
        $length = $this->getEnd() - $this->getStart() + 1;

        header("Content-Length: {$length}");
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes {$this->getStart()}-{$this->getEnd()}/{$this->getFilesize()}");
        if (!$fp = fopen($this->file->getPath(), 'r')) { // Error out if we can't read the file
            throw new \Exception("Internal Server Error", 500);
        }

        if ($this->getStart()) {
            fseek($fp, $this->getStart());
        }

        while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
            $read   = ($length > 8192) ? 8192 : $length;
            $length -= $read;
            print(fread($fp, $read));
        }
        fclose($fp);
    }
}
