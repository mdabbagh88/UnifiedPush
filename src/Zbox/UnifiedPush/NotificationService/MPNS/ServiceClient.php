<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\MPNS;

use Zbox\UnifiedPush\NotificationService\ServiceClientBase;
use Zbox\UnifiedPush\Exception\ClientException;
use Zbox\UnifiedPush\Exception\BadMethodCallException;
use Buzz\Browser;
use Buzz\Client\MultiCurl;

/**
 * Class ServiceClient
 * @package Zbox\UnifiedPush\NotificationService\MPNS
 */
class ServiceClient extends ServiceClientBase
{
    /**
     * Initializing HTTP client
     *
     * @return $this
     */
    protected function createClient()
    {
        $client = MultiCurl();

        $credentials      = $this->getCredentials();
        $isAuthenticated  = $credentials->isAuthenticated();

        $client->setVerifyPeer($isAuthenticated);

        if ($isAuthenticated) {
            $client->setOption(CURLOPT_SSLCERT, $credentials->getCertificatePassPhrase());
            $client->setOption(CURLOPT_SSLCERTPASSWD, $credentials->getCertificatePassPhrase());
        }

        $this->serviceClient = new Browser($client);

        return $this;
    }

    /**
     * @param array $notification
     * @throws ClientException
     * @return bool
     */
    public function sendNotification($notification)
    {
        try {
            $connection  = $this->getClientConnection();
            $serviceURL  = $this->getServiceURL();
            $serviceURL['host'] = str_replace('[TOKEN]', $notification['recipient'], $serviceURL['host']);

            $headers[] = 'Accept: application/*';
            $headers[] = 'Content-Type: text/xml';

            foreach ($notification['options'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }

            $response = $connection->post($serviceURL, $headers, $notification['body']);
            $connection->getClient()->flush();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        new Response($response);

        return true;
    }

    /**
     * No feedback service available in MPNS
     *
     * @throws BadMethodCallException
     */
    public function readFeedback()
    {
        throw new BadMethodCallException("No feedback service available in MPNS");
    }
}