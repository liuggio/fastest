<?php

namespace Liuggio\Fastest\Queue;


class CreateTestsQueueFromPhpUnitXMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnArrayOfTestSuitesFromXML()
    {
        $output = CreateTestsQueueFromPhpUnitXML::execute(__DIR__.'/Fixture/phpunit.xml.dist');

        $this->assertEquals(new TestsQueue(array('tests/Fastest/folderA', 'tests/Fastest/folderB')), $output);
    }
}
 