<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    /** @test */
    public function can_store_new_task()
    {
        $response = $this->runApp('POST', '/v1/tasks/store', [
            'task' => 'Write great unit tests'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Succesfully stored', json_decode((string) $response->getBody())->message);
    }
}