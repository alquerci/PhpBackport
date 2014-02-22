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
class Instinct_Component_PhpBackport_Tests_DateTimeZoneTest extends PHPUnit_Framework_TestCase
{
    private $defaultTimezone;
    private $fileHandle;

    public function setUp()
    {
        $this->defaultTimezone = date_default_timezone_get();
    }

    public function tearDown()
    {
        date_default_timezone_set($this->defaultTimezone);
    }

    /**
     * @dataProvider getConstructBasicData
     *
     * @param mixed $arg1 Valid timezone
     */
    public function testConstructBasic($arg1)
    {
        new DateTimeZone($arg1);
    }

    public function getConstructBasicData()
    {
        return array(
            array('GMT'),
            array('Europe/London'),
            array('America/Los_Angeles'),
        );
    }

    public function testConstructError()
    {
        date_default_timezone_set("GMT");

        $timezone = "GMT";
        $extra_arg = 99;

        try {
            new DateTimeZone($timezone, $extra_arg);

            $this->fail('An exception of type "Exception" is thrown when DateTimeZone::__construct() was called with more than expected no. of arguments.');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            throw $e;
        } catch (Exception $e) {
            $this->assertEquals('DateTimeZone::__construct() expects exactly 1 parameter, 2 given', $e->getMessage());
        }
    }

    /**
     * @dataProvider getConstructVariation1Data
     */
    public function testConstructVariation1($timezone, $msg)
    {
        //Set the default time zone
        date_default_timezone_set("Europe/London");

        try {
            new DateTimeZone($timezone);

            $this->fail('Expected exception of type "Exception" is thrown when DateTimeZone::__construct() was called with unexpected values to first argument $timezone.');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            throw $e;
        } catch (Exception $e) {
            $this->assertEquals('Exception', get_class($e));
            $this->assertEquals($msg, $e->getMessage());
        }
    }

    public function getConstructVariation1Data()
    {
        // add arrays
        $index_array = array (1, 2, 3);
        $assoc_array = array ('one' => 1, 'two' => 2);

        return array(
            array(0, 'DateTimeZone::__construct(): Unknown or bad timezone (0)'),
            array(1, 'DateTimeZone::__construct(): Unknown or bad timezone (1)'),
            array(12345, 'DateTimeZone::__construct(): Unknown or bad timezone (12345)'),
            array(-12345, 'DateTimeZone::__construct(): Unknown or bad timezone (-12345)'),
            array(10.5, 'DateTimeZone::__construct(): Unknown or bad timezone (10.5)'),
            array(-10.5, 'DateTimeZone::__construct(): Unknown or bad timezone (-10.5)'),
            array(.5, 'DateTimeZone::__construct(): Unknown or bad timezone (0.5)'),
            array(array(), 'DateTimeZone::__construct() expects parameter 1 to be string, array given'),
            array($index_array, 'DateTimeZone::__construct() expects parameter 1 to be string, array given'),
            array($assoc_array, 'DateTimeZone::__construct() expects parameter 1 to be string, array given'),
            array(array('foo', $index_array, $assoc_array), 'DateTimeZone::__construct() expects parameter 1 to be string, array given'),
            array(null, 'DateTimeZone::__construct(): Unknown or bad timezone ()'),
            array(true, 'DateTimeZone::__construct(): Unknown or bad timezone (1)'),
            array(false, 'DateTimeZone::__construct(): Unknown or bad timezone ()'),
            array('', 'DateTimeZone::__construct(): Unknown or bad timezone ()'),
            array('string', 'DateTimeZone::__construct(): Unknown or bad timezone (string)'),
            array('sTrInG', 'DateTimeZone::__construct(): Unknown or bad timezone (sTrInG)'),
            array('hello world', 'DateTimeZone::__construct(): Unknown or bad timezone (hello world)'),
            array(new Instinct_Component_PhpBackport_Tests_DateTimeZoneTestClassWithToString(), 'DateTimeZone::__construct(): Unknown or bad timezone (Class A object)'),
            array(new stdClass(), 'DateTimeZone::__construct() expects parameter 1 to be string, object given'),
            array(fopen(__FILE__, 'r'), 'DateTimeZone::__construct() expects parameter 1 to be string, resource given'),
            array('0', 'DateTimeZone::__construct(): Unknown or bad timezone (0)'),
            array('1', 'DateTimeZone::__construct(): Unknown or bad timezone (1)'),
            array('12345', 'DateTimeZone::__construct(): Unknown or bad timezone (12345)'),
            array('-12345', 'DateTimeZone::__construct(): Unknown or bad timezone (-12345)'),
            array('10.5', 'DateTimeZone::__construct(): Unknown or bad timezone (10.5)'),
            array('-10.5', 'DateTimeZone::__construct(): Unknown or bad timezone (-10.5)'),
            array('.5', 'DateTimeZone::__construct(): Unknown or bad timezone (.5)'),
        );
    }

    public function testListAbbreviationsBasic1()
    {
        //Set the default time zone
        date_default_timezone_set("GMT");

        $abbr = DateTimeZone::listAbbreviations();

        $this->assertInternalType('array', $abbr);
        $this->assertEquals(array(
            array('dst' => true, 'offset' => -14400, 'timezone_id' => 'America/Porto_Acre'),
            array('dst' => true, 'offset' => -14400, 'timezone_id' => 'America/Eirunepe'),
            array('dst' => true, 'offset' => -14400, 'timezone_id' => 'America/Rio_Branco'),
            array('dst' => true, 'offset' => -14400, 'timezone_id' => 'Brazil/Acre'),
        ), $abbr['acst']);
    }

    public function testListIdentifiersReturnAnArray()
    {
        //Set the default time zone
        date_default_timezone_set("GMT");

        $zones = DateTimeZone::listIdentifiers();

        $this->assertInternalType('array', $zones);
    }

    /**
     * @dataProvider getListIdentifiersBasic1Data
     *
     * @param string $timezone Valid timezone
     */
    public function testListIdentifiersBasic1($timezone)
    {
        //Set the default time zone
        date_default_timezone_set("GMT");

        $zones = DateTimeZone::listIdentifiers();

        $this->assertContains($timezone, $zones);
    }

    public function getListIdentifiersBasic1Data()
    {
        return array(
            array('Europe/London'),
            array('America/New_York'),
            array('UTC'),
        );
    }

    /**
     * @dataProvider getGetNameBasic1Data
     *
     * @param unknown_type $timezone
     * @param unknown_type $expected
     */
    public function testGetNameBasic1($timezone, $expected)
    {
        //Set the default time zone
        date_default_timezone_set("GMT");

        $object = new DateTimeZone($timezone);

        $this->assertEquals($expected, $object->getName());
    }

    public function getGetNameBasic1Data()
    {
        return array(
            array('Europe/London', 'Europe/London'),
            array('America/New_York', 'America/New_York'),
            array('America/Los_Angeles', 'America/Los_Angeles'),
        );
    }

    public function testGetNameWithMoreThanExpectedNoOfArguments()
    {
        //Set the default time zone
        date_default_timezone_set("GMT");

        $tz = new DateTimeZone("Europe/London");

        $extra_arg = 99;

        try {
            $this->assertFalse($tz->getName($extra_arg));
            $this->fail('DateTimeZone::getName() trigger a warning with more than expected no. of arguments');
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertThat($e->getCode(), $this->logicalOr(E_WARNING, E_USER_WARNING));
            $this->assertEquals('DateTimeZone::getName() expects exactly 0 parameters, 1 given', $e->getMessage());
        }
        $this->assertFalse(@$tz->getName($extra_arg));
    }
}

class Instinct_Component_PhpBackport_Tests_DateTimeZoneTestClassWithToString
{
    public function __toString()
    {
        return "Class A object";
    }
}
