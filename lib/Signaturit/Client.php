<?php

namespace Signaturit;

use GuzzleHttp\Client as GuzzleClient;
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
     * @param string $type
     * @param array $params
     *
     * @return array
     */
    public function setDocumentStorage($type, array $params)
    {
        $params['type'] = $type;

        return $this->request('PATCH', 'v2/account.json', $params)->json();
    }

    /**
     * @return array
     */
    public function revertToDefaultDocumentStorage()
    {
        return $this->request('DELETE', 'v2/account/storage.json');
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
     * @param int $status
     * @param \DateTime $since
     *
     * @return array
     */
    public function getSignatures($limit = 100, $offset = 0, $status = null, \DateTime $since = null)
    {
        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];

        if ($status) {
            $params['status'] = $status;
        }

        if ($since) {
            $params['since'] = $since;
        }

        $path = 'v2/signs.json?'.http_build_query($params);

        return $this->request('GET', $path)->json();
    }

    /**
     * @return int
     */
    public function countSignatures()
    {
        return $this->request('GET', 'v2/documents/count.json')->json()['count'];
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
    public function getAuditTrail($signatureId, $documentId, $path)
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
    public function getSignedDocument($signatureId, $documentId, $path)
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
    public function createSignatureRequest($files, $recipients, array $params = [])
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
    public function cancelSignatureRequest($signatureId)
    {
        return $this->request('PATCH', "v2/signs/$signatureId/cancel.json")->json();
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
    public function updateBrandingTemplate($brandingId, $template, $filePath)
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
     * @param string $method
     * @param string $path
     * @param array  $params
     *
     * @return Response
     */
    protected function request($method, $path, $params = [])
    {
        $data['headers'] = ['Authorization' => "Bearer $this->accessToken"];
        $data['body']    = $params;

        $request = $this->client->createRequest($method, "$this->url/$path", $data);

        $response = $this->client->send($request);

        return $response;
    }
}
