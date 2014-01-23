<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (class_exists('SplFileObject')) {
    return;
}

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class SplFileObject extends SplFileInfo
{
    const DROP_NEW_LINE = 1;
    const READ_AHEAD    = 2;
    const SKIP_EMPTY    = 4;
    const READ_CSV      = 8;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var string
     */
    private $currentc;

    /**
     * @var string
     */
    private $currentcvs;

    /**
     * @var array
     */
    private $cvsControl = array(',', '"');

    /**
     * @var int
     */
    private $maxLineLen = 0;

    public function __construct($fileName, $open_mode = 'r', $use_include_path = false, $context = null)
    {
        parent::__construct($fileName);

        $open_mode = (string) $open_mode;
        $use_include_path = (bool) $use_include_path;

        if (4 === func_num_args()) {
            if (!is_resource($context)) {
                throw new RuntimeException(sprintf('%s() expects parameter 4 to be resource, %s given',
                    __METHOD__,
                    gettype($context)
                ));
            }

            $this->stream = fopen($fileName, $open_mode, $use_include_path, $context);
        } else {
            $this->stream = fopen($fileName, $open_mode, $use_include_path);
        }
    }

    public function current()
    {
        if ($this->READ_CSV === ($this->READ_CSV & $this->flags)) {
            return $this->currentcvs;
        } else {
            return $this->currentc;
        }
    }

    public function eof()
    {
        return feof($this->stream);
    }

    public function fflush()
    {
        return fflush($this->stream);
    }

    public function fgetc()
    {
        return fgetc($this->stream);
    }

    public function fgetcsv($delimiter = ",", $enclosure = "\"", $escape = "\\")
    {
        $ret = fgetcsv($this->stream, 0, $delimiter, $enclosure);

        if ($this->SKIP_EMPTY | $this->DROP_NEW_LINE === ($this->SKIP_EMPTY | $this->DROP_NEW_LINE & $this->flags)) {
            while ($ret == null) {
                $ret = fgetcsv($this->stream, 0, $delimiter, $enclosure);
            }
        }

        return $this->currentcvs = $ret;
    }

    public function fgets()
    {
        return $this->currentc = fgets($this->stream);
    }

    public function fgetss()
    {
        return fgetss($this->stream);
    }

    public function flock($operation, &$wouldblock)
    {
        return flock($this->stream, $operation, $wouldblock);
    }

    public function fpassthru()
    {
        return fpassthru($this->stream);
    }

    public function fputcsv($fields, $delimiter = ',', $enclosure = '"' )
    {
        $handle = &$this->stream;

        // Sanity Check
        if (!is_resource($handle)) {
            trigger_error('fputcsv() expects parameter 1 to be resource, ' .
                gettype($handle) . ' given', E_USER_WARNING);

            return false;
        }

        if ($delimiter!=NULL) {
            if ( strlen($delimiter) < 1 ) {
                trigger_error('delimiter must be a character', E_USER_WARNING);

                return false;
            } elseif ( strlen($delimiter) > 1 ) {
                trigger_error('delimiter must be a single character', E_USER_NOTICE);
            }

            /* use first character from string */
            $delimiter = $delimiter[0];
        }

        if ($enclosure!=NULL) {
             if ( strlen($enclosure) < 1 ) {
                trigger_error('enclosure must be a character', E_USER_WARNING);

                return false;
            } elseif ( strlen($enclosure) > 1 ) {
                trigger_error('enclosure must be a single character', E_USER_NOTICE);
            }

            /* use first character from string */
            $enclosure = $enclosure[0];
       }

        $i = 0;
        $csvline = '';
        $escape_char = '\\';
        $field_cnt = count($fields);
        $enc_is_quote = in_array($enclosure, array('"',"'"));
        reset($fields);

        foreach ($fields AS $field) {

            /* enclose a field that contains a delimiter, an enclosure character, or a newline */
            if( is_string($field) && (
                strpos($field, $delimiter)!==false ||
                strpos($field, $enclosure)!==false ||
                strpos($field, $escape_char)!==false ||
                strpos($field, "\n")!==false ||
                strpos($field, "\r")!==false ||
                strpos($field, "\t")!==false ||
                strpos($field, ' ')!==false ) ) {

                $field_len = strlen($field);
                $escaped = 0;

                $csvline .= $enclosure;
                for ($ch = 0; $ch < $field_len; $ch++) {
                    if ($field[$ch] == $escape_char && $field[$ch+1] == $enclosure && $enc_is_quote) {
                        continue;
                    } elseif ($field[$ch] == $escape_char) {
                        $escaped = 1;
                    } elseif (!$escaped && $field[$ch] == $enclosure) {
                        $csvline .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $csvline .= $field[$ch];
                }
                $csvline .= $enclosure;
            } else {
                $csvline .= $field;
            }

            if ($i++ != $field_cnt) {
                $csvline .= $delimiter;
            }
        }

        $csvline .= "\n";

        return fwrite($handle, $csvline);
    }

    public function fscanf()
    {
        $args = func_get_args();
        array_unshift($args, $this->stream);

        return call_user_func_array('fscanf', $args);
    }

    public function fseek($offset, $whence = SEEK_SET)
    {
        return fseek($this->stream, $offset, $whence);
    }

    public function fstat()
    {
        return fstat($this->stream);
    }

    public function ftell()
    {
        return ftell($this->stream);
    }

    public function ftruncate($size)
    {
        return ftruncate($this->stream, $size);
    }

    public function fwrite($str, $lengt = null)
    {
        return fwrite($this->stream, $str, $lengt);
    }

    public function getChildren()
    {
        return null;
    }

    public function getCsvControl()
    {
        return $this->cvsControl;
    }

    public function getCurrentLine()
    {
        return $this->fgets();
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function getMaxLineLen()
    {
        return $this->maxLineLen;
    }

    public function hasChildren()
    {
        return false;
    }

    public function rewind()
    {
        return rewind($this->stream);
    }

    public function seek($offset)
    {
        return fseek($this->stream, $offset);
    }

    public function setCsvControl($delimiter = ",", $enclosure = "\"", $escape = "\\")
    {
        $this->cvsControl = array(
            (string) $delimiter,
            (string) $enclosure,
            (string) $escape,
        );
    }

    public function setFlags($flags)
    {
        $this->flags = (int) $flags;
    }

    public function setMaxLineLen($max_len)
    {
        $this->maxLineLen = (int) $max_len;
    }

    public function __toString()
    {
        return $this->current();
    }

    public function valid()
    {
        return !$this->eof();
    }
}
