<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromSTDINTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnArrayOfTestSuitesFromSTDIN()
    {
        $this->markTestIncomplete();
        $factory = new CreateTestsQueueFromSTDIN('php://memory');
    }
}
 