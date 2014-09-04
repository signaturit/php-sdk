<?php

namespace Signaturit;

class Validator
{
    /**
     * @param string|string[] $files
     * @param string|string[] $extensions
     *
     * @throws \RuntimeException
     */
    public static function assertValidFiles($files, $extensions)
    {
        $files = !is_array($files) ?
            array($files) :
            $files;

        $extensions = !is_array($extensions) ?
            array($extensions) :
            $extensions;

        $extensions = implode('|', $extensions);

        foreach ($files as $path) {
            if (!file_exists($path)) {
                throw new \RuntimeException("Can't find file $path");
            }

            if (!preg_match("/\\.($extensions)$/", $path)) {
                throw new \RuntimeException("File $path format is not supported");
            }
        }
    }

    /**
     * @param array $recipients
     *
     * @throws \RuntimeException
     */
    public static function assertValidRecipients(array $recipients)
    {
        foreach ($recipients as $recipient) {
            if (is_array($recipient)) {
                if (!array_key_exists('email', $recipient)) {
                    throw new \RuntimeException("Missing 'email' field in recipient");
                }

                self::assertIsEmail($recipient['email']);

                if (array_key_exists('fullname', $recipient) && !is_string($recipient['fullname'])) {
                    throw new \RuntimeException("Invalid 'fullname' field for recipient {$recipient['email']}");
                }

                if (array_key_exists('phone', $recipient) && !preg_match('/^[0-9]+$/', $recipient['phone'])) {
                    throw new \RuntimeException("Invalid 'phone' field for recipient {$recipient['email']}");
                }
            } else {
                self::assertIsEmail($recipient);
            }
        }
    }

    protected static function assertIsEmail($email)
    {
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("Invalid recipient email address: $email");
        }
    }
} 
