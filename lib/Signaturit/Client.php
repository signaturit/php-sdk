<?php

namespace Signaturit;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /**
     * Signaturit's production API URL
     */
    const PROD_BASE_URL = 'https://api.signaturit.com';

    /**
     * Signaturit's sandbox API URL
     */
    const SANDBOX_BASE_URL = 'http://sandbox.signaturit.com';

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @param string $accessToken
     * @param bool   $production
     */
    public function __construct($accessToken, $production = false)
    {
        $this->accessToken = $accessToken;

        $this->client = new GuzzleClient(['headers' => ['Authorization' => "Bearer $this->accessToken", 'user-agent' => 'signaturit-php-sdk 1.0.0']]);

        $this->url = $production ? self::PROD_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * @param array $conditions
     * @return int
     */
    public function countSignatures($conditions = [])
    {
        $params   = $this->extractQueryParameters($conditions);

        return $this->request('get', 'v3/signatures/count.json', ['query' => $params]);
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignature($signatureId)
    {
        return $this->request('get', "v3/signatures/$signatureId.json");
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $conditions
     *
     * @return array
     */
    public function getSignatures($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit'] = $limit;
        $params['offset'] = $offset;


        return $this->request('get', 'v3/signatures.json', ['query' => $params]);
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function downloadAuditTrail($signatureId, $documentId)
    {
        return $this->request(
            'get',
            "v3/signatures/$signatureId/documents/$documentId/download/audit_trail",
            [],
            false
        );
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function downloadSignedDocument($signatureId, $documentId)
    {
        return $this->request(
            'get',
            "v3/signatures/$signatureId/documents/$documentId/download/signed",
            [],
            false
        );
    }

    /**
     * @param string|string[] $files
     * @param string|string[] $recipients
     * @param array           $params
     *
     * @return array
     */
    public function createSignature($files, $recipients, array $params = [])
    {
        $recipients      = (array) $recipients;

        $multiFormData   = $this->extractFormParameters($files, $recipients, $params);

        return $this->request('post', 'v3/signatures.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function cancelSignature($signatureId)
    {
        return $this->request('patch', "v3/signatures/$signatureId/cancel.json");
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function sendSignatureReminder($signatureId)
    {
        return $this->request('post', "v3/signatures/$signatureId/reminder.json");
    }

    /**
     * @param string $brandingId
     *
     * @return array
     */
    public function getBranding($brandingId)
    {
        return $this->request('get', "v3/brandings/$brandingId.json");
    }

    /**
     * @return array
     */
    public function getBrandings()
    {
        return $this->request('get', "v3/brandings.json");
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function createBranding(array $params = [])
    {
        return $this->request('post', "v3/brandings.json", [ 'json' => $params]);
    }

    /**
     * @param string $brandingId
     * @param array $params
     *
     * @return array
     */
    public function updateBranding($brandingId, array $params)
    {
        return $this->request('patch', "v3/brandings/$brandingId.json", [ 'json' => $params ]);
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->request('get', 'v3/templates.json');
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param $conditions
     *
     * @return array
     */
    public function getEmails($limit=100, $offset=0, $conditions)
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('v3/emails.json', ['query' => $params]);
    }

    /**
     * @param array $conditions
     *
     * @return array
     */
    public function countEmails($conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        return $this->request('v3/emails/count.json', ['query' => $params]);
    }

    /**
     * @param $emailId
     *
     * @return array
     */
    public function getEmail($emailId)
    {
        return $this->request('get', "v3/emails/$emailId.json");
    }

    /**
     * @param $files
     * @param $recipients
     * @param $subject
     * @param $body
     * @param $params
     *
     * @return array
     */
    public function createEmail($files, $recipients, $subject, $body, $params)
    {
        $recipients      = (array) $recipients;

        $files           = (array) $files;

        $multiFormData   = $this->extractFormParameters($files, $recipients, $params);

        $multiFormData[] = [
            'name' => 'subject',
            'contents' => $subject
        ];

        $multiFormData[] = [
            'name' => 'body',
            'contents' => $body
        ];

        return $this->request('post', 'v3/emails.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param string $emailId
     * @param string $certificateId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function downloadEmailAuditTrail($emailId, $certificateId)
    {
        return $this->request(
                'get',
                "v3/emails/$emailId/documents/$certificateId/download/audit_trail",
                [],
                false
            );
    }

    /**
     * Extract query parameters
     *
     * @param $params
     *
     * @return array
     */
    protected function extractQueryParameters($params)
    {
        foreach ($params as $key => $value) {
            if ($key === 'data') {
                $data = [];

                foreach ($value as $dataKey => $dataValue) {
                    $data[$dataKey] = $dataValue;
                }

                $value = $data;
            }

            if ($key === 'ids') {
                $value = implode(",", $value);
            }

            $params[$key] = $value;
        }

        return $params;
    }

    /**
     * Fill array with values
     *
     * @param $formArray
     * @param $parameters
     * @param string $parent
     */
    protected function fillArray(&$formArray, $parameters, $parent)
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $parentKey = strlen($parent) === 0 ? $key : "{$parent}[$key]";

                $this->fillArray($formArray, $parameters[$key], $parentKey);
            } else {
                $parentKey = strlen($parent) === 0 ? $key : "{$parent}[$key]";

                $formArray[] = [
                    'name'     =>  $parentKey,
                    'contents' => (string) $value
                ];
            }
        }
    }

    /**
     * Extract basic data for post operations.
     *
     * @param $files
     * @param $recipients
     * @param $params
     *
     * @return array
     */
    protected function extractFormParameters($files, $recipients, $params)
    {
        $recipients      = isset($recipients['email']) ? [$recipients] : $recipients;

        $multiFormData   = [];

        $recipientNumber = 0;

        foreach ($recipients as $recipient) {
            $this->fillArray($multiFormData, $recipient, "recipients[$recipientNumber]");

            ++$recipientNumber;
        }

        foreach ($files as $i => $path) {
            $multiFormData[] =  [
                'name'    => "files[$i]",
                'contents' =>  fopen($path, 'r')
            ];
        }

        $this->fillArray($multiFormData, $params, '');

        return $multiFormData;
    }

    /**
     * @param $method
     * @param $path
     * @param array $params
     * @param bool $json
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function request($method, $path, $params = [], $json = true)
    {
        try {
            $response = $this->client->$method("$this->url/$path", $params)->getBody();

            if ($json) {
                $response = json_decode($response, true);
            }
        } catch (\Exception $exception) {
            $response = $exception->getMessage();
        }

        return $response;
    }
}
