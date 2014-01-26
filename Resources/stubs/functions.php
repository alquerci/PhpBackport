<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


if (!function_exists('str_split')) {
    function str_split($string,$string_length=1)
    {
        if (strlen($string)>$string_length || !$string_length) {
            do {
                $c = strlen($string);
                $parts[] = substr($string,0,$string_length);
                $string = substr($string,$string_length);
            } while ($string !== false);
        } else {
            $parts = array($string);
        }

        return $parts;
    }
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data)
    {
        $f = @fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);

            return $bytes;
        }
    }
}


if ( !function_exists('htmlspecialchars_decode') ) {
    function htmlspecialchars_decode($text, $flag = ENT_COMPAT)
    {
        return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $flag)));
    }
}

if (!function_exists('array_replace')) {
    function array_replace()
    {
        $args = func_get_args();
        $first = null;

        foreach ($args as $i => $arg) {
            if (!is_array($arg)) {
                return null;
            }

            if (null === $first) {
                $first = array();
            }

            foreach ($arg as $key => $value) {
                $first[$key] = $value;
            }
        }

        return $first;
    }
}

if (!function_exists('http_build_query')) {
    if (!defined('PHP_QUERY_RFC1738')) define('PHP_QUERY_RFC1738', 1);
    if (!defined('PHP_QUERY_RFC3986')) define('PHP_QUERY_RFC3986', 2);
    function http_build_query($query_data, $numeric_prefix = null, $arg_separator = null, $enc_type = PHP_QUERY_RFC1738, $parent_key = null)
    {
        $query_data       = (array) $query_data;
        $numeric_prefix   = (string) $numeric_prefix;
        $arg_separator    = (string) ($arg_separator ? $arg_separator : ini_get('arg_separator.output'));
        $enc_type         = (int) $enc_type;

        switch ($enc_type) {
            case PHP_QUERY_RFC3986:
                $encoder =  'rawurlencode';
                break;
            case PHP_QUERY_RFC1738:
                $encoder =  'urlencode';
                break;
            default:
                $encoder =  'urlencode';
                break;
        }

        $query = "";

        foreach ($query_data as $key => $value) {
            if (is_int($key) && null === $parent_key) {
                $key = (string) $key;
                $key = $numeric_prefix.$key;
            } else {
                $key = (string) $key;
            }

            if (null !== $parent_key) {
                $key = sprintf('[%s]', $key);
            }

            if (is_object($value) || is_array($value)) {
                $query .= http_build_query($value, $numeric_prefix, $arg_separator, $enc_type, $parent_key.$key);
            } else {
                $query .= $encoder($parent_key.$key);
                $query .= '=';
                $query .= $encoder((string) $value);
            }

            $query .= $arg_separator;
        }

        return substr($query, 0, -1);
    }
}

if (!function_exists('stream_resolve_include_path')) {
    /**
     * Resolve filename against the include path.
     *
     * @param string $filename
     *
     * @return string|boolean Returns a string containing the resolved absolute filename, or FALSE on failure.
     *
     * @see http://www.php.net/manual/fr/function.stream-resolve-include-path.php
     */
    function stream_resolve_include_path($filename)
    {
        $filename = (string) $filename;

        $includePaths = explode(PATH_SEPARATOR, get_include_path());

        foreach ($includePaths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                return realpath($path . DIRECTORY_SEPARATOR . $filename);
            }
        }

        return false;
    }
}

if (!function_exists('json_encode')) {
    function json_encode($data) {
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                return '"' . addslashes($data) . '"';
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed[] = json_encode($value);
                    $output_associative[] = json_encode($key) . ':' . json_encode($value);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(',', $output_indexed) . ']';
                } else {
                    return '{' . implode(',', $output_associative) . '}';
                }
            default:
                return ''; // Not supported
        }
    }
}

if (!function_exists('libxml_disable_entity_loader')) {
    /**
     * Disable/enable the ability to load external entities.
     *
     * @param Boolean $disable Disable (TRUE) or enable (FALSE) libxml extensions
     *     (such as DOM, XMLWriter and XMLReader) to load external entities.
     *
     * @return Boolean Returns the previous value.
     *
     * @see http://www.php.net/manual/function.libxml-disable-entity-loader.php
     */
    function libxml_disable_entity_loader($disable = true)
    {
        return false;
    }
}

if (!function_exists('finfo_open') && class_exists('finfo')) {
    function finfo_open($options = FILEINFO_NONE, $magicFile = null)
    {
        return new finfo($options, $magicFile);
    }
}

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    if ('\\' === DIRECTORY_SEPARATOR
        && 'win' === substr(strtolower(php_uname('s')), 0, 3)
    ) {
        // usefull for windows os detection
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            define('PHP_WINDOWS_VERSION_BUILD', preg_replace('/^.*?build\s*(\d*).*/', '\\1', php_uname('v')));
        }

        if (!defined('PHP_WINDOWS_VERSION_MAJOR')) {
            define('PHP_WINDOWS_VERSION_MAJOR', preg_replace('/^.*?(\d*)\..*/', '\\1', php_uname('r')));
        }
    }
}

if (!function_exists('sys_get_temp_dir')) {
    /**
     * Returns the path of the directory PHP stores temporary files in by default.
     *
     * You can override the default temporary directory by setting the TMPDIR environment variable.
     *
     * @return string Returns the path of the temporary directory.
     */
    function sys_get_temp_dir()
    {
        static $temporaryDirectory;

        // Did we determine the temporary directory already?
        if (null !== $temporaryDirectory) {
            return $temporaryDirectory;
        }

        $envTmpDirKeys = array();
        $default = '/tmp';

        if ('\\' === DIRECTORY_SEPARATOR
            && 'win' === substr(strtolower(php_uname('s')), 0, 3)
        ) {
            // On Windows checks for the existence of environment variables
            // in the following order:
            $envTmpDirKeys = array(
                'TMP',
                'TEMP',
                'USERPROFILE',
                'SYSTEMROOT', // The Windows directory
            );
        } elseif ('/' === DIRECTORY_SEPARATOR) {
            // On Unix use the (usual) TMPDIR environment variable.
            $envTmpDirKeys = array(
                'TMPDIR',
            );
        }

        foreach ($envTmpDirKeys as $key) {
            $dir = getenv($key);
            if ($dir) {
                return $temporaryDirectory = $dir;
            }
        }

        // Shouldn't ever(!) end up here ... last ditch default.
        return $temporaryDirectory = $default;
    }
}

if (!function_exists('stream_is_local')) {
    function stream_is_local($stream_or_url)
    {
        if (is_resource($stream_or_url)) {
            $data = stream_get_meta_data($stream_or_url);
            if (in_array($data['wrapper_type'], array('file', 'php', 'zlib', 'data', 'glob', 'phar', 'rar'))) {
                return true;
            }
        }

        $stream_or_url = (string) is_object($stream_or_url) && method_exists($stream_or_url, '__toString') ? $stream_or_url->__toString() : $stream_or_url;

        return preg_match('/^(https?|ftps?|ssl|tls):/', $stream_or_url) ? false : true;
    }
}

if (!function_exists('error_get_last')) {
    function error_get_last()
    {
        return null;
    }
}
