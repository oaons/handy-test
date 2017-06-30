<?php

namespace CoreBundle\Client;

use ApiBundle\Exception\DomainLogicException;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as HttpRequest;
use GuzzleHttp\RequestOptions;
use JMS\DiExtraBundle\Annotation as DI;
use function GuzzleHttp\json_decode;

/**
 * @DI\Service("core.client.handy")
 */
class HandyClient implements ApproveTransactionClientInterface
{
    const HTTP_TIMEOUT = 5;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $approveUri;

    /**
     * @DI\InjectParams({
     *     "httpClient" = @DI\Inject("guzzle.http_client"),
     *     "approveUri" = @DI\Inject("%client.handy.approve_uri%")
     * })
     * @param HttpClientInterface $httpClient
     * @param string $approveUri
     */
    public function __construct(HttpClientInterface $httpClient, string $approveUri)
    {
        $this->httpClient = $httpClient;
        $this->approveUri = $approveUri;
    }

    /**
     * {@inheritdoc}
     */
    public function isTransactionApproved(): bool
    {
        $request = new HttpRequest('GET', $this->approveUri);
        try {
            $response = $this->httpClient->send($request, [RequestOptions::TIMEOUT => self::HTTP_TIMEOUT]);
        } catch (ClientException $exception) {
            throw new DomainLogicException($exception->getMessage());
        }

        $json = json_decode((string) $response->getBody());
        if ('success' !== $json->status) {
            return false;
        }

        return true;
    }
}
