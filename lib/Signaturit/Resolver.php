<?php

namespace Signaturit;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Resolver
{
    public static function resolveDocumentStorageOptions($type, array $parameters)
    {
        $resolver = new OptionsResolver();

        switch($type) {
            case 's3':
                $resolver
                    ->setRequired(array('bucket', 'key', 'secret'))
                    ->setAllowedTypes(array(
                        'bucket' => 'string',
                        'key' => 'string',
                        'secret' => 'string'
                    ));
                break;
            case 'sftp':
                $resolver
                    ->setRequired(array('host', 'port', 'dir', 'user', 'auth_method'))
                    ->setOptional(array('password', 'private', 'public', 'passphrase'))
                    ->setAllowedTypes(array(
                        'host' => 'string',
                        'port' => 'int',
                        'dir' => 'string',
                        'user' => 'string',
                        'auth_method' => 'string'
                    ))
                    ->setAllowedValues(array(
                        'auth_method' => array('KEY', 'PASS')
                    ));

                    $parameters = $resolver
                        ->resolve($parameters);

                    switch($parameters['auth_method']) {
                        case 'KEY':
                            $resolver
                                ->setRequired(array('private', 'public', 'passphrase'))
                                ->setAllowedTypes(array(
                                    'private' => 'string',
                                    'public' => 'string',
                                    'passphrase' => 'string'
                                ));
                            break;
                        case 'PASS':
                            $resolver
                                ->setRequired(array('password'))
                                ->setAllowedTypes(array('password' => 'string'));
                            break;
                    }
                break;
            default:
                throw new InvalidOptionsException("Invalid type: $type");
        }

        $parameters = $resolver
            ->resolve($parameters);

        $parameters['type'] = $type;

        return $parameters;
    }

    public static function resolveBrandingOptions(array $parameters)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults(array(
                'primary' => false,
                'corporate_layout_color' => null,
                'corporate_text_color' => null,
                'application_texts' => null,
                'subject_tag' => null,
                'reminders' => null,
                'expire_time' => null,
                'callback_url' => null,
                'signature_pos_x' => null,
                'signature_pos_y' => null,
                'terms_and_conditions_label' => null,
                'terms_and_conditions_body' => null,
                'events_url' => null
            ))
            ->setAllowedTypes(array(
                'primary' => 'bool',
                'corporate_layout_color' => array('null', 'string'),
                'corporate_text_color' => array('null', 'string'),
                'application_texts' => array('null', 'array'),
                'subject_tag' => array('null', 'string'),
                'reminders' => array('null', 'array'),
                'expire_time' => array('null', 'int'),
                'callback_url' => array('null', 'string'),
                'signature_pos_x' => array('null', 'double'),
                'signature_pos_y' => array('null', 'double'),
                'terms_and_conditions_label' => array('null', 'string'),
                'terms_and_conditions_body' => array('null', 'string'),
                'events_url' => array('null', 'string')
            ));

        return $resolver
            ->resolve($parameters);
    }

    public static function resolveCreateSignatureRequestOptions(array $parameters)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults(array(
                'subject' => null,
                'body' => null,
                'in_person_sign' => false,
                'sequential' => false,
                'photo' => false,
                'mandatory_pages' => array(),
                'branding_id' => null
            ))
            ->setAllowedTypes(array(
                'subject' => array('null', 'string'),
                'body' => array('null', 'string'),
                'in_person_sign' => 'bool',
                'sequential' => 'bool',
                'photo' => 'bool',
                'mandatory_pages' => array('null', 'array'),
                'branding_id' => array('null', 'string')
            ));

        return $resolver
            ->resolve($parameters);
    }
}
