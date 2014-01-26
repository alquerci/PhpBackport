<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alexandre Quercia <alquerci@email.com>
 *
 * @see http://www.php.net/manual/class.filesystemiterator.php
 */
class FilesystemIterator extends DirectoryIterator implements SeekableIterator
{
    const CURRENT_AS_PATHNAME = 32;
    const CURRENT_AS_FILEINFO = 0;
    const CURRENT_AS_SELF = 16;
    const CURRENT_MODE_MASK = 240;
    const KEY_AS_PATHNAME = 0;
    const KEY_AS_FILENAME = 256;
    const FOLLOW_SYMLINKS = 512;
    const KEY_MODE_MASK = 3840;
    const NEW_CURRENT_AND_KEY = 256;
    const SKIP_DOTS = 4096;
    const UNIX_PATHS = 8192;

    private $flags;

    /**
     * Constructs a new filesystem iterator from the path.
     *
     * @param string $path The path of the filesystem item to be iterated over.
     * @param integer $flags Flags may be provided which will affect the behavior of some methods.
     *     A list of the flags can found under FilesystemIterator predefined constants.
     *     They can also be set later with FilesystemIterator::setFlags()
     *
     * @throws UnexpectedValueException if the path cannot be found.
     */
    public function __construct($path, $flags = FilesystemIterator::SKIP_DOTS)
    {
        if (is_object($path) && method_exists($path, '__toString')) {
            $path = $path->__toString();
        }

        $path = (string) $path;

        if (!file_exists($path)) {
            throw new UnexpectedValueException(sprintf('The path "%s" cannot be found.', $path));
        }

        $this->setFlags($flags);

        parent::__construct($path);
    }

    /**
     * Gets the handling flags.
     *
     * @return integer The integer value of the set flags.
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Sets handling flags.
     *
     * @param integer $flags The handling flags to set. See the FilesystemIterator constants.
     */
    public function setFlags($flags = 0)
    {
        if ($this->isFlagSet($flags, FilesystemIterator::CURRENT_MODE_MASK)) {
            $this->removeFlags(FilesystemIterator::CURRENT_MODE_MASK);
        }

        if ($this->isFlagSet($flags, FilesystemIterator::KEY_MODE_MASK)) {
            $this->removeFlags(FilesystemIterator::KEY_MODE_MASK);
        }

        $this->addFlags($flags);
    }

    /**
     * Get file information of the current element.
     *
     * $return mixed The filename, file information, or $this depending on the set flags.
     *     See the FilesystemIterator constants.
     */
    public function current()
    {
        if ($this->isFlagSet(FilesystemIterator::CURRENT_AS_PATHNAME)) {
            return $this->getPathname();
        }

        if ($this->isFlagSet(FilesystemIterator::CURRENT_AS_SELF)) {
            return $this;
        }

        return $this->getFileInfo();
    }


    /**
     * Retrieve the key for the current file.
     *
     * @return string Returns the pathname or filename depending on the set flags. See the FilesystemIterator constants.
     */
    public function key()
    {
        if ($this->isFlagSet(FilesystemIterator::KEY_AS_FILENAME)) {
            return $this->getFilename();
        }

        return $this->getPathname();
    }

    /**
     * Move to the next file.
     */
    public function next()
    {
        parent::next();

        if ($this->isFlagSet(FilesystemIterator::SKIP_DOTS)) {
            while ($this->valid() && $this->isDot()) {
                parent::next();
            }
        }
    }

    /**
     * Rewinds back to the start.
     */
    public function rewind()
    {
        parent::rewind();

        if ($this->isFlagSet(FilesystemIterator::SKIP_DOTS)) {
            while ($this->valid() && $this->isDot()) {
                $this->next();
            }
        }
    }

    /**
     * Seek to a given position.
     *
     * @param integer $position The zero-based numeric position to seek to.
     */
    public function seek($position)
    {
        $this->rewind();

        for ($i = 0; $i < $position; $i++) {
            if (!$this->valid()) {
                return;
            }

            $this->next();
        }
    }


    public function getBasename($suffix = null)
    {
        return basename($this->getPathname(), $suffix);
    }

    public function getExtension()
    {
        return pathinfo($this->getPathname(), PATHINFO_EXTENSION);
    }

    public function getLinkTarget()
    {
        return readlink($this->getPathname());
    }

    public function getRealPath()
    {
        return realpath($this->getPathname());
    }

    private function isFlagSet($flag, $flags = null)
    {
        if (null === $flags) {
            $flags = $this->flags;
        }

        return ($flags & $flag) === $flag;
    }

    private function addFlags($flags = 0)
    {
        $this->flags |= $flags;
    }

    private function removeFlags($flags = 0)
    {
        $this->flags &= ~$flags;
    }
}
