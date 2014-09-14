<?php

namespace Liuggio\Fastest\Queue;


class CreateTestSuitesFromPipeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnArrayOfTestSuitesFromSTDIN()
    {
        $this->markTestIncomplete();
        $factory = new CreateTestSuitesFromPipe('php://memory');
        $output = $factory->execute();
        $this->assertEquals(array(), $output);
    }
}
 