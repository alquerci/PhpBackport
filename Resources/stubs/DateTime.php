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
     * @var integer
     */
    private $timestamp;

    private $timezone_type;

    /**
     * @var DateTimeZone
     */
    private $timezone;

    private $date;

    private $time;

    public function __construct($time = null, DateTimeZone $timezone = null)
    {
        if (false === $this->timestamp = strtotime(strlen($time) ? $time : 'now')) {
            if (is_string($time)) {
                $message = sprintf('Faild to parse string "%s"', $time);;
            } else {
                $message = sprintf('Expects parameter 1 to be string, %s given', gettype($time));
            }

            throw new Exception($message);
        }

        $this->date = $time;
        $this->timezone = $timezone;
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

        if (null === $year || false === $year) {
            $year = 0;
        }
        if (!is_scalar($year) || is_string($year)) {
            trigger_error(sprintf('DateTime::setDate() expects parameter 1 to be long, %s given', gettype($year)), E_USER_WARNING);

            return false;
        }

        if (null === $month || false === $month) {
            $month = 0;
        }
        if (!is_scalar($month) || is_string($month)) {
            trigger_error(sprintf('DateTime::setDate() expects parameter 2 to be long, %s given', gettype($month)), E_USER_WARNING);

            return false;
        }

        if (null === $day || false === $day) {
            $day = 0;
        }
        if (!is_scalar($day) || is_string($day)) {
            trigger_error(sprintf('DateTime::setDate() expects parameter 3 to be long, %s given', gettype($day)), E_USER_WARNING);

            return false;
        }

        $time = $this->getTime();
        $time['year'] = (integer) $year;
        $time['month'] = (integer) $month;
        $time['day'] = (integer) $day;

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

        if (null === $hour || false === $hour) {
            $hour = 0;
        }
        if (!is_scalar($hour) || is_string($hour)) {
            trigger_error(sprintf('DateTime::setTime() expects parameter 1 to be long, %s given', gettype($hour)), E_USER_WARNING);

            return false;
        }

        if (null === $minute || false === $minute) {
            $minute = 0;
        }
        if (!is_scalar($minute) || is_string($minute)) {
            trigger_error(sprintf('DateTime::setTime() expects parameter 2 to be long, %s given', gettype($minute)), E_USER_WARNING);

            return false;
        }

        if (null === $second || false === $second) {
            $second = 0;
        }
        if (!is_scalar($second) || is_string($second)) {
            trigger_error(sprintf('DateTime::setTime() expects parameter 3 to be long, %s given', gettype($second)), E_USER_WARNING);

            return false;
        }

        $time = $this->getTime();
        $time['hour'] = (integer) $hour;
        $time['minute'] = (integer) $minute;
        $time['second'] = (integer) $second;

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
            if ('GMT' === $this->date
                || (strlen($this->date) - strlen('GMT')) === strpos($this->date, 'GMT')
            ) {
                return new DateTimeZone('GMT');
            } else {
                return new DateTimeZone(date_default_timezone_get());
            }
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
        $timezone = $this->getTimezone();
        $gmtDiff = null;

        $timestamp = $this->timestamp;

        if (false === $this->isValidTimestamp()) {
            $timestamp = gmmktime($this->time['hour'], $this->time['minute'], $this->time['second'], $this->time['month'], $this->time['day'], 1970);

            $year = (integer) $this->time['year'];
            $absYear = abs($year);
            if (4 > $len = strlen($absYear)) {
                if (0 > $year) {
                    $year = '-';
                } else {
                    $year = '';
                }

                $year .= str_repeat('0', 4 - $len).$absYear;
            }

            $format = preg_replace('/(^|[^\x5C]|\x5C\x5C)Y/', '${1}'.$year, $format);
            $format = preg_replace('/(^|[^\x5C]|\x5C\x5C)y/', '${1}'.substr($year, -2), $format);

            $dayText = 'Thursday';
            if (true === $this->time['have_relative'] && true === $this->time['relative']['have_weekday_relative']) {
                $dayText = $this->time['relative']['weekday_textual'];
            }
            $dayText = '\\'.implode('\\', str_split($dayText));
            $format = preg_replace('/(^|[^\x5C]|\x5C\x5C)l/', '${1}'.$dayText, $format);
            $format = preg_replace('/(^|[^\x5C]|\x5C\x5C)D/', '${1}'.substr($dayText, 0, 6), $format);
        }

        $default = date_default_timezone_get();
        date_default_timezone_set($timezone->getName());
        if (null === $this->timezone) {
            $date = date($format, $timestamp);
        } else {
            if (preg_match('/(^|[^\x5C]|\x5C\x5C)T$/', $format) && 'GMT' === date('T')) {
                $gmtDiff = 'O';
            }

            $date = date($format.$gmtDiff, $timestamp - date('Z', $timestamp));
        }
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
        try {
            $time = self::parseFromFormat($format, $time);
        } catch (Exception $e) {
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

        if (0 == $time['year']) {
            $timestamp = -2147483646;
        }

        try {
            $date = new DateTime('@'.$timestamp, $timezone);
            $date->time = $time;

            return $date;
        } catch (Exception $e) {
            return false;
        }
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

        $this->timestamp = gmmktime($time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year']);
        $this->time = $time;
        $this->timezone = $this->getTimezone();
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

    private function doNormalize(array $time)
    {
        if (null !== $time['second']) {
            do {} while ($this->doRangeLimit(0, 60, 60, $time['second'], $time['minute']));
        }
        if (null !== $time['second']) {
            do {} while ($this->doRangeLimit(0, 60, 60, $time['minute'], $time['hour']));
        }
        if (null !== $time['second']) {
            do {} while ($this->doRangeLimit(0, 24, 24, $time['hour'], $time['day']));
        }

        do {} while ($this->doRangeLimit(1, 13, 12, $time['month'], $time['year']));
        do {} while ($this->doRangeLimitDays($time['year'], $time['month'], $time['day']));
        do {} while ($this->doRangeLimit(1, 13, 12, $time['month'], $time['year']));

        return $time;
    }

    private function doRangeLimit($start, $end, $adj, &$a, &$b)
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

    private function doRangeLimitDays(&$y, &$m, &$d)
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

        $this->doRangeLimit(1, 13, 12, $m, $y);

        $leapyear = $this->isLeap($y);
        $daysThisMonth = $leapyear ? $daysInMonthLeap[$m] : $daysInMonth[$m];
        $lastMonth = $m - 1;

        if ($lastMonth < 1) {
            $lastMonth += 12;
            $lastYear = $y - 1;
        } else {
            $lastYear = $y;
        }
        $leapyear = $this->isLeap($lastYear);
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

    private function isLeap($y)
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
}
