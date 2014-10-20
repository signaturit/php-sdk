Signaturit PHP SDK
=====================
This package is a PHP wrapper around the Signaturit API. If you didn't read the documentation yet, maybe it's time to take a look [here](http://docs.signaturit.com/).

You'll need at least PHP 5.3 to use this package.

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

## Signature request

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

### Count signature requests

Count your signature requests.

```php
$response = $client->countSignatures();
```

### Get signature request

Get the information regarding a single signature request passing its ID.

```php
$response = $client->getSignature('a066298d-2877-11e4-b641-080027ea3a6e');
```

### Get signature documents

Get all documents from a signature request.

```php
$response = $client->getSignatureDocuments('a066298d-2877-11e4-b641-080027ea3a6e')
```

### Get signature document

Get a single document from a signature request.

```php
$response = $client->getSignatureDocument('a066298d-2877-11e4-b641-080027ea3a6e', 'd474a1eb-2877-11e4-b641-080027ea3a6e');
```

### Signature request

Create a new signature request. Check all available [options](http://docs.signaturit.com/api/#sign_create_sign).

```php
$filePath = '/documents/contracts/receipt250.pdf';
$recipients = array('email' => 'john.doe@example.com', 'fullname' => 'John Doe');
$options = array('subject' => 'Receipt no. 250', 'body' => 'Please sign the receipt');

$response = $client->createSignatureRequest($filePath, $recipients, $options);
```

### Cancel signature request

Cancel a signature request.

```
$response = client.cancelSignatureRequest('a066298d-2877-11e4-b641-080027ea3a6e');
```

### Get audit trail

Get the audit trail of a signature request document and save it locally.

```php
$response = $client->getAuditTrail('a066298d-2877-11e4-b641-080027ea3a6e', 'd474a1eb-2877-11e4-b641-080027ea3a6e','/local/path/for/doc.pdf');
```

### Get signed document

Get the signed document of a signature request document and save it locally.

```php
$response = $client->getSignedDocument('a066298d-2877-11e4-b641-080027ea3a6e', 'd474a1eb-2877-11e4-b641-080027ea3a6e','/local/path/for/doc.pdf');
```

## Account

### Get account

Retrieve the information of your account.

```php
$response = $client->getAccount();
```

### Set document storage

Set your own storage credentials, to store a copy of the documents. You can get all the info of credential types [here](http://docs.signaturit.com/api/#account_set_credentials).

```php
$credentials = array(
    'user' => 'remote',
    'port' => 22,
    'dir' => '/home/remote/storage',
    'host' => '1.2.3.4',
    'auth_method' => 'PASS',
    'password' => 'changeit'
);

$response = client->setDocumentStorage('sftp', $credentials);
```

### Revert to default document storage

If you ever want to store your files in Signaturit's servers just run this method:

```php
$client->revertToDefaultDocumentStorage();
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

Create a new branding. You can check all branding params [here](http://docs.signaturit.com/api/#set_branding).`

```php
$options = array(
    'corporate_layout_color' => '#FFBF00',
    'corporate_text_color' => '#2A1B0A',
    'application_texts' => array('sign_button' => 'Sign!')
);

$response = $client->createBranding($options);
```

### Update branding

Update a single branding.

```php
$options = array('application_texts' => array('send_button' => 'Send!'));

$response = $client->updateBranding('6472aad7-2877-11e4-b641-080027ea3a6e', $options);
```

### Update branding logo

Change the branding logo.

```php
$filePath = '/logos/new_logo.png';

$response = $client->updateBrandingLogo('6472aad7-2877-11e4-b641-080027ea3a6e', $filePath);
```

### Update branding template

Change a template. Learn more about the templates [here](http://docs.signaturit.com/api/#put_template_branding).

```php
$filePath = '/templates/sign_request_template.html';

$response = $client->updateBrandingTemplate('6472aad7-2877-11e4-b641-080027ea3a6e', 'sign_request', $filePath);
```

## Template

### Get all templates

Retrieve all data from your templates.

```php
$response = $client->getTemplates();
```
