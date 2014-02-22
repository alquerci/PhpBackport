<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('DATE_ATOM')) {
    define('DATE_ATOM', 'Y-m-d\TH:i:sP');
}

if (!defined('DATE_COOKIE')) {
    define('DATE_COOKIE', 'l, d-M-y H:i:s T');
}

if (!defined('DATE_ISO8601')) {
    define('DATE_ISO8601', 'Y-m-d\TH:i:sO');
}

if (!defined('DATE_RFC822')) {
    define('DATE_RFC822', 'D, d M y H:i:s O');
}

if (!defined('DATE_RFC850')) {
    define('DATE_RFC850', 'l, d-M-y H:i:s T');
}

if (!defined('DATE_RFC1036')) {
    define('DATE_RFC1036', 'D, d M y H:i:s O');
}

if (!defined('DATE_RFC1123')) {
    define('DATE_RFC1123', 'D, d M Y H:i:s O');
}

if (!defined('DATE_RFC2822')) {
    define('DATE_RFC2822', 'D, d M Y H:i:s O');
}

if (!defined('DATE_RFC3339')) {
    define('DATE_RFC3339', 'Y-m-d\TH:i:sP');
}

if (!defined('DATE_RSS')) {
    define('DATE_RSS', 'D, d M Y H:i:s O');
}

if (!defined('DATE_W3C')) {
    define('DATE_W3C', 'Y-m-d\TH:i:sP');
}

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class DateTime
{
    const ATOM        = "Y-m-d\TH:i:sP";
    const COOKIE      = "l, d-M-y H:i:s T";
    const ISO8601     = "Y-m-d\TH:i:sO";
    const RFC822      = "D, d M y H:i:s O";
    const RFC850      = "l, d-M-y H:i:s T";
    const RFC1036     = "D, d M y H:i:s O";
    const RFC1123     = "D, d M Y H:i:s O";
    const RFC2822     = "D, d M Y H:i:s O";
    const RFC3339     = "Y-m-d\TH:i:sP";
    const RSS         = "D, d M Y H:i:s O";
    const W3C         = "Y-m-d\TH:i:sP";

    /**
     * Seconde since epoc 1970-01-01 00:00:00 relative to
     * the timezone given by date_default_timezone_get()
     * sample: GMT+01:00 => -3600.
     *
     * When the time stamp is out of range the year is 2000 on leap or 2001
     * to keep valid timestamp.
     *
     * @var integer
     */
    private $timestamp;

    /**
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * @var DateTimeZone
     */
    private $defaultTimezone;

    /**
     * First argument passed to the constructor
     *
     * @var mixed
     */
    private $date;

    /**
     * relative to GMT
     *
     * @var array
     */
    private $time;

    private $isLocal = false;

    private static $lastErrors = array(
        'warning_count' => 0,
        'warnings'      => array(),
        'error_count'   => 0,
        'errors'        => array(),
    );

    private static $timezoneRegex = '(?x:
        (?P<tzcorrection> # timezone correction
            (?:GMT)(?P<tzsignal>[+-])(?P<tzhours>0?[1-9]|1[0-2]):?(?P<tzminutes>[0-5][0-9])?
        )
        |(?P<tz> # timezone name
            \(?[A-Za-z]{1,6}\)?
            |[A-Z][a-z]+([_\/][A-Z][a-z]+)+
        )
    )';

    public function __construct($time = null, DateTimeZone $timezone = null)
    {
        $date = strlen($time) ? $time : 'now';
        $timestamp = strtotime($date);

        if (false === $timestamp) {
            if (is_string($time)) {
                $message = sprintf('Faild to parse string "%s"', $time);;
            } else {
                $message = sprintf('Expects parameter 1 to be string, %s given', gettype($time));
            }

            throw new Exception($message);
        }

        // Fixed the timestamp if $time does not specifies a timezone
        if (0 !== strpos($date, '@')) {
            if (preg_match('/'.self::$timezoneRegex.'$/', $date, $matches)) {
                if (null === $timezone) {
                    if (isset($matches['tz'])) {
                        try {
                            $timezone = new DateTimeZone($matches['tz']);
                            $this->isLocal = true;
                        } catch (Exception $e) {
                        }
                    } elseif (isset($matches['tzcorrection'])) {
                        $hours   = (int) $matches['tzhours'];
                        $minutes = (int) $matches['tzminutes'];
                        $signal  = $matches['tzsignal'] == '-' ? -1 : 1;
                        $timezone = new DateTimeZone('GMT');

                        $this->time['have_relative'] = true;
                        $this->time['zone_type'] = 'OFFSET';
                        $this->time['tz_offset'] = $signal * ($hours * 3600 + $minutes * 60);
                        $this->time['relative'] = array(
                            'have_weekday_relative' => false,
                            'hour'                  => $hours,
                            'minute'                => $minutes,
                            'second'                => null,
                            'month'                 => null,
                            'day'                   => null,
                            'year'                  => null,
                            'is_dst'                => -1,
                            'weekday'               => null,
                        );
                    }
                }
            } elseif (null !== $timezone) {
                $timestamp += date('Z');
            }
        }

        if (null === $timezone) {
            $this->isLocal = true;
            $this->defaultTimezone = new DateTimeZone(date_default_timezone_get());
        }

        $this->date = $time;
        $this->timezone = $timezone;
        $this->timestamp = $timestamp;
    }

    /**
     * Resets the current date of the DateTime object to a different date.
     *
     * @param integer $year  Year of the date
     * @param integer $month Month of the date
     * @param integer $day   Day of the date
     *
     * @return DateTime|FALSE The DateTime object for method chaining or FALSE on failure
     */
    public function setDate($year = null, $month = null, $day = null)
    {
        if (3 !== $argc = func_num_args()) {
            trigger_error(sprintf('DateTime::setDate() expects exactly 3 parameters, %d given', $argc), E_USER_WARNING);

            return false;
        }

        $args = array(1 => $year, 2 => $month, 3 => $day);

        foreach ($args as $argno => $arg) {
            if (null !== $arg && !is_scalar($arg) || is_string($arg) && !is_numeric($arg)) {
                trigger_error(sprintf('DateTime::setDate() expects parameter %d to be long, %s given', $argno, gettype($arg)), E_USER_WARNING);

                return false;
            }

            $args[$argno] = (integer) $arg;
        }

        $this->time['year'] = $args[1];
        $this->time['month'] = $args[2];
        $this->time['day'] = $args[3];

        $time = $this->getTime();

        $this->updateTs($time);

        return $this;
    }

    /**
     * Resets the current time of the DateTime object to a different time.
     *
     * @param integer $hour   Hour of the time
     * @param integer $minute Minute of the time
     * @param integer $second Second of the time [Optional]
     *
     * @return DateTime|FALSE The DateTime object for method chaining or FALSE on failure
     */
    public function setTime($hour = null, $minute = null, $second = 0)
    {
        if (2 > $argc = func_num_args()) {
            trigger_error(sprintf('DateTime::setTime() expects at least 2 parameters, %d given', $argc), E_USER_WARNING);

            return false;
        }

        if (3 < $argc) {
            trigger_error(sprintf('DateTime::setTime() expects at most 3 parameters, %d given', $argc), E_USER_WARNING);

            return false;
        }

        $args = array(1 => $hour, 2 => $minute, 3 => $second);

        foreach ($args as $argno => $arg) {
            if (null !== $arg && !is_scalar($arg) || is_string($arg) && !is_numeric($arg)) {
                trigger_error(sprintf('DateTime::setTime() expects parameter %d to be long, %s given', $argno, gettype($arg)), E_USER_WARNING);

                return false;
            }

            $args[$argno] = (integer) $arg;
        }

        $this->time['hour'] = $args[1];
        $this->time['minute'] = $args[2];
        $this->time['second'] = $args[3];

        $time = $this->getTime();

        $this->updateTs($time);

        return $this;
    }

    public function setTimeZone(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    public function getTimezone()
    {
        if (null === $this->timezone) {
            return $this->defaultTimezone;
        }

        return $this->timezone;
    }

    public function getTimestamp()
    {
        if (false === $this->isValidTimestamp()) {
            return false;
        }

        return (integer) $this->format('U');
    }

    /**
     * Sets the date and time based on an Unix timestamp.
     *
     * @param integer $unixtimestamp Unix timestamp representing the date
     *
     * @return DateTime|FALSE The DateTime object for method chaining or FALSE on failure
     */
    public function setTimestamp($unixtimestamp)
    {
        $this->timestamp = $unixtimestamp;

        return $this;
    }

    public function format($format)
    {
        $format = (is_object($format) && method_exists($format, '__toString')) ? $format->__toString() : $format;

        if (null !== $format && !is_scalar($format)) {
            trigger_error(sprintf('DateTime::format() expects parameter 1 to be string, %s given', gettype($format)), E_USER_WARNING);

            return false;
        }

        $timezone = $this->getTimezone();
        $timestamp = $this->timestamp;
        $timezoneOffset = 0;
        $timezoneName = $timezone->getName();

        if (false === $this->isValidTimestamp()) {
            $year = (integer) $this->time['year'];
            $sign = '';
            if (0 > $year) {
                $sign = '-';
                $year *= -1;
            }
            $year = $sign.str_pad($year, 4, '0', STR_PAD_LEFT);

            $dayText = 'Thursday';
            if (true === $this->time['have_relative'] && true === $this->time['relative']['have_weekday_relative']) {
                $dayText = $this->time['relative']['weekday_textual'];
            }
            $dayText = '\\'.implode('\\', str_split($dayText));

            $format = $this->formatReplace($format, array(
                'Y' => $year,
                'y' => substr($year, -2),
                'l' => $dayText,
                'D' => substr($dayText, 0, 6),
            ));
        }

        // backup current timezone
        $default = date_default_timezone_get();

        $timezoneOffset -= $this->setDefaultTimezone($timezoneName);

        switch ($timezoneName) {
            case 'Australia/Eucla':
                $format = $this->formatReplace($format, array(
                    'U' => $timestamp,
                    'O' => '+0845',
                    'P' => '+08:45',
                    'T' => '\\C\\W\\S\\T',
                    'e' => '\\A\\u\\s\\t\\r\\a\\l\\i\\a\\/\\E\\u\\c\\l\\a',
                ));

                if (false === $this->isLocal && null !== $this->timezone) {
                    $timezoneOffset = 0;
                }

                break;
            default:
                break;
        }

        if (false === $this->isLocal) {
            $timezoneOffset -= date('Z', $timestamp);

            if ('GMT' === date('T')) {
                $format = $this->formatReplace($format, array('T' => 'TO'));
            }
        }

        if ($this->time['have_relative'] && 'OFFSET' === $this->time['zone_type']) {
            $relative = $this->time['relative'];
            $hours   = str_pad($relative['hour'], 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($relative['minute'], 2, '0', STR_PAD_LEFT);
            $offset = $this->time['tz_offset'];
            $signal  = 0 < $offset ? '+' : '-';

            $format = $this->formatReplace($format, array(
                'U' => $timestamp - $offset,
                'P' => $signal.$hours.':'.$minutes,
                'O' => $signal.$hours.$minutes,
                'e' => $signal.$hours.':'.$minutes,
            ));
        }

        $date = date($format, $timestamp + $timezoneOffset);

        // restore current timezone
        date_default_timezone_set($default);

        return $date;
    }

    /**
     * @param string       $format
     * @param string       $time
     * @param DateTimeZone $timezone
     *
     * @return DateTime|FALSE A new DateTime instance or FALSE on failure
     */
    public static function createFromFormat($format, $time, DateTimeZone $timezone = null)
    {
        $args = array(1 => $format, 2 => $time);

        foreach ($args as $argno => $arg) {
            $args[$argno] = $arg = (is_object($arg) && method_exists($arg, '__toString')) ? $arg->__toString() : $arg;

            if (null !== $arg && !is_scalar($arg)) {
                trigger_error(sprintf('DateTime::createFromFormat() expects parameter %d to be string, %s given', $argno, gettype($arg)), E_USER_WARNING);

                return false;
            }
        }

        $format = $args[1];
        $time = $args[2];

        self::resetErrors();

        try {
            $time = self::parseFromFormat($format, $time);
        } catch (Exception $e) {
            self::addError($e->getMessage());

            return false;
        }

        if (null === $time['hour']) {
            $time['hour'] = gmdate('H');
        }
        if (null === $time['minute']) {
            $time['minute'] = gmdate('i');
        }
        if (null === $time['second']) {
            $time['second'] = gmdate('s');
        }
        if (null === $time['month']) {
            $time['month'] = gmdate('n');
        }
        if (null === $time['day']) {
            $time['day'] = gmdate('j');
        }
        if (null === $time['year']) {
            $time['year'] = gmdate('Y');
        }

        $offset = 0;
        if ('OFFSET' === $time['zone_type'] && $time['have_relative']) {
            $offset += $time['relative']['second'];

            if ($time['relative']['have_weekday_relative']) {
                $offset += $time['relative']['weekday'] * 24 * 3600;
            }
        }

        if (null !== $time['tz_offset']) {
            $offset -= $time['tz_offset'];
        }

        $timestamp = $offset + gmmktime($time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year']);

        if ($time['tz_identifier']) {
            $timezone = new DateTimeZone($time['tz_identifier']);
        }

        try {
            $date = new DateTime('@'.$timestamp, $timezone);
            $date->time = $time;
            $date->time['zone_type'] = 'ID';

            return $date;
        } catch (Exception $e) {
            self::addError($e->getMessage());

            return false;
        }
    }

    public static function getLastErrors()
    {
        return self::$lastErrors;
    }

    private static function resetErrors()
    {
        self::$lastErrors = array(
            'warning_count' => 0,
            'warnings'      => array(),
            'error_count'   => 0,
            'errors'        => array(),
        );
    }

    private static function addError($message)
    {
        ++self::$lastErrors['error_count'];
        self::$lastErrors['errors'][] = $message;
    }

    /**
     * @param string $format
     * @param string $string
     *
     * @return array
     *
     * @throws Exception
     */
    private static function parseFromFormat($format, $string)
    {
        $formatLength = strlen($format);
        $formatCursor = 0;
        $stringLength = strlen($string);
        $stringCursor = 0;
        $allowExtraChar = false;

        $time = array(
            'hour'          => null,
            'minute'        => null,
            'second'        => null,
            'month'         => null,
            'day'           => null,
            'year'          => null,
            'is_dst'        => -1,
            'have_relative' => false,
            'zone_type'     => 'ID', // OFFSET, ABBR, ID
            'tz_identifier' => null,
            'tz_offset'     => null,
        );

        $time['relative'] = array(
            'have_weekday_relative' => false,
            'hour'                  => null,
            'minute'                => null,
            'second'                => null,
            'month'                 => null,
            'day'                   => null,
            'year'                  => null,
            'is_dst'                => -1,
            'weekday'               => null,
        );

        while ($formatCursor < $formatLength && $stringCursor < $stringLength) {
            $token = $format[$formatCursor];
            $match = array();

            switch ($token) {
                case 'D': // three letter day
                case 'l': // full day
                    if (!preg_match('/(Sun(?:day)?|Mon(?:day)?|Tue(?:sday)?|Wed(?:nesday)?|Thu(?:rsday)?|Fri(?:day)?|Sat(?:urday)?)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A textual day could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['have_relative'] = true;
                    $time['relative']['have_weekday_relative'] = true;

                    switch ($match[0]) {
                        case 'Sun';
                        case 'Sunday';
                            $time['relative']['weekday'] = 3;
                            $time['relative']['weekday_textual'] = 'Sunday';
                            break;
                        case 'Mon';
                        case 'Monday';
                            $time['relative']['weekday'] = 4;
                            $time['relative']['weekday_textual'] = 'Monday';
                            break;
                        case 'Tue';
                        case 'Tuesday';
                            $time['relative']['weekday'] = 5;
                            $time['relative']['weekday_textual'] = 'Tuesday';
                            break;
                        case 'Wed';
                        case 'Wednesday';
                            $time['relative']['weekday'] = 6;
                            $time['relative']['weekday_textual'] = 'Wednesday';
                            break;
                        case 'Thu';
                        case 'Thursday';
                            $time['relative']['weekday'] = 0;
                            $time['relative']['weekday_textual'] = 'Thursday';
                            break;
                        case 'Fri';
                        case 'Friday';
                            $time['relative']['weekday'] = 1;
                            $time['relative']['weekday_textual'] = 'Friday';
                            break;
                        case 'Sat';
                        case 'Saturday';
                            $time['relative']['weekday'] = 2;
                            $time['relative']['weekday_textual'] = 'Saturday';
                            break;
                        default:
                            break;
                    }

                    break;
                case 'd': // two digit day, with leading zero
                case 'j': // two digit day, without leading zero
                    if (!preg_match('/([0-2]\d|3[01]|0?\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit day could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['day'] = $match[0];

                    break;
                case 'S': // day suffix, ignored, nor checked
                    if (!preg_match('/(st|nd|rd|th)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('English ordinal suffix for the day of the month, 2 characters could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case 'z': // day of year - resets month (0 based)
                    if (!preg_match('/(3[0-6]{2}|[1-2][\d]{2}|0?[\d]{2}|[0]{0,2}\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A three digit day-of-year could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['month'] = 1;
                    $time['day'] = $match[0] + 1;
                    $time = self::doNormalize($time);

                    break;
                case 'm': // two digit month, with leading zero
                case 'n': // two digit month, without leading zero
                    if (!preg_match('/(1[0-2]|0?\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit month could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['month'] = $match[0];

                    break;
                case 'M': // three letter month
                case 'F': // full month
                    if (!preg_match('/(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A textual month could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    switch ($match[0]) {
                        case 'Jan':
                        case 'January':
                            $time['month'] = 1;
                            break;
                        case 'Feb':
                        case 'February':
                            $time['month'] = 2;
                            break;
                        case 'Mar':
                        case 'March':
                            $time['month'] = 3;
                            break;
                        case 'Apr':
                        case 'April':
                            $time['month'] = 4;
                            break;
                        case 'May':
                            $time['month'] = 5;
                            break;
                        case 'Jun':
                        case 'June':
                            $time['month'] = 6;
                            break;
                        case 'Jul':
                        case 'July':
                            $time['month'] = 7;
                            break;
                        case 'Aug':
                        case 'August':
                            $time['month'] = 8;
                            break;
                        case 'Sep':
                        case 'September':
                            $time['month'] = 9;
                            break;
                        case 'Oct':
                        case 'October':
                            $time['month'] = 10;
                            break;
                        case 'Nov':
                        case 'November':
                            $time['month'] = 11;
                            break;
                        case 'Dec':
                        case 'December':
                            $time['month'] = 12;
                            break;
                        default:
                            break;
                    }

                    break;
                case 'y': // two digit year
                    if (!preg_match('/([\d]{2})/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit year could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $prefix = '20';
                    if (38 < $match[0]) {
                        $prefix = '19';
                    }

                    $time['year'] = $prefix.$match[0];

                    break;
                case 'Y': // four digit year
                    if (!preg_match('/([\d]{1,4})/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A four digit year could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['year'] = $match[0];

                    break;
                case 'g': // two digit hour, with leading zero
                case 'h': // two digit hour, without leading zero
                    if (!preg_match('/(1[0-2]|0?\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit hour could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['hour'] = $match[0];

                    break;
                case 'G': // two digit hour, with leading zero
                case 'H': // two digit hour, without leading zero
                    if (!preg_match('/(2[0-3]|1\d|0?\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit hour could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['hour'] = $match[0];

                    break;
                case 'a': /* am/pm/a.m./p.m. */
                case 'A': /* AM/PM/A.M./P.M. */
                    if (!preg_match('/(([ap]m)\.?)/Ai', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit hour could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    if ('pm' === strtolower($match[2])) {
                        $time['hour'] += 12;
                    }

                    break;
                case 'i': // two digit minute, with leading zero
                    if (!preg_match('/([0-5]\d|0\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit minute could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['minute'] = $match[0];

                    break;
                case 's': // two digit second, with leading zero
                    if (!preg_match('/([0-5]\d|0\d)/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A two digit second could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['second'] = $match[0];

                    break;
                case 'u': // up to six digit millisecond
                    if (!preg_match('/([\d]{1,6})/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A six digit millisecond could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case ' ': // any sort of whitespace (' ' and \t)
                    if (!preg_match('/([ \t])/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('Any sort of whitespace could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case 'U': // epoch seconds
                    if (!preg_match('/([\d]{1,24})/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A 24 digit unix timestamp could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    $time['have_relative'] = true;
                    $time['relative']['second'] += $match[0];

                    $time['zone_type'] = 'OFFSET';
                    $time['tz_identifier'] = 'GMT';

                    $time['year'] = '1970';
                    $time['hour'] = '00';
                    $time['minute'] = '00';
                    $time['second'] = '00';
                    $time['month'] = '1';
                    $time['day'] = '1';
                    $time['is_dst'] = 0;

                    break;
                case 'O': // Difference to Greenwich time (GMT)
                case 'P': // Difference to Greenwich time (GMT) with colon between hours and minutes
                    if (!preg_match('/((?:GMT)|(?P<sign>[+-])(?P<hours>1[0-2]|0\d):?(?P<minutes>[0-5]\d|0\d))/Ai', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('The timezone near "... %s ..." could not be found in the database.', substr($string, $stringCursor, 10)));
                    }

                    if ('GMT' !== $match[0]) {
                        $time['tz_offset'] = $match['sign'].($match['hours'] * 3600 + $match['minutes'] * 60);
                    }

                    $time['tz_identifier'] = 'GMT';

                    break;
                case 'T': // Timezone abbreviation
                    $abbrs = DateTimeZone::listAbbreviations();

                    if (!preg_match('#('.implode('|', array_keys($abbrs)).')#Ai', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('The timezone near "... %s ..." could not be found in the database.', substr($string, $stringCursor, 10)));
                    }

                    $time['zone_type'] = 'ABBR';

                    if ('gmt' === strtolower($match[0])) {
                        if (preg_match('/((?P<sign>[+-])(?P<hours>1[0-2]|0\d):?(?P<minutes>[0-5]\d|0\d))/A', $string, $match2, null, $stringCursor + 3)) {
                            $time['tz_offset'] = $match2['sign'].($match2['hours'] * 3600 + $match2['minutes'] * 60);

                            $stringCursor += strlen($match2[0]);
                        }
                    }

                    $zone = $abbrs[strtolower($match[0])][0];
                    if (0 === $zone['offset']) {
                        $timezone_id = 'GMT';
                        $time['is_dst'] = 0;
                    } else {
                        $timezone_id = $zone['timezone_id'];
                        $time['is_dst'] = 1;
                    }

                    $time['tz_offset'] += $zone['offset'];

                    $time['tz_identifier'] = $timezone_id;

                    break;
                case 'e': // Timezone identifier
                    if (!preg_match('#('.implode('|', DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC)).')#A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('The timezone near "... %s ..." could not be found in the database.', substr($string, $stringCursor, 10)));
                    }

                    $time['tz_identifier'] = $match[0];

                    break;
                case '#':
                    if (!preg_match('/([;:\/\.,-])/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('The separation symbol ([;:/.,-]) could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case ';':
                case ':':
                case '/':
                case '.':
                case ',':
                case '-':
                case '(':
                case ')':
                    if (!preg_match('/('.preg_quote($token, '/').')/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('The separation symbol could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case '!': // reset all fields to default
                    $time = self::timeResetFields($time);

                    break;
                case '|': // reset all fields to default when not set
                    $time = self::timeResetUnsetFields($time);

                    break;
                case '?': // random char
                    ++$stringCursor;

                    break;
                case '\\': // escaped char
                    ++$formatCursor;

                    if (!preg_match('/('.preg_quote($format[$formatCursor], '/').')/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('The escaped character could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case '*': // random chars until a separator or number ([ \t.,:;/-0123456789])
                    if (!preg_match('/([ \t\.,:;\/0-9-])/A', $string, $match, null, $stringCursor)) {
                        throw new InvalidArgumentException(sprintf('A random chars until a separator or number ([ \t.,:;/-0123456789]) could not be found near "... %s ..."', substr($string, $stringCursor, 10)));
                    }

                    break;
                case '+': // allow extra chars in the format
                    $allowExtraChar = true;

                    break;
                default:
                    if ($token !== $string[$stringCursor]) {
                        throw new InvalidArgumentException(sprintf('Unable to parse time string format near "... %s ..."', substr($format, $formatCursor, 10)));
                    }
                    ++$stringCursor;
            }

            ++$formatCursor;
            if (isset($match[0])) {
                $stringCursor += strlen($match[0]);
            }
        }

        if ($stringCursor < $stringLength) {
            if (false === $allowExtraChar) {
                throw new InvalidArgumentException(sprintf('Trailing data near "... %s ..."', substr($string, $stringCursor, 10)));
            }
        }

        // ignore trailing +'s
        while ($formatCursor < $formatLength && '+' === $format[$formatCursor]) {
            ++$formatCursor;
        }
        if ($formatCursor < $formatLength) {
            $done = false;

            while ($formatCursor < $formatLength && false === $done) {
                switch ($format[$formatCursor]) {
                    case '!': // reset all fields to default
                        $time = self::timeResetFields($time);

                        break;
                    case '|': // reset all fields to default when not set
                        $time = self::timeResetUnsetFields($time);

                        break;
                    default:
                        throw new InvalidArgumentException(sprintf('Data missing near "... %s ..."', substr($format, $formatCursor, 10)));
                        $done = true;
                }

                ++$formatCursor;
            }
        }

        // clean up a bit
        if (null !== $time['hour'] || null !== $time['minute'] || null !== $time['second']) {
            if (null === $time['hour']) {
                $time['hour'] = 0;
            }
            if (null === $time['minute']) {
                $time['minute'] = 0;
            }
            if (null === $time['second']) {
                $time['second'] = 0;
            }
        }

        return $time;
    }

    /**
     * @param array $time
     *
     * @return array $time
     */
    private static function timeResetFields(array $time)
    {
        $time['year'] = '1970';
        $time['hour'] = '00';
        $time['minute'] = '00';
        $time['second'] = '00';
        $time['month'] = '1';
        $time['day'] = '1';
        $time['tz_identifier'] = 'GMT';
        $time['is_dst'] = 0;

        return $time;
    }

    /**
     * @param array $time
     *
     * @return array $time
     */
    private static function timeResetUnsetFields(array $time)
    {
        if (null === $time['year']) {
            $time['year'] = '1970';
        }
        if (null === $time['hour']) {
            $time['hour'] = '00';
        }
        if (null === $time['minute']) {
            $time['minute'] = '00';
        }
        if (null === $time['second']) {
            $time['second'] = '00';
        }
        if (null === $time['month']) {
            $time['month'] = '1';
        }
        if (null === $time['day']) {
            $time['day'] = '1';
        }

        return $time;
    }

    private function updateTs(array $time)
    {
        $time = $this->doNormalize($time);
        $this->time = $time;

        if (false === $this->isValidTimestamp()) {
            // Sets a year with the same number of day
            $time['year'] = $this->isLeap($time['year']) ? 2000 : 2001;
        }

        $this->timestamp = gmmktime($time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year']);
        $this->isLocal = false;
    }

    /**
     * Converts a year to the unix timestamp
     *
     * @param integer $year
     *
     * @return integer The unix timestamp
     */
    private function doYears($year)
    {
        $res = 0;
        $eras = 0;

        $secsPerEra = 12622780800;
        $daysPerLYear = 366;
        $daysPerYear = 365;
        $secsPerDay = 86400;

        $eras = ($year - 1970) / 40000;
        if (0 !== (integer) $eras) {
            $year -= $eras * 40000;
            $res += $secsPerEra * $eras * 100;
        }

        if (1970 <= $year) {
            for ($i = $year - 1; 1970 <= $i; $i--) {
                if ($this->isLeap($i)) {
                    $res += ($daysPerLYear * $secsPerDay);
                } else {
                    $res += ($daysPerYear * $secsPerDay);
                }
            }
        } else {
            for ($i = 1969; $i >= $year; $i--) {
                if ($this->isLeap($i)) {
                    $res -= ($daysPerLYear * $secsPerDay);
                } else {
                    $res -= ($daysPerYear * $secsPerDay);
                }
            }
        }

        return $res;
    }

    /**
     * Converts a month to the unix timestamp
     *
     * @param integer $month
     * @param integer $year
     *
     * @return integer The unix timestamp
     */
    private function doMonths($month, $year)
    {
        $secsPerDay = 86400;

        /*                    jan  feb  mrt  apr  may  jun  jul  aug  sep  oct  nov  dec */
        $monthTabLeap = array( -1,  30,  59,  90, 120, 151, 181, 212, 243, 273, 304, 334);
        $monthTab     = array(  0,  31,  59,  90, 120, 151, 181, 212, 243, 273, 304, 334);

        if ($this->isLeap($year)) {
            return ($monthTabLeap[$month - 1] + 1) * $secsPerDay;
        } else {
            return ($monthTab[$month - 1]) * $secsPerDay;
        }
    }

    /**
     * Converts a day to the unix timestamp
     *
     * @param integer $day
     *
     * @return integer The unix timestamp
     */
    private function doDays($day)
    {
        $secsPerDay = 86400;

        return ($day - 1) * $secsPerDay;
    }

    /**
     * Converts a time to the unix timestamp
     *
     * @param integer $hour
     * @param integer $minute
     * @param integer $second
     *
     * @return integer The unix timestamp
     */
    private function doTime($hour, $minute, $second)
    {
        $res = 0;

        $res += $hour * 3600;
        $res += $minute * 60;
        $res += $second;

        return $res;
    }

    private static function doNormalize(array $time)
    {
        if (null !== $time['second']) {
            do {} while (self::doRangeLimit(0, 60, 60, $time['second'], $time['minute']));
        }
        if (null !== $time['second']) {
            do {} while (self::doRangeLimit(0, 60, 60, $time['minute'], $time['hour']));
        }
        if (null !== $time['second']) {
            do {} while (self::doRangeLimit(0, 24, 24, $time['hour'], $time['day']));
        }

        do {} while (self::doRangeLimit(1, 13, 12, $time['month'], $time['year']));
        do {} while (self::doRangeLimitDays($time['year'], $time['month'], $time['day']));
        do {} while (self::doRangeLimit(1, 13, 12, $time['month'], $time['year']));

        return $time;
    }

    private static function doRangeLimit($start, $end, $adj, &$a, &$b)
    {
        if ($a < $start) {
            $b -= (integer) (abs($a) / $adj + 1);
            $a = $adj - abs($a) % $adj;
        }

        if ($a >= $end) {
            $b += (integer) ($a / $adj);
            $a %= $adj;
        }

        return false;
    }

    private static function doRangeLimitDays(&$y, &$m, &$d)
    {
        $leapyear = 0;
        $daysThisMonth = 0;
        $lastMonth = 0;
        $lastYear = 0;
        $daysLastMonth = 0;
        $daysPerLYearPeriod = 146097;
        $yearsPerLYearPeriod = 400;
        /*                       dec jan feb mrt apr may jun jul aug sep oct nov dec */
        $daysInMonthLeap = array( 31, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
        $daysInMonth     = array( 31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

        /* can jump an entire leap year period quickly */
        if ($d >= $daysPerLYearPeriod || $d <= -$daysPerLYearPeriod) {
            $y += $yearsPerLYearPeriod * ($d / $daysPerLYearPeriod);
            $d -= $daysPerLYearPeriod * ($d / $daysPerLYearPeriod);
        }

        self::doRangeLimit(1, 13, 12, $m, $y);

        $leapyear = self::isLeap($y);
        $daysThisMonth = $leapyear ? $daysInMonthLeap[$m] : $daysInMonth[$m];
        $lastMonth = $m - 1;

        if ($lastMonth < 1) {
            $lastMonth += 12;
            $lastYear = $y - 1;
        } else {
            $lastYear = $y;
        }
        $leapyear = self::isLeap($lastYear);
        $daysLastMonth = $leapyear ? $daysInMonthLeap[$lastMonth] : $daysInMonth[$lastMonth];

        if ($d <= 0) {
            $d += $daysLastMonth;
            $m--;

            return true;
        }

        if ($d > $daysThisMonth) {
            $d -= $daysThisMonth;
            $m++;

            return true;
        }

        return false;
    }

    private static function isLeap($y)
    {
        return ($y % 4 === 0) && (($y % 100 !== 0) || ($y % 400 === 0));
    }

    private function getTime()
    {
        if (!isset($this->time['year'])) {
            $this->time['year'] = gmdate('Y', $this->timestamp);
        }
        if (!isset($this->time['month'])) {
            $this->time['month'] = gmdate('n', $this->timestamp);
        }
        if (!isset($this->time['day'])) {
            $this->time['day'] = gmdate('j', $this->timestamp);
        }
        if (!isset($this->time['hour'])) {
            $this->time['hour'] = gmdate('H', $this->timestamp);
        }
        if (!isset($this->time['minute'])) {
            $this->time['minute'] = gmdate('i', $this->timestamp);
        }
        if (!isset($this->time['second'])) {
            $this->time['second'] = gmdate('s', $this->timestamp);
        }
        if (!isset($this->time['have_relative'])) {
            $this->time['have_relative'] = false;
        }

        return $this->time;
    }

    private function isValidTimestamp()
    {
        $time = $this->getTime();

        if (1901 > $time['year']) {
            return false;
        }
        if (1901 === $time['year']) {
            if (12 > $time['month']) {
                return false;
            }
            if (12 === $time['month']) {
                if (13 > $time['day']) {
                    return false;
                }
                if (13 === $time['day']) {
                    if (20 > $time['hour']) {
                        return false;
                    }
                    if (20 === $time['hour']) {
                        if (45 > $time['minute']) {
                            return false;
                        }
                        if (45 === $time['minute']) {
                            if (45 > $time['second']) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        if (2038 < $time['year']) {
            return false;
        }
        if (2038 === $time['year']) {
            if (1 < $time['month']) {
                return false;
            }
            if (1 === $time['month']) {
                if (19 < $time['day']) {
                    return false;
                }
                if (19 === $time['day']) {
                    if (3 < $time['hour']) {
                        return false;
                    }
                    if (3 === $time['hour']) {
                        if (14 < $time['minute']) {
                            return false;
                        }
                        if (14 === $time['minute']) {
                            if (7 < $time['second']) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function setDefaultTimezone($tzIdentifier)
    {
        $timestampDiff = 0;

        if ('Australia/Eucla' === $tzIdentifier) {
            $tzIdentifier = 'UTC';
            $timestampDiff -= 31500;
        }

        date_default_timezone_set($tzIdentifier);

        return $timestampDiff;
    }

    private function formatReplace($format, array $replacePairs)
    {
        $pattern = array();
        $replacement = array();

        foreach ($replacePairs as $from => $to) {
            $pattern[] = '/((?:^|[^\x5C])(?:\x5C\x5C)*)'.preg_quote($from, '/').'/';
            $replacement[] = '${1}'.$to;
        }

        return preg_replace($pattern, $replacement, $format);
    }
}
