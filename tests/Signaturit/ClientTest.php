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

    public function test_count_signatures_should_return_count()
    {
        $response = '{"count": 3}';
        $expected = json_decode($response, true);

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->countSignatures();

        $this->assertEquals($expected, $actual);
        $this->assertRequestMethodWas('GET');
        $this->assertRequestPathWas('/v3/signatures/count.json');
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamsWereEmpty();
    }

    public function test_get_signature_should_return_signature()
    {
        $signatureId = 'an_id';
        $response = "{\"id\": \"$signatureId\"}";
        $expected = json_decode($response, true);

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->getSignature($signatureId);

        $this->assertEquals($expected, $actual);
        $this->assertRequestMethodWas('GET');
        $this->assertRequestPathWas("/v3/signatures/$signatureId.json");
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamsWereEmpty();
    }

    public function test_download_signature_audit_trail_should_return_content()
    {
        $signatureId = 'an_id';
        $documentId = 'another_id';
        $response = 'binary_content';

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->downloadAuditTrail($signatureId, $documentId);

        $this->assertEquals($response, $actual);
        $this->assertRequestMethodWas('GET');
        $this->assertRequestPathWas("/v3/signatures/$signatureId/documents/$documentId/download/audit_trail");
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamsWereEmpty();
    }

    public function test_get_signatures_should_return_signatures()
    {
        $signatureId = 'an_id';
        $response = "[{\"id\": \"$signatureId\"}]";
        $expected = json_decode($response, true);

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->getSignatures(1, 5);

        $this->assertEquals($expected, $actual);
        $this->assertRequestMethodWas('GET');
        $this->assertRequestPathWas('/v3/signatures.json');
        $this->assertRequestQueryParamsContains('limit', 1);
        $this->assertRequestQueryParamsContains('offset', 5);
        $this->assertRequestBodyParamsWereEmpty();
    }

    public function test_download_signature_signed_should_return_content()
    {
        $signatureId = 'an_id';
        $documentId = 'another_id';
        $response = 'binary_content';

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->downloadSignedDocument($signatureId, $documentId);

        $this->assertEquals($response, $actual);
        $this->assertRequestMethodWas('GET');
        $this->assertRequestPathWas("/v3/signatures/$signatureId/documents/$documentId/download/signed");
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamsWereEmpty();
    }

    public function test_create_signature_should_create_signature()
    {
        $signatureId = 'an_id';
        $response = "[{\"id\": \"$signatureId\"}]";
        $expected = json_decode($response, true);

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->createSignature('composer.json', ['email' => 'an_email']);

        $this->assertEquals($expected, $actual);
        $this->assertRequestMethodWas('POST');
        $this->assertRequestPathWas('/v3/signatures.json');
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamWas('recipients[0][email]', 'an_email');
    }

    public function test_cancel_signature_should_return_empty_response()
    {
        $signatureId = 'an_id';
        $response = '';

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->cancelSignature($signatureId);

        $this->assertEquals($response, $actual);
        $this->assertRequestMethodWas('PATCH');
        $this->assertRequestPathWas("/v3/signatures/$signatureId/cancel.json");
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamsWereEmpty();
    }

    public function test_send_signature_reminder_should_return_empty_response()
    {
        $signatureId = 'an_id';
        $response = '';

        $this->prepareRequestResponse(200, $response);

        $actual = $this->client->sendSignatureReminder($signatureId);

        $this->assertEquals($response, $actual);
        $this->assertRequestMethodWas('POST');
        $this->assertRequestPathWas("/v3/signatures/$signatureId/reminder.json");
        $this->assertRequestQueryParamsWereEmpty();
        $this->assertRequestBodyParamsWereEmpty();
    }

    private function prepareRequestResponse($statusCode, $body)
    {
        $this->mockHandler->append(
            new Response($statusCode, [], $body)
        );
    }

    private function assertRequestMethodWas($method)
    {
        $actual = $this->mockHandler->getLastRequest()->getMethod();

        $this->assertEquals($method, $actual);
    }

    private function assertRequestPathWas($path)
    {
        $actual = $this->mockHandler->getLastRequest()->getUri()->getPath();

        $this->assertEquals($path, $actual);
    }

    private function assertRequestQueryParamsWereEmpty()
    {
        $actual = $this->mockHandler->getLastRequest()->getUri()->getQuery();

        $this->assertEmpty($actual);
    }

    private function assertRequestQueryParamsContains($param, $value)
    {
        $actual = $this->mockHandler->getLastRequest()->getUri()->getQuery();

        parse_str($actual, $actual);

        $this->assertArrayHasKey($param, $actual);
        $this->assertEquals($value, $actual[$param]);
    }

    private function assertRequestBodyParamsWereEmpty()
    {
        $actual = $this->mockHandler->getLastRequest()->getBody()->getContents();

        $this->assertEmpty($actual);
    }

    private function assertRequestBodyParamWas($param, $value)
    {
        $actual = $this->mockHandler->getLastRequest()->getBody()->getContents();

        $this->assertStringContainsString("name=\"$param\"", $actual);
        $this->assertStringContainsString("\r\n$value\r\n", $actual);
    }
}
