========================
DO NOT USE MASTER BRANCH
========================

Signaturit PHP SDK
=====================
This package is a PHP wrapper around the Signaturit API. If you didn't read the documentation yet, maybe it's time to take a look [here](https://docs.signaturit.com/).

You'll need at least PHP 5.5 to use this package (http://php.net/supported-versions.php).

Configuration
-------------

The recommended way to install the SDK is through [Composer](https://getcomposer.org/).

```bash
composer require signaturit/signaturit-sdk
```

Then import Composer's autoload.php file from your script and instantiate the
Client class passing in your API access token.

```php
require_once __DIR__.'/vendor/autoload.php';

$accessToken = 'OTllYjUwM2NhYmNjNmJlYTZlNWEzNWYzYmZjNTRiZWI2YjU0ZjUxNzUwZDRjZjEwZTA0ZTFkZWQwZGExNDM3ZQ';

$client = new Signaturit\Client($accessToken);
```

Please note that by default the client will use our sandbox API. When you are
ready to start using the production environment just get the correct access token and pass an additional argument to the constructor:

```php
$client = new Signaturit\Client($accessToken, true);
```

Examples
--------

## Signatures

### Count signature requests

Count your signature requests.

```php
$response = $client->countSignatures();
```

### Get all signature requests

Retrieve all data from your signature requests using different filters.

##### All signatures

```php
$response = $client->getSignatures();
```

##### Getting the last 50 signatures

```php
$response = $client->getSignatures(50);
```

##### Getting signatures with custom field "crm_id"

```php
$response = $client->getSignatures(100, 0, ['crm_id' => 'CUSTOM_ID'])
```

### Get signature request

Get the information regarding a single signature request passing its ID.

```php
$response = $client->getSignature('a066298d-2877-11e4-b641-080027ea3a6e');
```
### Signature request

Create a new signature request. You can check all signature [params](https://docs.signaturit.com/api/v3#sign_create_sign).

```php
$filePath   = '/documents/contracts/receipt250.pdf';
$recipients = ['email' => 'john.doe@example.com', 'name' => 'John Doe'];
$options    = ['subject' => 'Receipt no. 250', 'body' => 'Please sign the receipt'];

$response = $client->createSignature($filePath, $recipients, $options);
```

You can add custom info in your requests

```php
$filePath   = '/documents/contracts/receipt250.pdf';
$recipients = ['email' => 'john.doe@example.com', 'name' => 'John Doe'];
$options    = ['subject' => 'Receipt no. 250', 'body' => 'Please sign the receipt', 'data' => ['crm_id' => '45673']];

$response = $client->createSignature($filePath, $recipients, $options);
```

You can send templates with the fields filled
```php

$recipients = ['email' => 'john.doe@example.com', 'name' => 'John Doe'];
$options    = ['subject' => 'Receipt no. 250', 'body' => 'Please sign the receipt', 'templates' => ['template_name'], 'data' => ['widget_id' => 'default value']];

$response = $client->createSignature([], $recipients, $options);
```
### Cancel signature request

Cancel a signature request.

```php
$response = $client->cancelSignature('a066298d-2877-11e4-b641-080027ea3a6e');
```

### Send reminder

Send a reminder email.

```php
$response = $client->sendSignatureReminder('a066298d-2877-11e4-b641-080027ea3a6e');
```

### Get audit trail

Get the audit trail of a signature request document

```php
$response = $client->downloadAuditTrail('a066298d-2877-11e4-b641-080027ea3a6e', 'd474a1eb-2877-11e4-b641-080027ea3a6e');
```

### Get signed document

Get the signed document of a signature request document

```php
$response = $client->downloadSignedDocument('a066298d-2877-11e4-b641-080027ea3a6e', 'd474a1eb-2877-11e4-b641-080027ea3a6e');
```

## Branding

### Get brandings

Get all account brandings.

```php
$response = $client->getBrandings();
```

### Get branding

Get a single branding.

```php
$response = $client->getBranding('6472aad7-2877-11e4-b641-080027ea3a6e');
```

### Create branding

Create a new branding. You can check all branding [params](https://docs.signaturit.com/api/v3#set_branding).`

```php
$options = [
    'layout_color'      => '#FFBF00',
    'text_color'        => '#2A1B0A',
    'application_texts' => ['sign_button' => 'Sign!']
];

$response = $client->createBranding($options);
```

### Update branding

Update a single branding.

```php
$options = ['application_texts' => ['send_button' => 'Send!']];

$response = $client->updateBranding('6472aad7-2877-11e4-b641-080027ea3a6e', $options);
```

## Template

### Get all templates

Retrieve all data from your templates.

```php
$response = $client->getTemplates();
```

## Email

### Get emails

####Get all certified emails

```php
response = client->getEmails()
```

####Get last 50 emails

```php
response = client->getEmails(50)
```

####Navigate through all emails in blocks of 50 results

```php
response = client->getEmails(50, 50)
```

### Count emails

Count all certified emails

```php
response = client->countEmails()
```

### Get email

Get a single email

```php
client->getEmail('EMAIL_ID')
```

### Create email

Create a new certified email.

```php
response = client.createEmail(
    ['demo.pdf', 'receipt.pdf'],
    ['email' => 'john.doe@signaturit.com', 'name' => 'Mr John'],
    'Php subject',
    'Php body',
    []
)
```

### Get audit trail document

Get the audit trail document of an email request.

```php
response = client.downloadEmailAuditTrail('EMAIL_ID','CERTIFICATE_ID')
```

## Sms

### Get all sms

```php
response = client->getSms()
```

### Count emails

Count all certified sms

```php
response = client->countSms()
```

### Get sms

Get a single sms

```php
client->getSms('SMS_ID')
```

### Create sms

Create a new certified sms.

```php
response = client.createSms(
    [],
    ['phone' => '34123456', 'name' => 'Mr John'],
    'Php body',
    []
)
```

### Get audit trail document

Get the audit trail document of an sms request.

```php
response = client.downloadSmsAuditTrail('SMS_ID','CERTIFICATE_ID')
```

## Team

### Get users

Get all account users

```php
$response = $client->getUsers();
```

### Get seats

Get all account seats

```php
$response = $client->getSeats();
```

### Get user

Get a single user

```php
$response = $client->getUser('USER_ID');
```

### Invite users to join your team

Invite user to join the team
 
```php
$response = $client->inviteUser('bob.soap@signaturit.com', 'admin');
```

### Change user role

Change role for user

```php
$response = $client->changeUserRole('USER_ID', 'member');
```

### Remove user

Remove user from the team

```php
$response = $client->removeUser('USER_ID');
```

### Remove seat

Remove seat from the team

```php
$response = $client->removeSeat('SEAT_ID');
```

### Get groups

Get all account groups

```php
$response = $client->getGroups();
```

### Get group

Get a single group

```php
$response = $client->getGroup('GROUP_ID');
```

### Create group

Create a new group

```php
$response = $client->createGroup('test_node');
```

### Update group

Update group name

```php
$response = $client->updateGroup('GROUP_ID', 'new_name');
```

### Delete group

Delete a group

```php
$response = $client->deleteGroup('GROUP_ID');
```

### Add manager to group

Add a manager to a group

```php
$response = $client->addManagerToGroup('GROUP_ID', 'USER_ID');
```

### Add manager to group

Add a member to a group

```php
$response = $client->addMemberToGroup('GROUP_ID', 'USER_ID');
```                     

### Delete manager from group

Remove a manager from group

```php
$response = $client->removeManagerFromGroup('GROUP_ID', 'USER_ID');
```

### Delete member from group

Remove a member from group

```php
$response  = $client->removeMemberFromGroup('GROUP_ID', 'USER_ID');
```


## Contacts

### Get contacts

Get all contacts

```php
$response = $client->getContacts();
```

### Get contact

Get a single contact

```php
$response = $client->getContact('CONTACT_ID');
```

### Create contact

Create a new contact

```php
$response = $client->createContact('email@signaturit.com', 'name');
```

### Update contact

Update contact

```php
$response = $client->updateContact('CONTACT_ID', 'new_email@signaturit.com', 'name1');
```

### Delete contact

Delete a contact

```php
$response = $client->deleteContact('CONTACT_ID');
```

### Get subscriptions

Get all subscriptions

```php
$response = $client->getSubscriptions();
```

### Get subscription

Get a single subscription

```php
$response = $client->getSubscription('SUBSCRIPTION_ID');
```

### Create subscription

Create a new subscription

```php
$response = $client->createSubscription('http://httpbin.org/post', 'email_processed');
```

### Update subscription

Update contact

```php
$response = $client->updateSubscription('SUBSCRIPTION_ID', null, 'email_delivered');
```

### Delete subscription

Delete a subscription

```php
$response = $client->deleteSubscription('SUBSCRIPTION_ID');
```
