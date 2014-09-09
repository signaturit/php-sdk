<?php

namespace Signaturit;

use Buzz\Browser;
use Buzz\Exception\ClientException;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\RequestInterface;
use Buzz\Message\Response;

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
     * @var array
     */
    private $authHeader;

    /**
     * @var Browser
     */
    private $buzz;

    /**
     * @var string
     */
    private $url;

    /**
     * @param string $accessToken
     * @param bool $production
     */
    public function __construct($accessToken, $production = false)
    {
        $this->authHeader = array('Authorization' => "Bearer $accessToken");

        $this->buzz = new Browser();

        $this->url = $production ? self::PROD_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * @return array
     */
    public function getAccount()
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/account.json"
        );
    }

    /**
     * @param string $type
     * @param array $params
     *
     * @return array
     */
    public function setDocumentStorage($type, array $params)
    {
        $safeParams = Resolver::resolveDocumentStorageOptions($type, $params);

        try {
            /** @var Response $response */
            $response = $this->buzz
                ->submit(
                    "$this->url/v2/account/storage.json",
                    $safeParams,
                    RequestInterface::METHOD_POST,
                    $this->authHeader
                );
        } catch (ClientException $e) {
            return $this->parseClientException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @return array
     */
    public function revertToDefaultDocumentStorage()
    {
        return $this->doRequest(
            RequestInterface::METHOD_DELETE,
            "$this->url/v2/account/storage.json"
        );
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignature($signatureId)
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/signs/$signatureId.json"
        );
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
        $url = "$this->url/v2/signs.json?limit=$limit&offset=$offset";
        $url = $status ? "$url&status=$status" : $url;
        $url = $since ? "$url&since={$since->format("")}" : $url;

        return $this->doRequest(
            RequestInterface::METHOD_GET,
            $url
        );
    }

    /**
     * @return int
     */
    public function countSignatures()
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/documents/count.json"
        );
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     *
     * @return array
     */
    public function getSignatureDocument($signatureId, $documentId)
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/signs/$signatureId/documents/$documentId.json"
        );
    }

    /**
     * @param string $signatureId
     *
     * @return array
     */
    public function getSignatureDocuments($signatureId)
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/signs/$signatureId/documents.json"
        );
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     * @param string $path
     */
    public function getAuditTrail($signatureId, $documentId, $path)
    {
        /** @var Response $response */
        $response = $this->buzz
            ->get(
                "$this->url/v2/signs/$signatureId/documents/$documentId/download/doc/proof",
                $this->authHeader
            );

        file_put_contents($path, $response->getContent());
    }

    /**
     * @param string $signatureId
     * @param string $documentId
     * @param string $path
     */
    public function getSignedDocument($signatureId, $documentId, $path)
    {
        /** @var Response $response */
        $response = $this->buzz
            ->get(
                "$this->url/v2/signs/$signatureId/documents/$documentId/download/signed",
                $this->authHeader
            );

        file_put_contents($path, $response->getContent());
    }

    /**
     * @param string|string[] $files
     * @param string[] $recipients
     * @param array $params
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function createSignatureRequest($files, array $recipients, array $params = array())
    {
        if (!is_array($files)) {
            $files = array($files);
        }

        if (isset($recipients['email'])) {
            $recipients = array($recipients);
        }

        $safeParams = Resolver::resolveCreateSignatureRequestOptions($params);

        for ($i = 0; $i < count($files); $i++) {
            $safeParams['files'][$i] = new FormUpload($files[$i], 'application/pdf');
        }
        $safeParams['recipients'] = $recipients;

        try {
            /** @var Response $response */
            $response = $this->buzz
                ->submit(
                    "$this->url/v2/signs.json",
                    $safeParams,
                    RequestInterface::METHOD_POST,
                    $this->authHeader
                );
        } catch (ClientException $e) {
            return $this->parseClientException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param $brandingId
     *
     * @return array
     */
    public function getBranding($brandingId)
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/brandings/$brandingId.json"
        );
    }

    /**
     * @return array
     */
    public function getBrandings()
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/brandings.json"
        );
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function createBranding(array $params = array())
    {
        $safeParams = Resolver::resolveBrandingOptions($params);

        try {
            /** @var Response $response */
            $response = $this->buzz->submit(
                "$this->url/v2/brandings.json",
                $safeParams,
                RequestInterface::METHOD_POST,
                $this->authHeader
            );
        } catch (ClientException $e) {
            return $this->parseClientException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param string $brandingId
     * @param array $params
     *
     * @return array
     */
    public function updateBranding($brandingId, array $params)
    {
        $safeParams = Resolver::resolveBrandingOptions($params);

        try {
            /** @var Response $response */
            $response = $this->buzz
                ->submit(
                    "$this->url/v2/brandings/$brandingId.json",
                    $safeParams,
                    RequestInterface::METHOD_PATCH,
                    $this->authHeader
                );
        } catch (ClientException $e) {
            return $this->parseClientException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param string $brandingId
     * @param string $filePath
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function updateBrandingLogo($brandingId, $filePath)
    {
        return $this->doRequest(
            RequestInterface::METHOD_PUT,
            "$this->url/v2/brandings/$brandingId/logo.json",
            file_get_contents($filePath)
        );
    }

    /**
     * @param string $brandingId
     * @param string $template
     * @param string $filePath
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function updateBrandingTemplate($brandingId, $template, $filePath)
    {
        return $this->doRequest(
            RequestInterface::METHOD_PUT,
            "$this->url/v2/brandings/$brandingId/emails/$template.json",
            file_get_contents($filePath)
        );
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->doRequest(
            RequestInterface::METHOD_GET,
            "$this->url/v2/templates.json"
        );
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $content
     *
     * @return array
     */
    protected function doRequest($method, $url, $content = '')
    {
        try {
            /** @var Response $response */
            $response = $this->buzz->call($url, $method, $this->authHeader, $content);
        } catch (ClientException $e) {
            return $this->parseClientException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    protected function parseResponse(Response $response)
    {
        switch ($response->getStatusCode()) {
            case 200:
                return json_decode($response->getContent(), true);

            case 204:
                return true;

            default:
                return array(
                    'error' => array(
                        'status' => $response->getStatusCode(),
                        'message' => $response->getReasonPhrase()
                    )
                );
        }
    }

    /**
     * @param ClientException $e
     *
     * @return array
     */
    protected function parseClientException(ClientException $e)
    {
        return array('exception' => $e->getMessage());
    }
}
