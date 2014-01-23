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

        $this->assertEquals($expected, $d->getTimezone()->getName());
    }

    public function getConstructBasic1Data()
    {
        // TODO Improve timezone detection

        return array(
            array('Europe/London', ''),
            array('GMT', 'GMT'),
            array('Europe/London', '2005-07-14 22:30:41'),
            array('GMT', '2005-07-14 22:30:41 GMT'),
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
            array(new Tests_DateTimeTestClassWithToString(), $timezone),
            array(new stdClass(), $timezone),
            array(fopen(__FILE__, 'r'), $timezone),
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
     * @dataProvider getFormatPassingUnexpectedValuesData
     */
    public function testFormatPassingUnexpectedValues($format, $expected)
    {
        date_default_timezone_set("Europe/London");

        $d = new DateTime('2005-07-14 22:30:41');

        try {
            $this->assertSame($expected, $d->format($format));
        } catch (ErrorException $e) {
        } catch (PHPUnit_Framework_Error_Warning $e) {
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
            array(new Tests_DateTimeTestClassWithToString(), 'CThursdaypm4141 PM 2005b14Europe/London2005-07-14T22:30:41+01:0031'),
            array(new stdClass(), false),
            array(fopen(__FILE__, 'r'), false),
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

        $this->assertThat($date->format('D, d M Y H:i:s').' GMT', $this->logicalOr(
            'Sat, 01 Jan 0000 00:00:00 GMT',
            'Fri, 13 Dec 1901 20:45:54 GMT' // The valid range of a timestamp from
        ));
    }
}

class Tests_DateTimeTestClassWithToString
{
    public function __toString()
    {
        return "Class A object";
    }
}
