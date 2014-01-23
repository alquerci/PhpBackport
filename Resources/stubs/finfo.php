<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('FILEINFO_NONE')) {
    define ('FILEINFO_NONE', 0);
}

if (!defined('FILEINFO_SYMLINK')) {
    define ('FILEINFO_SYMLINK', 2);
}

if (!defined('FILEINFO_MIME_TYPE')) {
    define ('FILEINFO_MIME_TYPE', 16);
}

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class finfo
{
    private $options;
    private $magicFile;

    public function __construct($options = FILEINFO_NONE, $magicFile = null)
    {
        $this->options = (integer) $options;
        $this->magicFile = (string) $magicFile;
    }

    public function file($filename = null, $options = FILEINFO_NONE, $context = null)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $options |= $this->options;

        if (FILEINFO_SYMLINK === (FILEINFO_SYMLINK & $options)) {
            if (is_link($filename)) {
                $filename = realpath($filename);
            }
        }

        $extension = empty($filename) ? '' : pathinfo($filename, PATHINFO_EXTENSION);

        if (empty($extension)) {
            return $this->getMimeTypeFromContent($filename);
        }

        $mime = $this->getMimeTypeFromExtension($extension);

        if (false === $mime) {
            if (is_link($filename)) {
                return 'inode/symlink';
            }

            if (is_dir($filename)) {
                return 'inode/directory';
            }

            if ('' === file_get_contents($filename, null, null, null, 1)) {
                return 'inode/x-empty';
            }

            return 'application/octet-stream';
        }

        return $mime;
    }

    private function buffer($string = null, $options = FILEINFO_NONE, $context = null)
    {
        if (empty($string)) {
            return false;
        }

        // FIXME add some content detections
        $patterns = array(
            'image/gif' => '/^GIF/',
        );

        foreach ($patterns as $mimeType => $pattern) {
            if (preg_match($pattern, $string)) {
                return $mimeType;
            }
        }

        return false;
    }

    private function getMimeTypeFromContent($filename)
    {
        $handle = fopen($filename, 'rb');
        $string = fread($handle, 1024);
        fclose($handle);

        return $this->buffer($string);
    }

    private function getMimeTypeFromExtension($extension)
    {
        $mimeTypes = $this->getMimeTypes();

        return isset($mimeTypes[$extension]) ? $mimeTypes[$extension][0] : false;
    }

    private function getMimeTypes()
    {
        if (!empty($this->magicFile)) {
            return $this->parseMimeFile($this->magicFile);
        }

        $phpMimeFile = dirname(__FILE__).'/../misc/mime.types.php';
        $mimeFile = dirname(__FILE__).'/../misc/mime.types';

        if (!file_exists($phpMimeFile) || filemtime($mimeFile) > filemtime($phpMimeFile)) {
            $mimeTypes = $this->parseMimeFile($mimeFile);
            file_put_contents($phpMimeFile, sprintf('<?php return %s;', var_export($mimeTypes, true)));
        }

        $mimeTypes = (array) include $phpMimeFile;

        return $mimeTypes;
    }

    private function parseMimeFile($filename)
    {
        $mimes = array();

        $lines = explode("\n", file_get_contents($filename));

        if (empty($lines)) {
            return $mimes;
        }

        $linePattern = '/([^\s]+)\s+(\w+(?:\s+\w+)*)/';

        foreach ($lines as $line) {
            $line = trim($line);
            if (false !== $pos = strpos($line, '#')) {
                $line = substr($line, 0, $pos);
            }

            if (preg_match($linePattern, $line, $matches)) {
                $extensions = preg_split('/\s+/', $matches[2]);

                foreach ($extensions as $extension) {
                    $mimes[$extension][] = $matches[1];
                }
            }
        }

        return $mimes;
    }
}
