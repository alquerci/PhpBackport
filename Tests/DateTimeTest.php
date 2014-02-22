<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Instinct_Component_PhpBackport_Tests_DateTimeTest extends PHPUnit_Framework_TestCase
{
    private $defaultTimezone;

    public function setUp()
    {
        $this->defaultTimezone = date_default_timezone_get();
    }

    public function tearDown()
    {
        date_default_timezone_set($this->defaultTimezone);
    }

    public function testConstructDstOverlap()
    {
        date_default_timezone_set('America/New_York');

        $d = new DateTime('2011-11-06 01:30:00');

        $this->assertEquals('-04:00', $d->format('P'));
    }

    /**
     * @dataProvider getConstructBasic1Data
     */
    public function testConstructBasic1($expected, $value)
    {
        date_default_timezone_set('Europe/London');

        $d = new DateTime($value);

        date_default_timezone_set('America/New_York');

        $this->assertEquals($expected, $d->format('e'));
    }

    public function getConstructBasic1Data()
    {
        return array(
            array('Europe/London', ''),
            array('GMT', 'GMT'),
            array('Europe/London', '2005-07-14 22:30:41'),
            array('GMT', '2005-07-14 22:30:41 GMT'),
            array('+08:00', 'GMT+08:00'),
            array('+08:00', '2005-07-14 22:30:41 GMT+0800'),
        );
    }

    /**
     * @dataProvider getConstructPassingUnexpectedValuesData
     */
    public function testConstructPassingUnexpectedValues($time, DateTimeZone $timezone = null, $expected = null)
    {
        date_default_timezone_set("Europe/London");

        try {
            $d = new DateTime($time);

            if (null === $expected) {
                $this->fail('Expected throws an Exception');
            }

            $this->assertEquals($expected, $d->getTimezone()->getName());
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            throw $e;
        } catch (Exception $e) {
        }

        try {
            $d = new DateTime($time, $timezone);

            if (null === $expected) {
                $this->fail('Expected throws an Exception');
            }

            $this->assertEquals($expected, $d->getTimezone()->getName());
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            throw $e;
        } catch (Exception $e) {
        }
    }

    public function getConstructPassingUnexpectedValuesData()
    {
        $timezone = new DateTimeZone("Europe/London");

        return array(
            array(0, $timezone),
            array(1, $timezone),
            array(12345, $timezone),
            array(-12345, $timezone),
            array(10.5, $timezone, "Europe/London"),
            array(-10.5, $timezone),
            array(.5, $timezone, "Europe/London"),
            array(array(), $timezone),
            array(array(1, 2, 3), $timezone),
            array(array('one' => 1, 'two' => 2), $timezone),
            array(null, $timezone, "Europe/London"),
            array(true, $timezone),
            array(false, $timezone, "Europe/London"),
            array('', $timezone, "Europe/London"),
            array('sTrInG', $timezone),
            array('hello world', $timezone),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), $timezone),
            array(new stdClass(), $timezone),
            array(fopen(__FILE__, 'r'), $timezone),
            array('0', $timezone),
            array('1', $timezone),
            array('12345', $timezone),
            array('-12345', $timezone),
            array('10.5', $timezone, "Europe/London"),
            array('-10.5', $timezone),
            array('.5', $timezone, "Europe/London"),
        );
    }

    /**
     * @dataProvider getConstructWithDifferentDefaultTimezoneData
     *
     * @param string $defaultTimezone
     */
    public function testConstructWithDifferentDefaultTimezone($defaultTimezone, DateTimeZone $timezone = null, $expected = null)
    {
        date_default_timezone_set($defaultTimezone);

        $date = new DateTime('01-Jan-70 00:00:00', $timezone);

        $this->assertEquals($expected, $date->format('U'));
    }

    public function getConstructWithDifferentDefaultTimezoneData()
    {
        return array(
            array('Etc/GMT-1', new DateTimeZone("Australia/Darwin"), -34200),
            array('Etc/GMT-0', new DateTimeZone("Australia/Darwin"), -34200),
            array('Etc/GMT+1', new DateTimeZone("Australia/Darwin"), -34200),
            array('Etc/GMT-1', null, -3600),
            array('Etc/GMT+0', null, 0),
            array('Etc/GMT+1', null, 3600),
        );
    }

    /**
     * @dataProvider getConstructWithTimezoneOnTimeData
     *
     * @param string $format
     * @param string $expected
     * @param string $time
     */
    public function testConstructWithTimezoneOnTime($format, $expected, $time = '01-Jan-70 00:00:00 GMT+0802')
    {
        date_default_timezone_set('Australia/Darwin');

        $date = new DateTime($time);

        $this->assertEquals($expected, $date->format($format));
    }

    public function getConstructWithTimezoneOnTimeData()
    {
        return array(
            array('U', '-28920'),
            array('e', '+08:02'),
            array(DateTime::COOKIE, 'Thursday, 01-Jan-70 00:00:00 GMT+0802'),
            array(DateTime::RFC2822, 'Thu, 01 Jan 1970 00:00:00 +0802'),
            array(DateTime::ATOM, '1970-01-01T00:00:00+08:02'),
            array('c', '1970-01-01T00:00:00-05:00', '01-Jan-70 00:00:00 America/New_York'),
            array('c', '1970-01-01T00:00:00+00:00', '@0 UTC'),
        );
    }

    /**
     * @dataProvider getFormatBasic1Data
     *
     * @param string $expected
     * @param string $format
     */
    public function testFormatBasic1($expected, $format)
    {
        date_default_timezone_set("Europe/London");

        $date = new DateTime("2005-07-14 22:30:41");
        $this->assertSame($expected, $date->format($format));
    }

    public function getFormatBasic1Data()
    {
        return array(
            array('July 14, 2005, 10:30 pm', 'F j, Y, g:i a'),
            array('07.14.05', 'm.d.y'),
            array('14, 7, 2005', 'j, n, Y'),
            array('20050714', 'Ymd'),
            array('10-30-41, 14-07-05, 3031 3041 4 Thupm05', 'h-i-s, j-m-y, it is w Day'),
            array('it is the 14th day.', '\i\t \i\s \t\h\e jS \d\a\y.'),
            array('Thu Jul 14 22:30:41 BST 2005', 'D M j G:i:s T Y'),
            array('22:07:41 m is month', 'H:m:s \m \i\s\ \m\o\n\t\h'),
            array('22:30:41', 'H:i:s'),
        );
    }

    /**
     * @dataProvider getFormatBasic2Data
     *
     * @param string $expected
     * @param string $format
     */
    public function testFormatBasic2($expected, $format)
    {
        date_default_timezone_set("Europe/London");

        $date = new DateTime("2005-07-14 22:30:41");

        $this->assertSame($expected, $date->format($format));
    }

    public function getFormatBasic2Data()
    {
        return array(
            array('2005-07-14T22:30:41+01:00', DateTime::ATOM),
            array('Thursday, 14-Jul-05 22:30:41 BST', DateTime::COOKIE),
            array('2005-07-14T22:30:41+0100', DateTime::ISO8601),
            array('Thu, 14 Jul 05 22:30:41 +0100', DateTime::RFC822),
            array('Thursday, 14-Jul-05 22:30:41 BST', DateTime::RFC850),
            array('Thu, 14 Jul 05 22:30:41 +0100', DateTime::RFC1036),
            array('Thu, 14 Jul 2005 22:30:41 +0100', DateTime::RFC1123),
            array('Thu, 14 Jul 2005 22:30:41 +0100', DateTime::RFC2822),
            array('2005-07-14T22:30:41+01:00', DateTime::RFC3339),
            array('Thu, 14 Jul 2005 22:30:41 +0100', DateTime::RSS),
            array('2005-07-14T22:30:41+01:00', DateTime::W3C),
        );
    }

    /**
     * @dataProvider getFormatWithBackslashesData
     *
     * @param string $format
     * @param string $expected
     */
    public function testFormatWithBackslashes($format, $expected)
    {
        date_default_timezone_set("Europe/London");

        $date = DateTime::createFromFormat('U', 0);

        $this->assertSame($expected, $date->format($format));
    }

    public function getFormatWithBackslashesData()
    {
        return array(
            array('T', 'GMT+0000'),
            array('\\T', 'T'),
            array('\\\\T', '\\GMT+0000'),
            array('\\\\\\T', '\\T'),
            array('_T', '_GMT+0000'),
            array('_\\T', '_T'),
            array('_\\\\T', '_\\GMT+0000'),
            array('_\\\\\\T', '_\\T'),
        );
    }

    /**
     * @dataProvider getFormatPassingUnexpectedValuesData
     */
    public function testFormatPassingUnexpectedValues($format, $expected)
    {
        date_default_timezone_set("Europe/London");

        $d = new DateTime('2005-07-14 22:30:41');

        try {
            $this->assertSame($expected, $d->format($format));

            if (false === $expected) {
                $this->fail('DateTime::format() trigger a warning with unexpected value at argument 1 $format');
            }
        } catch (PHPUnit_Framework_Error $e) {
            if (false !== $expected) {
                throw $e;
            }

            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals(sprintf('DateTime::format() expects parameter 1 to be string, %s given', gettype($format)), $e->getMessage());
        }

        $this->assertSame($expected, @$d->format($format));

        if (is_resource($format)) {
            @fclose($format);
        }
    }

    public function getFormatPassingUnexpectedValuesData()
    {
        return array(
            array(0, '0'),
            array(1, '1'),
            array(12345, '12345'),
            array(-12345, '-12345'),
            array(10.5, '10.5'),
            array(-10.5, '-10.5'),
            array(.5, '0.5'),
            array(array(), false),
            array(array(1, 2, 3), false),
            array(array('one' => 1, 'two' => 2), false),
            array(null, ''),
            array(true, '1'),
            array(false, ''),
            array('', ''),
            array('string', '4131Thu, 14 Jul 2005 22:30:41 +010030710'),
            array('sTrInG', '41BSTThu, 14 Jul 2005 22:30:41 +01001722'),
            array('hello world', '10Europe/LondonThursdayThursday2005 42005Thu, 14 Jul 2005 22:30:41 +0100Thursday14'),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), 'CThursdaypm4141 PM 2005b14Europe/London2005-07-14T22:30:41+01:0031'),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', '0'),
            array('1', '1'),
            array('12345', '12345'),
            array('-12345', '-12345'),
            array('10.5', '10.5'),
            array('-10.5', '-10.5'),
            array('.5', '.5'),
        );
    }

    /**
     * @dataProvider getFormatOutOfTimestampRangeData
     *
     * @param string $format
     * @param string $expected
     */
    public function testFormatOutOfTimestampRange($format, $expected)
    {
        date_default_timezone_set("Australia/Darwin");

        $date = DateTime::createFromFormat(DateTime::RFC2822, 'Sat, 01 Jan 0000 00:00:00 +0000');
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $this->assertEquals($expected, $date->format($format));
    }

    public function getFormatOutOfTimestampRangeData()
    {
        return array(
            array(DateTime::ATOM, '0000-01-01T00:00:00+00:00'),
            array(DateTime::COOKIE, 'Saturday, 01-Jan-00 00:00:00 GMT+0000'),
            array(DateTime::ISO8601, '0000-01-01T00:00:00+0000'),
            array(DateTime::RFC1036, 'Sat, 01 Jan 00 00:00:00 +0000'),
            array(DateTime::RFC1123, 'Sat, 01 Jan 0000 00:00:00 +0000'),
            array(DateTime::RFC2822, 'Sat, 01 Jan 0000 00:00:00 +0000'),
            array(DateTime::RFC2822, 'Sat, 01 Jan 0000 00:00:00 +0000'),
            array(DateTime::RFC3339, '0000-01-01T00:00:00+00:00'),
            array(DateTime::RFC822, 'Sat, 01 Jan 00 00:00:00 +0000'),
            array(DateTime::RFC850, 'Saturday, 01-Jan-00 00:00:00 GMT+0000'),
            array(DateTime::RSS, 'Sat, 01 Jan 0000 00:00:00 +0000'),
            array(DateTime::W3C, '0000-01-01T00:00:00+00:00'),
        );
    }

    /**
     * @dataProvider getFormatSpecialTimezoneData
     *
     * @param string $format
     * @param string $expected
     */
    public function testFormatSpecialTimezone($format, $expected = null)
    {
        date_default_timezone_set('Europe/London');

        $dateTime = new DateTime();
        $dateTime->setTimestamp(0);
        $dateTime->setTimezone(new DateTimeZone('Australia/Eucla'));

        $this->assertEquals($expected, $dateTime->format($format));
        $this->assertEquals($format, $dateTime->format('\\'.$format));
        $this->assertEquals('\\'.$expected, $dateTime->format('\\\\'.$format));
        $this->assertEquals('\\'.$format, $dateTime->format('\\\\\\'.$format));
    }

    public function getFormatSpecialTimezoneData()
    {
        return array(
            array('A', 'AM'),
            array('l', 'Thursday'),
            array('z', '0'),
            array('j', '1'),
            array('g', '8'),
            array('G', '8'),
            array('i', '45'),
            array('n', '1'),
            array('m', '01'),
            array('M', 'Jan'),
            array('F', 'January'),
            array('n', '1'),
            array('s', '00'),
            array('U', '0'),
            array('O', '+0845'),
            array('T', 'CWST'),
            array('P', '+08:45'),
            array('y', '70'),
            array('Y', '1970'),
            array('e', 'Australia/Eucla'),
        );
    }

    /**
     * @dataProvider getCreateFromFormatData
     *
     * @param mixed             $expected
     * @param string            $format
     * @param string            $value
     * @param DateTimeZone|NULL $timezone
     */
    public function testCreateFromFormat($expected, $format, $value, DateTimeZone $timezone)
    {
        date_default_timezone_set("Europe/Paris");

        if (null === $value) {
            $value = gmdate($format);
        }

        $date = DateTime::createFromFormat($format, $value, $timezone);
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        if (null === $expected) {
            $expected = time();
        }

        $this->assertEquals($expected, $date->getTimestamp(), '', 2);
    }

    public function getCreateFromFormatData()
    {
        return array(
            array(null, 'd', null, new DateTimeZone('GMT')),
            array(null, 'j', null, new DateTimeZone('GMT')),
            array(null, 'm', null, new DateTimeZone('GMT')),
            array(null, 'y', null, new DateTimeZone('GMT')),
            array(null, 'D', null, new DateTimeZone('GMT')),
            array(null, 'l', null, new DateTimeZone('GMT')),
            array(null, 'S', null, new DateTimeZone('GMT')),
            array(null, 'z', null, new DateTimeZone('GMT')),
            array(null, 'M', null, new DateTimeZone('GMT')),
            array(null, 'F', null, new DateTimeZone('GMT')),
            array(null, 'Y', null, new DateTimeZone('GMT')),
            array(33*24*3600, 'z|', 33, new DateTimeZone('GMT')),
            array('1293839999', 'U', '1293839999', new DateTimeZone('GMT')),
            array(12345, 'U', 12345, new DateTimeZone('GMT')),
            array(null, 'U', null, new DateTimeZone('GMT')),
            array(null, '#', ';', new DateTimeZone('GMT')),
            array(1596182400, 'D, d-m-y H', 'Fri, 31-07-20 08', new DateTimeZone('GMT')),
            array(1596182400, 'D, d-m-y H*', 'Fri, 31-07-20 085', new DateTimeZone('GMT')),
            array(1596182400, 'D, d-m-y g', 'Fri, 31-07-20 08', new DateTimeZone('GMT')),
            array(1596225600, 'D, d-m-y g a', 'Fri, 31-07-20 08 pm', new DateTimeZone('GMT')),
            array(1596182400, 'D, d-m-y g a', 'Fri, 31-07-20 08 am', new DateTimeZone('GMT')),
            array(1596185340, 'D, d-m-y H:i', 'Fri, 31-07-20 08:49', new DateTimeZone('GMT')),
            array(1596185377, 'D, d-m-y H:i:s', 'Fri, 31-07-20 08:49:37', new DateTimeZone('GMT')),
            array(1596185377, 'D, d-m-y H:i:s,u', 'Fri, 31-07-20 08:49:37,001020', new DateTimeZone('GMT')),
            array(1596185377, 'D, d-m-y H:i:s,u', 'Fri, 31-07-20 08:49:37,3', new DateTimeZone('GMT')),
            array(1596185377, 'D, d-m-y H:i:s T', 'Fri, 31-07-20 08:49:37 GMT', new DateTimeZone('GMT')),
            array(1596167377, 'D, d-m-y H:i:s T', 'Fri, 31-07-20 08:49:37 GMT+0500', new DateTimeZone('GMT')),
            array(1596167377, 'D, d-m-y H:i:s TO', 'Fri, 31-07-20 08:49:37 GMT+0500+05:00', new DateTimeZone('GMT')),
        );
    }

    /**
     * @dataProvider getCreateFromFormatPassingUnexpectedValuesForArgument1Data
     *
     * @param mixed   $format
     * @param Boolean $warning Must trigger a warning
     */
    public function testCreateFromFormatPassingUnexpectedValuesForArgument1($format, $warning = false)
    {
        date_default_timezone_set("Europe/London");

        $time = 'Thu, 14 Jul 2005 22:30:41 +0100';

        try {
            $this->assertFalse(DateTime::createFromFormat($format, $time));

            if ($warning) {
                $this->fail('DateTime::createFromFormat() trigger a warning with unexpected value at argument 1 $format');
            }
        } catch (PHPUnit_Framework_Error $e) {
            if (false === $warning) {
                throw $e;
            }

            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals(sprintf('DateTime::createFromFormat() expects parameter 1 to be string, %s given', gettype($format)), $e->getMessage());
        }

        $this->assertFalse(@DateTime::createFromFormat($format, $time));

        if (is_resource($format)) {
            @fclose($format);
        }
    }

    public function getCreateFromFormatPassingUnexpectedValuesForArgument1Data()
    {
        return array(
            array(0),
            array(1),
            array(12345),
            array(-12345),
            array(10.5),
            array(-10.5),
            array(.5),
            array(array(), true),
            array(array(1, 2, 3), true),
            array(array('one' => 1, 'two' => 2), true),
            array(null),
            array(true),
            array(false),
            array(''),
            array('string'),
            array('sTrInG'),
            array('hello world'),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString()),
            array(new stdClass(), true),
            array(fopen(__FILE__, 'r'), true),
            array('0'),
            array('1'),
            array('12345'),
            array('-12345'),
            array('10.5'),
            array('-10.5'),
            array('.5'),
        );
    }

    /**
     * @dataProvider getCreateFromFormatPassingUnexpectedValuesForArgument2Data
     *
     * @param mixed   $time
     * @param Boolean $warning Must trigger a warning
     */
    public function testCreateFromFormatPassingUnexpectedValuesForArgument2($time, $warning = false)
    {
        date_default_timezone_set("Europe/London");

        $format = DateTime::RFC2822;

        try {
            $this->assertFalse(DateTime::createFromFormat($format, $time));

            if ($warning) {
                $this->fail('DateTime::createFromFormat() trigger a warning with unexpected value at argument 2 $time');
            }
        } catch (PHPUnit_Framework_Error $e) {
            if (false === $warning) {
                throw $e;
            }

            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals(sprintf('DateTime::createFromFormat() expects parameter 2 to be string, %s given', gettype($time)), $e->getMessage());
        }

        $this->assertFalse(@DateTime::createFromFormat($format, $time));

        if (is_resource($time)) {
            @fclose($time);
        }
    }

    public function getCreateFromFormatPassingUnexpectedValuesForArgument2Data()
    {
        return array(
            array(0),
            array(1),
            array(12345),
            array(-12345),
            array(10.5),
            array(-10.5),
            array(.5),
            array(array(), true),
            array(array(1, 2, 3), true),
            array(array('one' => 1, 'two' => 2), true),
            array(null),
            array(true),
            array(false),
            array(''),
            array('string'),
            array('sTrInG'),
            array('hello world'),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString()),
            array(new stdClass(), true),
            array(fopen(__FILE__, 'r'), true),
            array('0'),
            array('1'),
            array('12345'),
            array('-12345'),
            array('10.5'),
            array('-10.5'),
            array('.5'),
        );
    }

    public function testBug0()
    {
        date_default_timezone_set("Europe/London");

        $date = DateTime::createFromFormat('D, d M Y H:i:s T', 'Fri, 31 Dec 2010 23:59:59 GMT', new DateTimeZone('GMT'));

        $timestamp = $date->getTimestamp();

        $formated = DateTime::createFromFormat('U', $timestamp, new DateTimeZone('GMT'))->format('D, d M Y H:i:s T');

        $this->assertSame('Fri, 31 Dec 2010 23:59:59 GMT+0000', $formated);
    }

    public function testBug1()
    {
        date_default_timezone_set("Europe/London");

        $date = DateTime::createFromFormat('D, d M Y H:i:s T', 'Fri, 31 Dec 2010 23:59:59 GMT', new DateTimeZone('GMT'));

        $timestamp = $date->getTimestamp();

        $formated = DateTime::createFromFormat('U', $timestamp, new DateTimeZone('GMT'))->format('D, d M Y H:i:s');

        $this->assertSame('Fri, 31 Dec 2010 23:59:59', $formated);
    }

    public function testBug2()
    {
        date_default_timezone_set("Europe/London");

        $date = DateTime::createFromFormat('D, d M Y H:i:s', 'Fri, 31 Dec 2010 23:59:59', new DateTimeZone('Europe/Paris'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293836399, $timestamp);

        $formated = DateTime::createFromFormat('U', $timestamp, new DateTimeZone('Europe/Paris'))->format('D, d M Y H:i:s T');

        $this->assertSame('Fri, 31 Dec 2010 22:59:59 GMT+0000', $formated);
    }

    public function testBug3()
    {
        date_default_timezone_set("Europe/London");

        $date = DateTime::createFromFormat('D, d M Y H:i:s', 'Fri, 31 Dec 2010 23:59:59', new DateTimeZone('Europe/Paris'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293836399, $timestamp);

        $formated = $date->format('D, d M Y H:i:s T');

        $this->assertSame('Fri, 31 Dec 2010 23:59:59 CET', $formated);
    }

    /**
     * @dataProvider getCreateFromFormatWithStandardFormatsData
     *
     * @param string $format
     * @param string $time
     */
    public function testCreateFromFormatWithStandardFormats($format, $time)
    {
        date_default_timezone_set("Europe/London");

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293839999, $timestamp);
    }

    public function getCreateFromFormatWithStandardFormatsData()
    {
        return array(
            array(DateTime::ATOM, '2010-12-31T23:59:59+00:00'),
            array(DateTime::COOKIE, 'Friday, 31-Dec-10 23:59:59 GMT'),
            array(DateTime::ISO8601, '2010-12-31T23:59:59+0000'),
            array(DateTime::RFC1036, 'Fri, 31 Dec 10 23:59:59 +0000'),
            array(DateTime::RFC1123, 'Fri, 31 Dec 2010 23:59:59 +0000'),
            array(DateTime::RFC2822, 'Fri, 31 Dec 2010 23:59:59 +0000'),
            array(DateTime::RFC2822, 'Fri, 31 Dec 2010 23:59:59 GMT'),
            array(DateTime::RFC3339, '2010-12-31T23:59:59+00:00'),
            array(DateTime::RFC822, 'Fri, 31 Dec 10 23:59:59 +0000'),
            array(DateTime::RFC850, 'Friday, 31-Dec-10 23:59:59 GMT'),
            array(DateTime::RSS, 'Fri, 31 Dec 2010 23:59:59 +0000'),
            array(DateTime::W3C, '2010-12-31T23:59:59+00:00'),
        );
    }

    public function testCreateFromFormatRandomChar()
    {
        date_default_timezone_set("Europe/London");

        $format = DateTime::W3C.'???';
        $time = '2010-12-31T23:59:59+00:00foo';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293839999, $timestamp);
    }

    public function testCreateFromFormatSeparationSymbolHashTag()
    {
        date_default_timezone_set("Europe/London");

        $format = DateTime::W3C.'######';
        $time = '2010-12-31T23:59:59+00:00;:/.,-';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293839999, $timestamp);
    }

    public function testCreateFromFormatAllowExtraCharsInTheFormat()
    {
        date_default_timezone_set("Europe/London");

        $format = DateTime::W3C.'+';
        $time = '2010-12-31T23:59:59+00:00foobar';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293839999, $timestamp);
    }

    public function testCreateFromFormatResetAllFieldsToDefault()
    {
        date_default_timezone_set("Europe/London");

        $format = DateTime::W3C.'!';
        $time = '2010-12-31T23:59:59+00:00';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(0, $timestamp);
    }

    public function testCreateFromFormatResetAllFieldsToDefaultWhenNotSet()
    {
        date_default_timezone_set("Europe/London");

        $format = 'Y-m-d\TH|';
        $time = '2010-12-31T23';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293832800, $timestamp);
    }

    public function testCreateFromFormatWithTimezoneAbbreviation()
    {
        date_default_timezone_set("Europe/London");

        $format = DateTime::COOKIE;
        $time = 'Friday, 31-Dec-10 23:59:59 bdst';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293832799, $timestamp);
    }

    public function testCreateFromFormatWithTimezoneIdentifier()
    {
        date_default_timezone_set("Europe/London");

        $format = 'l, d-M-y H:i:s e';
        $time = 'Friday, 31-Dec-10 23:59:59 Australia/Darwin';

        $date = DateTime::createFromFormat($format, $time, new DateTimeZone('Europe/Paris'));
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $timestamp = $date->getTimestamp();

        $this->assertEquals(1293805799, $timestamp);
    }

    public function testConstructWithTimezoneUTC()
    {
        date_default_timezone_set("Australia/Darwin");

        $date = new DateTime(null, new DateTimeZone('UTC'));
        $timestamp = $date->getTimestamp();

        $this->assertEquals(time(), $timestamp, '', 5);
    }

    public function testCreateFromFormatDateZero()
    {
        date_default_timezone_set("Australia/Darwin");

        $date = DateTime::createFromFormat(DateTime::RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
        $this->assertThat($date, $this->isInstanceOf('DateTime'));

        $this->assertEquals('Sat, 01 Jan 0000 00:00:00 GMT', $date->format('D, d M Y H:i:s').' GMT');
    }

    /**
     * @dataProvider getSetTimestampCorrectDateModificationData
     *
     * @param mixed $expected
     * @param mixed $newTimestamp
     */
    public function testSetTimestampCorrectDateModification($expected, $newTimestamp)
    {
        $date = new DateTime();

        $date->setTimestamp($newTimestamp);
        $this->assertSame($expected, $date->getTimestamp());
    }

    public function getSetTimestampCorrectDateModificationData()
    {
        return array(
            array(0, 0),
            array(0, '0'),
            array(-33, -33),
            array(-9, PHP_INT_MAX.'1'), // Correct behavior for 32 bits system
        );
    }

    /**
     * @dataProvider getSetTimestampReturnValueData
     *
     * @param mixed $newTimestamp
     * @param PHPUnit_Framework_Constraint $expected
     */
    public function testSetTimestampReturnValue($newTimestamp, $expected)
    {
        $date = new DateTime();

        $this->assertThat($date->setTimestamp($newTimestamp), $expected);
    }

    public function getSetTimestampReturnValueData()
    {
        return array(
            array(0, $this->isInstanceOf('DateTime')),
            array('0', $this->isInstanceOf('DateTime')),
            array(PHP_INT_MAX.'1', $this->isInstanceOf('DateTime')),
        );
    }

    public function testSetDateBasic1()
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-30 19:34:10");

        $this->assertEquals('Fri, 30 Jan 2009 19:34:10 +0000', $datetime->format(DateTime::RFC2822));
        $datetime->setDate(2008, 02, 01);
        $this->assertEquals('Fri, 01 Feb 2008 19:34:10 +0000', $datetime->format(DateTime::RFC2822));
    }

    public function testSetDateErrorWithZeroArguments()
    {
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-30 19:34:10");

        try {
            $this->assertFalse($datetime->setDate());
            $this->fail('DateTime::setDate() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setDate() expects exactly 3 parameters, 0 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setDate());
    }

    public function testSetDateErrorWithLessThanExpectedNoOfArguments()
    {
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-30 19:34:10");

        $year = 2009;
        $month = 1;
        $day = 30;

        try {
            $this->assertFalse($datetime->setDate($year));
            $this->fail('DateTime::setDate() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setDate() expects exactly 3 parameters, 1 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setDate($year));

        try {
            $this->assertFalse($datetime->setDate($year, $month));
            $this->fail('DateTime::setDate() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setDate() expects exactly 3 parameters, 2 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setDate($year, $month));
    }

    public function testSetDateErrorWithMoreThanExpectedNoOfArguments()
    {
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-30 19:34:10");

        $year = 2009;
        $month = 1;
        $day = 30;
        $extra_arg = 10;

        try {
            $this->assertFalse($datetime->setDate($year, $month, $day, $extra_arg));
            $this->fail('DateTime::setDate() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setDate() expects exactly 3 parameters, 4 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setDate($year, $month, $day, $extra_arg));
    }

    /**
     * @dataProvider getSetDateVariation1Data
     */
    public function testSetDateVariation1($year, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $object = new DateTime("2009-02-27 08:34:10");
        $day = 2;
        $month = 7;

        if (is_array($expected)) {
            $res = $object->setDate($year, $month, $day);

            $this->assertSame('Europe/London', $res->getTimezone()->getName());
            $this->assertSame($expected['date'], $res->format('Y-m-d H:i:s'));
            $this->assertSame($expected['ts'], $res->getTimestamp());
        } elseif (false === $expected) {
            try {
                $this->assertFalse($object->setDate($year, $month, $day));
                $this->fail('DateTime::setDate() trigger a warning with unexpected values to first argument $years');
            } catch (PHPUnit_Framework_Error $e) {
                $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
                $this->assertEquals(sprintf('DateTime::setDate() expects parameter 1 to be long, %s given', gettype($year)), $e->getMessage());
            }

            $this->assertFalse(@$object->setDate($year, $month, $day));
        }

        if (is_resource($year)) {
            fclose($year);
        }
    }

    public function getSetDateVariation1Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, array('ts' => false, 'date' => '0000-07-02 08:34:10')),
            array(1, array('ts' => false, 'date' => '0001-07-02 08:34:10')),
            array(12345, array('ts' => false, 'date' => '12345-07-02 08:34:10')),
            array(-12345, array('ts' => false, 'date' => '-12345-07-02 08:34:10')),
            array(10.5, array('ts' => false, 'date' => '0010-07-02 08:34:10')),
            array(-10.5, array('ts' => false, 'date' => '-0010-07-02 08:34:10')),
            array(.5, array('ts' => false, 'date' => '0000-07-02 08:34:10')),
            array(array(), false),
            array($index_array, false),
            array($assoc_array, false),
            array(array('foo', $index_array, $assoc_array), false),
            array(null, array('ts' => false, 'date' => '0000-07-02 08:34:10')),
            array(true, array('ts' => false, 'date' => '0001-07-02 08:34:10')),
            array(false, array('ts' => false, 'date' => '0000-07-02 08:34:10')),
            array('', false),
            array('string', false),
            array('sTrInG', false),
            array('hello world', false),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), false),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', array('ts' => false, 'date' => '0000-07-02 08:34:10')),
            array('1', array('ts' => false, 'date' => '0001-07-02 08:34:10')),
            array('12345', array('ts' => false, 'date' => '12345-07-02 08:34:10')),
            array('-12345', array('ts' => false, 'date' => '-12345-07-02 08:34:10')),
            array('10.5', array('ts' => false, 'date' => '0010-07-02 08:34:10')),
            array('-10.5', array('ts' => false, 'date' => '-0010-07-02 08:34:10')),
            array('.5', array('ts' => false, 'date' => '0000-07-02 08:34:10')),
        );
    }

    /**
     * @dataProvider getSetDateVariation2Data
     */
    public function testSetDateVariation2($month, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $object = new DateTime("2009-02-27 08:34:10");
        $day = 2;
        $year = 1963;

        if (is_array($expected)) {
            $res = $object->setDate($year, $month, $day);

            $this->assertSame('Europe/London', $res->getTimezone()->getName());
            $this->assertSame($expected['date'], $res->format('Y-m-d H:i:s'));
            $this->assertSame($expected['ts'], $res->getTimestamp());
        } elseif (false === $expected) {
            try {
                $this->assertFalse($object->setDate($year, $month, $day));
                $this->fail('DateTime::setDate() trigger a warning with unexpected values to second argument $month');
            } catch (PHPUnit_Framework_Error $e) {
                $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
                $this->assertEquals(sprintf('DateTime::setDate() expects parameter 2 to be long, %s given', gettype($month)), $e->getMessage());
            }

            $this->assertFalse(@$object->setDate($year, $month, $day));
        }

        if (is_resource($month)) {
            fclose($month);
        }
    }

    public function getSetDateVariation2Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, array('ts' => -223485950, 'date' => '1962-12-02 08:34:10')),
            array(1, array('ts' => -220807550, 'date' => '1963-01-02 08:34:10')),
            array(12345, array('ts' => false, 'date' => '2991-09-02 08:34:10')),
            array(-12345, array('ts' => false, 'date' => '0934-03-02 08:34:10')),
            array(10.5, array('ts' => -197223950, 'date' => '1963-10-02 08:34:10')),
            array(-10.5, array('ts' => -249665150, 'date' => '1962-02-02 08:34:10')),
            array(.5, array('ts' => -223485950, 'date' => '1962-12-02 08:34:10')),
            array(array(), false),
            array($index_array, false),
            array($assoc_array, false),
            array(array('foo', $index_array, $assoc_array), false),
            array(null, array('ts' => -223485950, 'date' => '1962-12-02 08:34:10')),
            array(true, array('ts' => -220807550, 'date' => '1963-01-02 08:34:10')),
            array(false, array('ts' => -223485950, 'date' => '1962-12-02 08:34:10')),
            array('', false),
            array('string', false),
            array('sTrInG', false),
            array('hello world', false),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), false),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', array('ts' => -223485950, 'date' => '1962-12-02 08:34:10')),
            array('1', array('ts' => -220807550, 'date' => '1963-01-02 08:34:10')),
            array('12345', array('ts' => false, 'date' => '2991-09-02 08:34:10')),
            array('-12345', array('ts' => false, 'date' => '0934-03-02 08:34:10')),
            array('10.5', array('ts' => -197223950, 'date' => '1963-10-02 08:34:10')),
            array('-10.5', array('ts' => -249665150, 'date' => '1962-02-02 08:34:10')),
            array('.5', array('ts' => -223485950, 'date' => '1962-12-02 08:34:10')),
        );
    }

    /**
     * @dataProvider getSetDateVariation3Data
     */
    public function testSetDateVariation3($day, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $object = new DateTime("2009-02-27 08:34:10");
        $month = 7;
        $year = 1963;

        if (is_array($expected)) {
            $res = $object->setDate($year, $month, $day);

            $this->assertSame('Europe/London', $res->getTimezone()->getName());
            $this->assertSame($expected['date'], $res->format('Y-m-d H:i:s'));
            $this->assertSame($expected['ts'], $res->getTimestamp());
        } elseif (false === $expected) {
            try {
                $this->assertFalse($object->setDate($year, $month, $day));
                $this->fail('DateTime::setDate() trigger a warning with unexpected values to third argument $day');
            } catch (PHPUnit_Framework_Error $e) {
                $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
                $this->assertEquals(sprintf('DateTime::setDate() expects parameter 3 to be long, %s given', gettype($day)), $e->getMessage());
            }

            $this->assertFalse(@$object->setDate($year, $month, $day));
        }

        if (is_resource($day)) {
            fclose($day);
        }
    }

    public function getSetDateVariation3Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, array('ts' => -205345550, 'date' => '1963-06-30 08:34:10')),
            array(1, array('ts' => -205259150, 'date' => '1963-07-01 08:34:10')),
            array(12345, array('ts' => 861262450, 'date' => '1997-04-17 08:34:10')),
            array(-12345, array('ts' => -1271953550, 'date' => '1929-09-11 08:34:10')),
            array(10.5, array('ts' => -204481550, 'date' => '1963-07-10 08:34:10')),
            array(-10.5, array('ts' => -206209550, 'date' => '1963-06-20 08:34:10')),
            array(.5, array('ts' => -205345550, 'date' => '1963-06-30 08:34:10')),
            array(array(), false),
            array($index_array, false),
            array($assoc_array, false),
            array(array('foo', $index_array, $assoc_array), false),
            array(null, array('ts' => -205345550, 'date' => '1963-06-30 08:34:10')),
            array(true, array('ts' => -205259150, 'date' => '1963-07-01 08:34:10')),
            array(false, array('ts' => -205345550, 'date' => '1963-06-30 08:34:10')),
            array('', false),
            array('string', false),
            array('sTrInG', false),
            array('hello world', false),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), false),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', array('ts' => -205345550, 'date' => '1963-06-30 08:34:10')),
            array('1', array('ts' => -205259150, 'date' => '1963-07-01 08:34:10')),
            array('12345', array('ts' => 861262450, 'date' => '1997-04-17 08:34:10')),
            array('-12345', array('ts' => -1271953550, 'date' => '1929-09-11 08:34:10')),
            array('10.5', array('ts' => -204481550, 'date' => '1963-07-10 08:34:10')),
            array('-10.5', array('ts' => -206209550, 'date' => '1963-06-20 08:34:10')),
            array('.5', array('ts' => -205345550, 'date' => '1963-06-30 08:34:10')),
        );
    }

    /**
     * @dataProvider getSetTimeBasic1Data
     *
     * @param integer $hour
     * @param integer $minute
     * @param integer $second
     * @param string $expected
     */
    public function testSetTimeBasic1($hour, $minute, $second, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        // Create a DateTime object
        $datetime = new DateTime("2009-01-31 15:14:10");

        $this->assertEquals('Sat, 31 Jan 2009 15:14:10 +0000', $datetime ->format(DateTime::RFC2822));

        if (null === $second) {
            $datetime->setTime($hour, $minute);
        } else {
            $datetime->setTime($hour, $minute, $second);
        }

        $this->assertEquals($expected, $datetime ->format(DateTime::RFC2822));
    }

    public function getSetTimeBasic1Data()
    {
        return array(
            array(17, 20, null, 'Sat, 31 Jan 2009 17:20:00 +0000'),
            array(19, 05, 59, 'Sat, 31 Jan 2009 19:05:59 +0000'),
            array(24, 10, null, 'Sun, 01 Feb 2009 00:10:00 +0000'),
            array(47, 35, 47, 'Sun, 01 Feb 2009 23:35:47 +0000'),
            array(54, 25, null, 'Mon, 02 Feb 2009 06:25:00 +0000'),
        );
    }

    public function testSetTimeErrorWithZeroArguments()
    {
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-31 15:34:10");

        try {
            $this->assertFalse($datetime->setTime());
            $this->fail('DateTime::setTime() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setTime() expects at least 2 parameters, 0 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setTime());
    }

    public function testSetTimeErrorWithLessThanExpectedNoOfArguments()
    {
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-31 15:34:10");

        $hour = 18;

        try {
            $this->assertFalse($datetime->setTime($hour));
            $this->fail('DateTime::setTime() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setTime() expects at least 2 parameters, 1 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setTime($hour));
    }

    public function testSetTimeErrorWithMoreThanExpectedNoOfArguments()
    {
        date_default_timezone_set("Europe/London");

        $datetime = new DateTime("2009-01-31 15:34:10");

        $hour = 18;
        $min = 15;
        $sec = 30;
        $extra_arg = 10;

        try {
            $this->assertFalse($datetime->setTime($hour, $min, $sec, $extra_arg));
            $this->fail('DateTime::setTime() trigger a warning with zero arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTime::setTime() expects at most 3 parameters, 4 given', $e->getMessage());
        }
        $this->assertFalse(@$datetime->setTime($hour, $min, $sec, $extra_arg));
    }

    /**
     * @dataProvider getSetTimeVariation1Data
     */
    public function testSetTimeVariation1($hour, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $object = new DateTime("2009-01-31 15:14:10");
        $minute = 13;
        $sec = 45;

        if (is_array($expected)) {
            $res = $object->setTime($hour, $minute, $sec);

            $this->assertSame('Europe/London', $res->getTimezone()->getName());
            $this->assertSame($expected['date'], $res->format('Y-m-d H:i:s'));
            $this->assertSame($expected['ts'], $res->getTimestamp());
        } elseif (false === $expected) {
            try {
                $this->assertFalse($object->setTime($hour, $minute, $sec));
                $this->fail('DateTime::setTime() trigger a warning with unexpected values to first argument $hour');
            } catch (PHPUnit_Framework_Error $e) {
                $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
                $this->assertEquals(sprintf('DateTime::setTime() expects parameter 1 to be long, %s given', gettype($hour)), $e->getMessage());
            }

            $this->assertFalse(@$object->setTime($hour, $minute, $sec));
        }

        if (is_resource($hour)) {
            fclose($hour);
        }
    }

    public function getSetTimeVariation1Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, array('ts' => 1233360825, 'date' => '2009-01-31 00:13:45')),
            array(1, array('ts' => 1233364425, 'date' => '2009-01-31 01:13:45')),
            array(12345, array('ts' => 1277799225, 'date' => '2010-06-29 09:13:45')),
            array(-12345, array('ts' => 1188915225, 'date' => '2007-09-04 15:13:45')),
            array(10.5, array('ts' => 1233396825, 'date' => '2009-01-31 10:13:45')),
            array(-10.5, array('ts' => 1233324825, 'date' => '2009-01-30 14:13:45')),
            array(.5, array('ts' => 1233360825, 'date' => '2009-01-31 00:13:45')),
            array(array(), false),
            array($index_array, false),
            array($assoc_array, false),
            array(array('foo', $index_array, $assoc_array), false),
            array(null, array('ts' => 1233360825, 'date' => '2009-01-31 00:13:45')),
            array(true, array('ts' => 1233364425, 'date' => '2009-01-31 01:13:45')),
            array(false, array('ts' => 1233360825, 'date' => '2009-01-31 00:13:45')),
            array('', false),
            array('string', false),
            array('sTrInG', false),
            array('hello world', false),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), false),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', array('ts' => 1233360825, 'date' => '2009-01-31 00:13:45')),
            array('1', array('ts' => 1233364425, 'date' => '2009-01-31 01:13:45')),
            array('12345', array('ts' => 1277799225, 'date' => '2010-06-29 09:13:45')),
            array('-12345', array('ts' => 1188915225, 'date' => '2007-09-04 15:13:45')),
            array('10.5', array('ts' => 1233396825, 'date' => '2009-01-31 10:13:45')),
            array('-10.5', array('ts' => 1233324825, 'date' => '2009-01-30 14:13:45')),
            array('.5', array('ts' => 1233360825, 'date' => '2009-01-31 00:13:45')),
        );
    }

    /**
     * @dataProvider getSetTimeVariation2Data
     */
    public function testSetTimeVariation2($minute, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $object = new DateTime("2009-01-31 15:14:10");
        $hour = 10;
        $sec = 45;

        if (is_array($expected)) {
            $res = $object->setTime($hour, $minute, $sec);

            $this->assertSame('Europe/London', $res->getTimezone()->getName());
            $this->assertSame($expected['date'], $res->format('Y-m-d H:i:s'));
            $this->assertSame($expected['ts'], $res->getTimestamp());
        } elseif (false === $expected) {
            try {
                $this->assertFalse($object->setTime($hour, $minute, $sec));
                $this->fail('DateTime::setTime() trigger a warning with unexpected values to second argument $minute');
            } catch (PHPUnit_Framework_Error $e) {
                $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
                $this->assertEquals(sprintf('DateTime::setTime() expects parameter 2 to be long, %s given', gettype($minute)), $e->getMessage());
            }

            $this->assertFalse(@$object->setTime($hour, $minute, $sec));
        }

        if (is_resource($minute)) {
            fclose($minute);
        }
    }

    public function getSetTimeVariation2Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, array('ts' => 1233396045, 'date' => '2009-01-31 10:00:45')),
            array(1, array('ts' => 1233396105, 'date' => '2009-01-31 10:01:45')),
            array(12345, array('ts' => 1234136745, 'date' => '2009-02-08 23:45:45')),
            array(-12345, array('ts' => 1232655345, 'date' => '2009-01-22 20:15:45')),
            array(10.5, array('ts' => 1233396645, 'date' => '2009-01-31 10:10:45')),
            array(-10.5, array('ts' => 1233395445, 'date' => '2009-01-31 09:50:45')),
            array(.5, array('ts' => 1233396045, 'date' => '2009-01-31 10:00:45')),
            array(array(), false),
            array($index_array, false),
            array($assoc_array, false),
            array(array('foo', $index_array, $assoc_array), false),
            array(null, array('ts' => 1233396045, 'date' => '2009-01-31 10:00:45')),
            array(true, array('ts' => 1233396105, 'date' => '2009-01-31 10:01:45')),
            array(false, array('ts' => 1233396045, 'date' => '2009-01-31 10:00:45')),
            array('', false),
            array('string', false),
            array('sTrInG', false),
            array('hello world', false),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), false),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', array('ts' => 1233396045, 'date' => '2009-01-31 10:00:45')),
            array('1', array('ts' => 1233396105, 'date' => '2009-01-31 10:01:45')),
            array('12345', array('ts' => 1234136745, 'date' => '2009-02-08 23:45:45')),
            array('-12345', array('ts' => 1232655345, 'date' => '2009-01-22 20:15:45')),
            array('10.5', array('ts' => 1233396645, 'date' => '2009-01-31 10:10:45')),
            array('-10.5', array('ts' => 1233395445, 'date' => '2009-01-31 09:50:45')),
            array('.5', array('ts' => 1233396045, 'date' => '2009-01-31 10:00:45')),
        );
    }

    /**
     * @dataProvider getSetTimeVariation3Data
     */
    public function testSetTimeVariation3($sec, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        $object = new DateTime("2009-01-31 15:14:10");
        $hour = 10;
        $minute = 13;

        if (is_array($expected)) {
            $res = $object->setTime($hour, $minute, $sec);

            $this->assertSame('Europe/London', $res->getTimezone()->getName());
            $this->assertSame($expected['date'], $res->format('Y-m-d H:i:s'));
            $this->assertSame($expected['ts'], $res->getTimestamp());
        } elseif (false === $expected) {
            try {
                $this->assertFalse($object->setTime($hour, $minute, $sec));
                $this->fail('DateTime::setTime() trigger a warning with unexpected values to third argument $sec');
            } catch (PHPUnit_Framework_Error $e) {
                $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
                $this->assertEquals(sprintf('DateTime::setTime() expects parameter 3 to be long, %s given', gettype($sec)), $e->getMessage());
            }

            $this->assertFalse(@$object->setTime($hour, $minute, $sec));
        }

        if (is_resource($sec)) {
            fclose($sec);
        }
    }

    public function getSetTimeVariation3Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, array('ts' => 1233396780, 'date' => '2009-01-31 10:13:00')),
            array(1, array('ts' => 1233396781, 'date' => '2009-01-31 10:13:01')),
            array(12345, array('ts' => 1233409125, 'date' => '2009-01-31 13:38:45')),
            array(-12345, array('ts' => 1233384435, 'date' => '2009-01-31 06:47:15')),
            array(10.5, array('ts' => 1233396790, 'date' => '2009-01-31 10:13:10')),
            array(-10.5, array('ts' => 1233396770, 'date' => '2009-01-31 10:12:50')),
            array(.5, array('ts' => 1233396780, 'date' => '2009-01-31 10:13:00')),
            array(array(), false),
            array($index_array, false),
            array($assoc_array, false),
            array(array('foo', $index_array, $assoc_array), false),
            array(null, array('ts' => 1233396780, 'date' => '2009-01-31 10:13:00')),
            array(true, array('ts' => 1233396781, 'date' => '2009-01-31 10:13:01')),
            array(false, array('ts' => 1233396780, 'date' => '2009-01-31 10:13:00')),
            array('', false),
            array('string', false),
            array('sTrInG', false),
            array('hello world', false),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString(), false),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
            array('0', array('ts' => 1233396780, 'date' => '2009-01-31 10:13:00')),
            array('1', array('ts' => 1233396781, 'date' => '2009-01-31 10:13:01')),
            array('12345', array('ts' => 1233409125, 'date' => '2009-01-31 13:38:45')),
            array('-12345', array('ts' => 1233384435, 'date' => '2009-01-31 06:47:15')),
            array('10.5', array('ts' => 1233396790, 'date' => '2009-01-31 10:13:10')),
            array('-10.5', array('ts' => 1233396770, 'date' => '2009-01-31 10:12:50')),
            array('.5', array('ts' => 1233396780, 'date' => '2009-01-31 10:13:00')),
        );
    }

    /**
     * @dataProvider getSetTimezoneData
     *
     * @param string  $format
     * @param string  $timezone
     * @param integer $expected
     */
    public function testSetTimezone($format, $timezone, $expected)
    {
        date_default_timezone_set('Australia/ACT');

        $dateTime = new DateTime();
        $dateTime->setTimestamp(0);
        $dateTime->setTimezone(new DateTimeZone($timezone));
        $dateTime->setDate(1970, 1, 1);
        $dateTime->setTime(0, 0, 0);

        $this->assertEquals($expected, $dateTime->format($format));
    }

    public function getSetTimezoneData()
    {
        return array(
            array('U', 'Europe/London', -3600),
            array('U', 'UTC', 0),
            array('Y-m-d H:i:s', 'UTC', '1970-01-01 00:00:00'),
            array('Y-m-d H:i:s', 'Europe/London', '1970-01-01 00:00:00'),
            array('Y-m-d H:i:s', 'Australia/Eucla', '1970-01-01 00:00:00'),
            array('Y-m-d H:i:s', 'Australia/Melbourne', '1970-01-01 00:00:00'),
        );
    }
}

class Instinct_Component_PhpBackport_Tests_DateTimeTestClassWithToString
{
    public function __toString()
    {
        return "Class A object";
    }
}
