<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (class_exists('SplFileInfo')) {
    return;
}

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class SplFileInfo
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileClass = 'SplFileObject';

    /**
     * @var string
     */
    private $infoClass = 'SplFileInfo';

    public function __construct($fileName)
    {
        $this->fileName = (string) $fileName;
    }

    public function getATime()
    {
        return fileatime($this->fileName);
    }

    public function getBasename($suffix = null)
    {
        return basename($this->fileName, $suffix);
    }

    public function getCTime()
    {
        return filectime($this->fileName);
    }

    public function getExtension()
    {
        return pathinfo($this->fileName, PATHINFO_EXTENSION);
    }

    public function getFileInfo($class_name = null)
    {
        if (null === $class_name) {
            return new self($this->fileName);
        } elseif (is_subclass_of($class_name, self)) {
            return new $class_name($this->fileName);
        }
    }

    public function getFilename()
    {
        return basename($this->fileName);
    }

    public function getGroup()
    {
        return filegroup($this->fileName);
    }

    public function getInode()
    {
        return fileinode($this->fileName);
    }

    public function getLinkTarget()
    {
        return readlink($this->fileName);
    }

    public function getMTime()
    {
        return filemtime($this->fileName);
    }

    public function getOwner()
    {
        return fileowner($this->fileName);
    }

    public function getPath()
    {
        return dirname($this->fileName);
    }

    public function getPathInfo($class_name = null)
    {
        if (null === $class_name) {
            return new $this->infoClass($this->getPath());
        } elseif (is_subclass_of($class_name, self)) {
            return new $class_name($this->getPath());
        }
    }

    public function getPathname()
    {
        return $this->fileName;
    }

    public function getPerms()
    {
        return fileperms($this->fileName);
    }

    public function getRealPath()
    {
        return realpath($this->fileName);
    }

    public function getSize()
    {
        return filesize($this->fileName);
    }

    public function getType()
    {
        return filetype($this->fileName);
    }

    public function isDir()
    {
        return is_dir($this->fileName);
    }

    public function isExecutable()
    {
        return is_executable($this->fileName);
    }

    public function isFile()
    {
        return is_file($this->fileName);
    }

    public function isLink()
    {
        return is_link($this->fileName);
    }

    public function isReadable()
    {
        return is_readable($this->fileName);
    }

    public function isWritable()
    {
        return is_writable($this->fileName);
    }

    public function openFile($open_mode = 'r', $use_include_path = false, $context = null)
    {
        return new $this->fileClass($this->fileName, $open_mode, $use_include_path, $context);
    }

    public function setFileClass($class_name = null)
    {
        if (null === $class_name) {
            $this->fileClass = 'SplFileObject';
        } elseif (is_subclass_of($class_name, 'SplFileObject')) {
            $this->fileClass = $class_name;
        }
    }

    public function setInfoClass($class_name = null)
    {
        if (null === $class_name) {
            $this->infoClass = 'SplFileInfo';
        } elseif (is_subclass_of($class_name, 'SplFileInfo')) {
            $this->infoClass = $class_name;
        }
    }
}
