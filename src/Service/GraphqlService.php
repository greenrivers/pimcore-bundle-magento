<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class GraphqlService
{
    /**
     * GraphqlService constructor.
     * @param Client $client
     */
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @param string $magentoUrl
     * @param string $magentoToken
     * @param array $data
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws JsonException
     */
    public function sendProduct(string $magentoUrl, string $magentoToken, array $data): ResponseInterface
    {
        $body = [
            'query' => 'mutation ($status: Boolean, $attributeSetId: Int, $name: String, $sku: String, $price: Float) {
                            createProduct(
                                input:{
                                    status: $status,
                                    attribute_set_id: $attributeSetId,
                                    name: $name,
                                    sku: $sku,
                                    price: $price
                                }
                            ) {
                                product {
                                    status
                                    attribute_set_id
                                    name
                                    sku
                                    price
                                }
                            }
                        }',
            'variables' => $data
        ];

        return $this->makeRequest($magentoUrl, $magentoToken, $body);
    }

    /**
     * @param string $magentoUrl
     * @param string $magentoToken
     * @param array $data
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws JsonException
     */
    public function sendCategory(string $magentoUrl, string $magentoToken, array $data): ResponseInterface
    {
        $body = [
            'query' => 'mutation ($isActive: Boolean, $includeInMenu: Boolean, $name: String, $parentCategoryId: Int) {
                            createCategory(
                                input:{
                                    is_active: $isActive,
                                    include_in_menu: $includeInMenu,
                                    name: $name,
                                    parent_id: $parentCategoryId
                                }
                            ) {
                                category {
                                    is_active
                                    include_in_menu
                                    name
                                    parent_id
                                }
                            }
                        }',
            'variables' => $data
        ];

        return $this->makeRequest($magentoUrl, $magentoToken, $body);
    }

    /**
     * @param ResponseInterface $response
     * @return void
     * @throws JsonException
     * @throws RuntimeException
     */
    public function processResponse(ResponseInterface $response): void
    {
        $contents = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('errors', $contents)) {
            $message = $contents['errors'][0]['message'];

            if (
                array_key_exists('extensions', $contents['errors'][0]) &&
                array_key_exists('debugMessage', $contents['errors'][0]['extensions'])
            ) {
                $message .= ' => ' . $contents['errors'][0]['extensions']['debugMessage'];
            }

            throw new RuntimeException($message);
        }
    }

    /**
     * @param string $magentoUrl
     * @param string $magentoToken
     * @param array $body
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws JsonException
     */
    private function makeRequest(string $magentoUrl, string $magentoToken, array $body): ResponseInterface
    {
        return $this->client->request(Request::METHOD_POST, $magentoUrl . 'graphql', [
            'headers' => [
                'Authorization' => 'Bearer ' . $magentoToken,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR)
        ]);
    }
}
