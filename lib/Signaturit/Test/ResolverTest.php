<?php

namespace Signaturit\Test;

use Signaturit\Resolver;

class SignaturitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testResolveDocumentDocumentStorageOptionsDataProvider
     */
    public function testResolveDocumentDocumentStorageOptions($type, $parameters, $ok)
    {
        if (!$ok) {
            $this->setExpectedException('\Symfony\Component\OptionsResolver\Exception\ExceptionInterface');
        }

        Resolver::resolveDocumentStorageOptions($type, $parameters);
    }

    public function testResolveDocumentDocumentStorageOptionsDataProvider()
    {
        return array(
            // Valid s3 parameters
            array(
                's3',
                array(
                    'bucket' => 'bucket name',
                    'key' => 's3 key',
                    'secret' => 's3 secret'
                ),
                true
            ),

            // Invalid or missing s3 parameters
            array(
                's3',
                array(
                    'bucket' => 1234,
                    'key' => 's3 key',
                    'secret' => 's3 secret'
                ),
                false
            ),

            array(
                's3',
                array(
                    'bucket' => 'bucket name',
                    'secret' => 's3 secret'
                ),
                false
            ),

            // Valid sftp PASS parameters
            array(
                'sftp',
                array(
                    'host' => 'example.com',
                    'port' => 2222,
                    'dir' => '/some/abs/dir',
                    'user' => 'root',
                    'auth_method' => 'PASS',
                    'password' => 'user password'
                ),
                true
            ),

            // Valid sftp KEY parameters
            array(
                'sftp',
                array(
                    'host' => 'example.com',
                    'port' => 2222,
                    'dir' => '/some/abs/dir',
                    'user' => 'root',
                    'auth_method' => 'KEY',
                    'private' => 'private password',
                    'public' => 'public key',
                    'passphrase' => 'key passphrase'
                ),
                true
            ),

            // Invalid storage type
            array(
                'dropbox',
                array(),
                false
            )
        );
    }

    /**
     * @dataProvider testResolveBrandingOptionsDataProvider
     */
    public function testResolveBrandingOptions($parameters)
    {
        Resolver::resolveBrandingOptions($parameters);
    }

    public function testResolveBrandingOptionsDataProvider()
    {
        return array(
            // $parameters can be an empty array
            array(
                array()
            ),

            // Or contain up to all these keys:
            array(
                array(
                    'primary' => true,
                    'corporate_layout_color' => '#FF00FF',
                    'corporate_text_color' => '#00FF00',
                    'application_texts' => null,
                    'subject_tag' => 'My Tag',
                    'reminders' => array(1, 2, 3),
                    'expire_time' => 1234,
                    'callback_url' => 'http://example.com/landing',
                    'signature_pos_x' => 0.4,
                    'signature_pos_y' => 0.7,
                    'terms_and_conditions_label' => 'T&C Label',
                    'terms_and_conditions_body' => 'T&C Body',
                    'events_url' => 'https://example.com/parse'
                )
            )
        );
    }

    /**
     * @dataProvider testResolveCreateSignatureRequestOptionsDataProvider
     */
    public function testResolveCreateSignatureRequestOptions($parameters)
    {
        Resolver::resolveCreateSignatureRequestOptions($parameters);
    }

    public function testResolveCreateSignatureRequestOptionsDataProvider()
    {
        return array(
            // $parameters can be an empty array
            array(
                array()
            ),

            // Or contain up to all these keys:
            array(
                array(
                    'subject' => 'Request email subject',
                    'body' => 'Request email body',
                    'in_person' => true,
                    'sequential' => false,
                    'photo' => false,
                    'mandatory_pages' => array(1, 2, 3),
                    'branding_id' => 'my_branding_id'
                )
            )
        );
    }
}
