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
    const SANDBOX_BASE_URL = 'https://api.sandbox.signaturit.com';

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

        $this->client = new GuzzleClient(
            [
                'headers'    => ['Authorization' => "Bearer $this->accessToken",
                'user-agent' => 'signaturit-php-sdk 1.1.0']
            ]
        );

        $this->url = $production ? self::PROD_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * @param array $conditions
     * @return int
     */
    public function countSignatures($conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

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
        if (!is_array($files)) {
            $files = [ $files ];
        }

        $files         = (array) $files;

        $recipients    = (array) $recipients;

        $multiFormData = $this->extractFormParameters($files, $recipients, $params);

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
        return $this->request('post', "v3/brandings.json", ['json' => $params]);
    }

    /**
     * @param string $brandingId
     * @param array $params
     *
     * @return array
     */
    public function updateBranding($brandingId, array $params)
    {
        return $this->request('patch', "v3/brandings/$brandingId.json", ['json' => $params ]);
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getTemplates($limit = 100, $offset = 0)
    {
        $params = [];

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/templates.json', ['query' => $params]);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param $conditions
     *
     * @return array
     */
    public function getEmails($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/emails.json', ['query' => $params]);
    }

    /**
     * @param array $conditions
     *
     * @return array
     */
    public function countEmails($conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        return $this->request('get', 'v3/emails/count.json', ['query' => $params]);
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

        $recipients      = (isset($recipients['to']) || isset($recipients['cc']) || isset($recipients['bcc'])) ?
            [ $recipients ]
            :
            $recipients;

        $multiFormData   = $this->extractFormParameters($files, $recipients, $params);

        $multiFormData[] = ['name' => 'subject', 'contents' => $subject];
        $multiFormData[] = ['name' => 'body', 'contents' => $body];

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
            "v3/emails/$emailId/certificates/$certificateId/download/audit_trail",
            [],
            false
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param $conditions
     *
     * @return array
     */
    public function getSMS($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/sms.json', ['query' => $params]);
    }

    /**
     * @param array $conditions
     *
     * @return array
     */
    public function countSMS($conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        return $this->request('get', 'v3/sms/count.json', ['query' => $params]);
    }

    /**
     * @param $smsId
     *
     * @return array
     */
    public function getSingleSMS($smsId)
    {
        return $this->request('get', "v3/sms/$smsId.json");
    }

    /**
     * @param $files
     * @param $recipients
     * @param $body
     * @param $params
     *
     * @return array
     */
    public function createSMS($files, $recipients, $body, $params)
    {
        $recipients      = (array) $recipients;

        $files           = (array) $files;

        $recipients      = isset($recipients['phone']) ?
            [ $recipients ]
            :
            $recipients;

        $multiFormData   = $this->extractFormParameters($files, $recipients, $params);

        $multiFormData[] = ['name' => 'body', 'contents' => $body];

        return $this->request('post', 'v3/sms.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param string $smsId
     * @param string $certificateId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function downloadSMSAuditTrail($smsId, $certificateId)
    {
        return $this->request(
            'get',
            "v3/sms/$smsId/certificates/$certificateId/download/audit_trail",
            [],
            false
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param $conditions
     *
     * @return array
     */
    public function getUsers($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/team/users.json', ['query' => $params]);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $conditions
     *
     * @return array
     */
    public function getSeats($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/team/seats.json', ['query' => $params]);
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function getUser($userId)
    {
        return $this->request('get', "v3/team/users/$userId.json");
    }

    /**
     * @param $email
     * @param $role
     *
     * @return array
     */
    public function inviteUser($email, $role)
    {
        $multiFormData = [
            ['name' => 'email', 'contents' => $email],
            ['name' => 'role', 'contents' => $role]
        ];

        return $this->request('post', 'v3/team/users.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param $userId
     * @param $role
     *
     * @return array
     *
     */
    public function changeUserRole($userId, $role)
    {
        $data = [
            'role' => $role
        ];

        return $this->request('patch', "v3/team/users/$userId.json", ['json' => $data]);
    }

    /**
     * @param $userId
     *
     * @return array
     *
     */
    public function removeUser($userId)
    {
        return $this->request('delete', "v3/team/users/$userId.json", []);
    }

    /**
     * @param $seatId
     *
     * @return array
     *
     */
    public function removeSeat($seatId)
    {
        return $this->request('delete', "v3/team/seats/$seatId.json", []);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $conditions
     *
     * @return array
     */
    public function getGroups($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/team/groups.json', ['query' => $params]);
    }

    /**
     * @param $groupId
     *
     * @return array
     */
    public function getGroup($groupId)
    {
        return $this->request('get', "v3/team/groups/$groupId.json");
    }

    /**
     * @param $name
     * @return array
     */
    public function createGroup($name)
    {
        $multiFormData = [
            ['name' => 'name', 'contents' => $name],
        ];

        return $this->request('post', 'v3/team/groups.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param $groupId
     * @param $name
     * @return array
     *
     */
    public function updateGroup($groupId, $name)
    {
        $data = [
            'name' => $name
        ];

        return $this->request('patch', "v3/team/groups/$groupId.json", ['json' => $data]);
    }

    /**
     * @param $groupId
     *
     * @return array
     *
     */
    public function deleteGroup($groupId)
    {
        return $this->request('delete', "v3/team/groups/$groupId.json", []);
    }

    /**
     * @param $groupId
     * @param $userId
     *
     * @return array
     */
    public function addManagerToGroup($groupId, $userId)
    {
        return $this->request('post', "v3/team/groups/$groupId/managers/$userId.json");
    }

    /**
     * @param $groupId
     * @param $userId
     *
     * @return array
     */
    public function addMemberToGroup($groupId, $userId)
    {
        return $this->request('post', "v3/team/groups/$groupId/members/$userId.json");
    }

    /**
     * @param $groupId
     * @param $userId
     *
     * @return array
     */
    public function removeManagerFromGroup($groupId, $userId)
    {
        return $this->request('delete', "v3/team/groups/$groupId/managers/$userId.json");
    }

    /**
     * @param $groupId
     * @param $userId
     *
     * @return array
     */
    public function removeMemberFromGroup($groupId, $userId)
    {
        return $this->request('delete', "v3/team/groups/$groupId/managers/$userId.json");
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $conditions
     *
     * @return array
     */
    public function getContacts($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/contacts.json', ['query' => $params]);
    }

    /**
     * @param $contactId
     *
     * @return array
     */
    public function getContact($contactId)
    {
        return $this->request('get', "v3/contacts/$contactId.json");
    }

    /**
     * @param $email
     * @param $name
     * @return array
     */
    public function createContact($email, $name)
    {
        $multiFormData = [
            ['name' => 'name', 'contents' => $name],
            ['name' => 'email', 'contents' => $email],
        ];

        return $this->request('post', 'v3/contacts.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param $contactId
     * @param $email
     * @param $name
     * @return array
     */
    public function updateContact($contactId, $email, $name)
    {
        $data = [];

        if ($email) {
            $data['email'] = $email;
        }

        if ($name) {
            $data['name'] = $name;
        }

        return $this->request('patch', "v3/contacts/$contactId.json", ['json' => $data]);
    }

    /**
     * @param $contactId
     *
     * @return array
     *
     */
    public function deleteContact($contactId)
    {
        return $this->request('delete', "v3/contacts/$contactId.json", []);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $conditions
     *
     * @return array
     */
    public function getSubscriptions($limit = 100, $offset = 0, $conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        return $this->request('get', 'v3/subscriptions.json', ['query' => $params]);
    }

    /**
     * @param array $conditions
     *
     * @return array
     */
    public function countSubscriptions($conditions = [])
    {
        $params = $this->extractQueryParameters($conditions);

        return $this->request('get', 'v3/subscriptions/count.json', ['query' => $params]);
    }

    /**
     * @param $subscriptionId
     *
     * @return array
     */
    public function getSubscription($subscriptionId)
    {
        return $this->request('get', "v3/subscriptions/$subscriptionId.json");
    }

    /**
     * @param $url
     * @param $events
     * @return array
     */
    public function createSubscription($url, $events)
    {
        $multiFormData = [
            ['name' => 'url', 'contents' => $url],
            ['name' => 'events', 'contents' => $events],
        ];

        return $this->request('post', 'v3/subscriptions.json', ['multipart' => $multiFormData]);
    }

    /**
     * @param $subscriptionId
     * @param $url
     * @param $events
     * @return array
     */
    public function updateSubscription($subscriptionId, $url, $events)
    {
        $data = [];

        if ($url) {
            $data['url'] = $url;
        }

        if ($events) {
            $data['events'] = $events;
        }

        return $this->request('patch', "v3/subscriptions/$subscriptionId.json", ['json' => $data]);
    }

    /**
     * @param $subscriptionId
     *
     * @return array
     *
     */
    public function deleteSubscription($subscriptionId)
    {
        return $this->request('delete', "v3/subscriptions/$subscriptionId.json", []);
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
                $value = implode(',', $value);
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
            $parentKey = strlen($parent) === 0 ? $key : "{$parent}[$key]";

            if (is_array($value)) {
                $this->fillArray($formArray, $parameters[$key], $parentKey);
            } else {
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
        $recipients = isset($recipients['email']) ? [$recipients] : $recipients;

        $multiFormData = [];

        $recipientNumber = 0;

        foreach ($recipients as $recipient) {
            $this->fillArray($multiFormData, $recipient, "recipients[$recipientNumber]");

            ++$recipientNumber;
        }

        foreach ($files as $i => $path) {
            $multiFormData[] =  [
                'name'     => "files[$i]",
                'contents' => fopen($path, 'r')
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
