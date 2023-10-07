<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\Tests\Service;

use Greenrivers\Bundle\MagentoIntegrationBundle\Service\GraphqlService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GraphqlServiceTest extends TestCase
{
    private GraphqlService $graphqlService;

    private MockHandler $mockHandler;

    private Client $client;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler([]);
        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->client = new Client(['handler' => $handlerStack]);
        $this->graphqlService = new GraphqlService($this->client);
    }

    /**
     * @covers GraphqlService::sendProduct
     */
    public function testSendProduct(): void
    {
        $magentoUrl = 'https://app.magento.test';
        $magentoToken = 'token123';
        $data = [
            'name' => 'Test product',
            'sku' => 'test',
            'attribute_set_id' => 4,
            'price' => 23.99,
            'status' => 1
        ];

        $this->mockHandler->append(
            new Response(
                200,
                [],
                Utils::streamFor(
                    json_encode(
                        [
                            'data' => [
                                'createProduct' => [
                                    'product' => [
                                        'name' => 'Test product',
                                        'sku' => 'test',
                                        'attribute_set_id' => 4,
                                        'price' => 23.99,
                                        'status' => 1
                                    ]
                                ]
                            ]
                        ],
                        JSON_THROW_ON_ERROR
                    )
                )
            )
        );

        $response = $this->graphqlService->sendProduct($magentoUrl, $magentoToken, $data);
        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('product', $result['data']['createProduct']);
        $this->assertCount(5, $result['data']['createProduct']['product']);
        $this->assertEquals('Test product', $result['data']['createProduct']['product']['name']);
        $this->assertEquals('test', $result['data']['createProduct']['product']['sku']);
        $this->assertEquals(4, $result['data']['createProduct']['product']['attribute_set_id']);
        $this->assertEquals(23.99, $result['data']['createProduct']['product']['price']);
        $this->assertEquals(1, $result['data']['createProduct']['product']['status']);
    }

    /**
     * @covers GraphqlService::sendCategory
     */
    public function testSendCategory(): void
    {
        $magentoUrl = 'https://app.magento.test';
        $magentoToken = 'token123';
        $data = [
            'is_active' => 1,
            'include_in_menu' => 1,
            'name' => 'Test category',
            'parent_id' => 2
        ];

        $this->mockHandler->append(
            new Response(
                200,
                [],
                Utils::streamFor(
                    json_encode(
                        [
                            'data' => [
                                'createCategory' => [
                                    'category' => [
                                        'is_active' => 1,
                                        'include_in_menu' => 1,
                                        'name' => 'Test category',
                                        'parent_id' => 2
                                    ]
                                ]
                            ]
                        ],
                        JSON_THROW_ON_ERROR
                    )
                )
            )
        );

        $response = $this->graphqlService->sendCategory($magentoUrl, $magentoToken, $data);
        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('category', $result['data']['createCategory']);
        $this->assertCount(4, $result['data']['createCategory']['category']);
        $this->assertEquals(1, $result['data']['createCategory']['category']['is_active']);
        $this->assertEquals(1, $result['data']['createCategory']['category']['include_in_menu']);
        $this->assertEquals('Test category', $result['data']['createCategory']['category']['name']);
        $this->assertEquals(2, $result['data']['createCategory']['category']['parent_id']);
    }

    /**
     * @covers GraphqlService::processResponse
     */
    public function testProcessResponse(): void
    {
        $response = new Response(
            200,
            [],
            Utils::streamFor(
                json_encode(
                    [
                        'errors' => [
                            [
                                'message' => 'Test error',
                                'path' => 'createProduct'
                            ]
                        ],
                        'data' => [
                            'createProduct' => null
                        ]
                    ],
                    JSON_THROW_ON_ERROR
                )
            )
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error');

        $this->graphqlService->processResponse($response);
    }
}
