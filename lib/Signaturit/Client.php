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
    const SANDBOX_BASE_URL = 'http://api.sandbox.signaturit.com';

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

        $this->client = new GuzzleClient(['headers' => ['Authorization' => "Bearer $this->accessToken", 'user-agent' => 'signaturit-php-sdk 0.0.4']]);

        $this->url = $production ? self::PROD_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * @return array
     */
    public function getAccount()
    {
        return $this->request('get', 'v2/account.json');
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignature($signatureId)
    {
        return $this->request('get', "v2/signs/$signatureId.json");
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


        return $this->request('get', 'v2/signs.json', ['query' => $params]);
    }

    /**
     *
     * @param array $conditions
     * @return int
     */
    public function countSignatures($conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        return $this->request('get', 'v2/signs/count.json?', ['query' => $params])['count'];
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     *
     * @return array
     */
    public function getSignatureDocument($signatureId, $documentId)
    {
        return $this->request('get', "v2/signs/$signatureId/documents/$documentId.json");
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignatureDocuments($signatureId)
    {
        return $this->request('get', "v2/signs/$signatureId/documents.json");
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     * @param string $path
     */
    public function downloadAuditTrail($signatureId, $documentId, $path)
    {
        file_put_contents(
            $path,
            $this->request(
                'get',
                "v2/signs/$signatureId/documents/$documentId/download/doc_proof",
                [],
                false
            )
        );
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     * @param string $path
     */
    public function downloadSignedDocument($signatureId, $documentId, $path)
    {
        file_put_contents(
            $path,
            $this->request(
                'get',
                "v2/signs/$signatureId/documents/$documentId/download/signed",
                [],
                false
            )
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

        return $this->request('post', 'v2/signs.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function cancelSignature($signatureId)
    {
        return $this->request('patch', "v2/signs/$signatureId/cancel.json");
    }

    /**
     * @param string $signatureId
     * @param $documentId
     *
     * @return array
     */
    public function sendSignatureReminder($signatureId, $documentId)
    {
        return $this->request('post', "v2/signs/$signatureId/documents/$documentId/reminder.json");
    }

    /**
     * @param string $brandingId
     *
     * @return array
     */
    public function getBranding($brandingId)
    {
        return $this->request('get', "v2/brandings/$brandingId.json");
    }

    /**
     * @return array
     */
    public function getBrandings()
    {
        return $this->request('get', "v2/brandings.json");
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function createBranding(array $params = [])
    {
        return $this->request('post', "v2/brandings.json", [ 'json' => $params]);
    }

    /**
     * @param string $brandingId
     * @param array $params
     *
     * @return array
     */
    public function updateBranding($brandingId, array $params)
    {
        return $this->request('patch', "v2/brandings/$brandingId.json", [ 'json' => $params ]);
    }

    /**
     * @param string $brandingId
     * @param string $filePath
     *
     * @return array
     */
    public function updateBrandingLogo($brandingId, $filePath)
    {
        $data = file_get_contents($filePath);

        return $this->request('put', "v2/brandings/$brandingId/logo.json", ["body" => $data]);
    }

    /**
     * @param string $brandingId
     * @param string $template
     * @param string $filePath
     *
     * @return array
     */
    public function updateBrandingEmail($brandingId, $template, $filePath)
    {
        $data = file_get_contents($filePath);

        return $this->request('put', "v2/brandings/$brandingId/emails/$template.json", ["body" => $data]);
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->request('get', 'v2/templates.json');
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
     * @param $emailId
     *
     * @return array
     */
    public function getEmailCertificates($emailId)
    {
        return $this->request('get', "v3/emails/$emailId/certificates.json");
    }

    /**
     * @param $emailId
     * @param $certificateId
     *
     * @return array
     */
    public function getEmailCertificate($emailId, $certificateId)
    {
        return $this->request('get', "v3/emails/$emailId/certificates/$certificateId.json");
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
        $recipients = (array) $recipients;
        $files      = (array) $files;

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
     * @param string $path
     */
    public function downloadEmailAuditTrail($emailId, $certificateId, $path)
    {
        file_put_contents(
            $path,
            $this->request(
                'get',
                "v3/emails/$emailId/documents/$certificateId/download/audit_trail",
                [],
                false
            )
        );
    }

    /**
     * @param string $emailId
     * @param string $certificateId
     * @param string $path
     */
    public function downloadEmailOriginalFile($emailId, $certificateId, $path)
    {
        file_put_contents(
            $path,
            $this->request(
                'get',
                "v3/emails/$emailId/documents/$certificateId/download/original",
                [],
                false
            )
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
            $multiFormData[] = [
                'name'     => "recipients[$recipientNumber][email]",
                'contents' => $recipient['email']
            ];

            $multiFormData[] = [
                'name'     => "recipients[$recipientNumber][fullname]",
                'contents' => $recipient['fullname']
            ];
        }

        foreach ($files as $i => $path) {
            $multiFormData[] =  [
                'name'    => "files[$i]",
                'contents' =>  fopen($path, 'r')
            ];
        }

        foreach ($params as $paramKey => $paramValue) {
            if (is_array($paramValue)) {
                foreach ($paramValue as $innerKey => $innerValue) {
                    $multiFormData[] = [
                        'name'     => "data[$innerKey]",
                        'contents' => $innerValue
                    ];
                }
                continue;
            }

            $multiFormData[] = [
                'name'     => $paramKey,
                'contents' => $paramValue
            ];
        }

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
        $response = $this->client->$method("$this->url/$path", $params)->getBody();

        if ($json) {
            $response = json_decode($response, true);
        }

        return $response;
    }
}
