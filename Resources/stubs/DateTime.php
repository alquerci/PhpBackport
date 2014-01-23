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
        return $this->format('U');
    }

    public function format($format)
    {
        $timezone = $this->getTimezone();
        $gmtDiff = null;

        $default = date_default_timezone_get();
        date_default_timezone_set($timezone->getName());
        if (null === $this->timezone) {
            $date = date($format, $this->timestamp);
        } else {
            if (preg_match('/[^\\\]?T/', $format) && 'GMT' === date('T')) {
                $gmtDiff = 'O';
            }

            $date = date($format.$gmtDiff, $this->timestamp - date('Z', $this->timestamp));
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
                            break;
                        case 'Mon';
                        case 'Monday';
                            $time['relative']['weekday'] = 4;
                            break;
                        case 'Tue';
                        case 'Tuesday';
                            $time['relative']['weekday'] = 5;
                            break;
                        case 'Wed';
                        case 'Wednesday';
                            $time['relative']['weekday'] = 6;
                            break;
                        case 'Thu';
                        case 'Thursday';
                            $time['relative']['weekday'] = 0;
                            break;
                        case 'Fri';
                        case 'Friday';
                            $time['relative']['weekday'] = 1;
                            break;
                        case 'Sat';
                        case 'Saturday';
                            $time['relative']['weekday'] = 2;
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

                    $time['year'] = substr($time['year'], 0, -2).$match[0];

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
}
