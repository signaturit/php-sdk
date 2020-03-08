<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Signaturit\Client;

final class ClientTest extends TestCase
{
    private $client;

    private $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new GuzzleHttp\Client(['handler' => $handlerStack]);

        $this->client = new Client('a_token', true);

        $reflection = new ReflectionClass($this->client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        $property->setValue($this->client, $client);
    }

    protected function tearDown(): void
    {
        $this->mockHandler->reset();
    }

    public function test_count_signatures_should_return_num()
    {
        $response = '{"count": 3}';
        $expected = json_decode($response, true);

        $this->mockHandler->append(
            new Response(200, [], $response)
        );

        $actual = $this->client->countSignatures();

        $this->assertEquals($expected, $actual);
    }
}
