<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\NotificationService\ServiceClientBase;
use Zbox\UnifiedPush\Exception\ClientException;
use Zbox\UnifiedPush\Exception\BadMethodCallException;
use Buzz\Browser;
use Buzz\Client\MultiCurl;

/**
 * Class ServiceClient
 * @package Zbox\UnifiedPush\NotificationService\GCM
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
        $client->setVerifyPeer(false);

        $this->serviceClient = new Browser($client);

        return $this;
    }

    /**
     * If you send a notification that is accepted by APNs,
     * nothing is returned. If you send a notification that is malformed
     * or otherwise unintelligible, APNs returns an error-response packet
     *
     * @param string $notification
     * @throws ClientException
     * @return bool
     */
    public function sendNotification($notification)
    {
        try {
            $connection  = $this->getClientConnection();
            $serviceURL  = $this->getServiceURL();
            $credentials = $this->getCredentials();

            $headers[] = 'Authorization: key='.$credentials->getAuthToken();
            $headers[] = 'Content-Type: application/json';

            $response = $connection->post($serviceURL, $headers, $notification);
            $connection->getClient()->flush();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        new Response($response);

        return true;
    }

    /**
     * @throws BadMethodCallException
     */
    public function readFeedback()
    {
        throw new BadMethodCallException("No feedback service available in GCM");
    }
}
