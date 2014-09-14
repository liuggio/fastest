<?php

namespace Liuggio\Fastest\Queue;


class CreateTestSuitesFromPhpUnitXMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnArrayOfTestSuitesFromXML()
    {
        $factory = new CreateTestSuitesFromPhpUnitXML();
        $output = $factory->execute(__DIR__.'/Fixture/phpunit.xml.dist');


        $this->assertEquals(array('tests/Fastest/folderA', 'tests/Fastest/folderB'), $output);
    }
}
 