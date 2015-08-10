<?php

namespace Signaturit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Post\PostFile;

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
     * @var Client
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

        $this->client = new GuzzleClient();

        $this->url = $production ? self::PROD_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * @return array
     */
    public function getAccount()
    {
        return $this->request('GET', 'v2/account.json')->json();
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignature($signatureId)
    {
        return $this->request('GET', "v2/signs/$signatureId.json")->json();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $conditions
     *
     * @return array
     */
    public function getSignatures($limit = 100, $offset = 0, $conditions = []) {
        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];

        foreach ($conditions as $key => $value) {
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

        $path = 'v2/signs.json?'.http_build_query($params);

        return $this->request('GET', $path)->json();
    }

    /**
     *
     * @param array $conditions
     * @return int
     */
    public function countSignatures($conditions = [])
    {
        $params = [];

        foreach ($conditions as $key => $value) {
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

        $path = 'v2/signs/count.json?'.http_build_query($params);

        return $this->request('GET', $path)->json()['count'];
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     *
     * @return array
     */
    public function getSignatureDocument($signatureId, $documentId)
    {
        return $this->request('GET', "v2/signs/$signatureId/documents/$documentId.json")->json();
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignatureDocuments($signatureId)
    {
        return $this->request('GET', "v2/signs/$signatureId/documents.json")->json();
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
            $this->request('GET', "v2/signs/$signatureId/documents/$documentId/download/doc_proof")->getBody()
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
            $this->request('GET', "v2/signs/$signatureId/documents/$documentId/download/signed")->getBody()
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
        $recipients = (array) $recipients;
        $files      = (array) $files;

        $params['recipients'] = isset($recipients['email']) ? [$recipients] : $recipients;

        foreach ($files as $i => $path) {
            $params["files[$i]"] =  new PostFile(
                "files[$i]",
                fopen($path, 'r')
            );
        }

        return $this->request('POST', 'v2/signs.json', $params)->json();
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function cancelSignature($signatureId)
    {
        return $this->request('PATCH', "v2/signs/$signatureId/cancel.json")->json();
    }

    /**
     * @param string $signatureId
     * @param $documentId
     *
     * @return array
     */
    public function sendSignatureReminder($signatureId, $documentId)
    {
        return $this->request('POST', "v2/signs/$signatureId/documents/$documentId/reminder.json")->json();
    }

    /**
     * @param string $brandingId
     *
     * @return array
     */
    public function getBranding($brandingId)
    {
        return $this->request('GET', "v2/brandings/$brandingId.json")->json();
    }

    /**
     * @return array
     */
    public function getBrandings()
    {
        return $this->request('GET', "v2/brandings.json")->json();
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function createBranding(array $params = [])
    {
        return $this->request('POST', "v2/brandings.json", $params)->json();
    }

    /**
     * @param string $brandingId
     * @param array $params
     *
     * @return array
     */
    public function updateBranding($brandingId, array $params)
    {
        return $this->request('PATCH', "v2/brandings/$brandingId.json", $params)->json();
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

        return $this->request('PUT', "v2/brandings/$brandingId/logo.json", $data)->json();
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

        return $this->request('PUT', "v2/brandings/$brandingId/emails/$template.json", $data)->json();
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->request('GET', 'v2/templates.json')->json();
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
        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];

        foreach ($conditions as $key => $value) {
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

        $path = 'v3/emails.json?'.http_build_query($conditions);

        return $this->request('GET', $path)->json();
    }

    /**
     * @param array $conditions
     *
     * @return array
     */
    public function countEmails($conditions = [])
    {
        foreach ($conditions as $key => $value) {
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

        $path = 'v3/emails/count.json?'.http_build_query($conditions);

        return $this->request('GET', $path)->json();
    }

    /**
     * @param $emailId
     *
     * @return array
     */
    public function getEmail($emailId)
    {
        return $this->request('GET', "v3/emails/$emailId.json")->json();
    }

    /**
     * @param $emailId
     *
     * @return array
     */
    public function getEmailCertificates($emailId)
    {
        return $this->request('GET', "v3/emails/$emailId/certificates.json")->json();
    }

    /**
     * @param $emailId
     * @param $certificateId
     *
     * @return array
     */
    public function getEmailCertificate($emailId, $certificateId)
    {
        return $this->request('GET', "v3/emails/$emailId/certificates/$certificateId.json")->json();
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

        $params['recipients'] = isset($recipients['email']) ? [$recipients] : $recipients;

        foreach ($files as $i => $path) {
            $params["files[$i]"] =  new PostFile(
                "files[$i]",
                fopen($path, 'r')
            );
        }

        $params['subject'] = $subject;
        $params['body']    = $body;

        return $this->request('POST', 'v3/emails.json', $params)->json();
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
            $this->request('GET', "v3/emails/$emailId/documents/$certificateId/download/audit_trail")->getBody()
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
            $this->request('GET', "v3/emails/$emailId/documents/$certificateId/download/original")->getBody()
        );
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $params
     *
     * @return Response
     */
    protected function request($method, $path, $params = [])
    {
        $data['headers'] = ['Authorization' => "Bearer $this->accessToken", 'user-agent' => 'signaturit-php-sdk 0.0.4'];
        $data['body']    = $params;

        $request = $this->client->createRequest($method, "$this->url/$path", $data);

        $response = $this->client->send($request);

        return $response;
    }
}
