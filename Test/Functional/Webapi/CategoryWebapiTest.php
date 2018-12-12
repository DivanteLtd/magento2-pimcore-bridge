<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Functional\Webapi;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class CategoryWebapiTest
 */
class CategoryWebapiTest extends WebapiAbstract
{
    public function testInsertUpdateRoute()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/bridge/catalog/category/add',
                'httpMethod'   => Request::HTTP_METHOD_PUT,
            ],
        ];
        $requestData = [
            'data' => [
                'category_id'   => 10,
                'store_view_id' => 1,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(200, $response[0]['code']);
        $this->assertEquals('Accepted', $response[0]['status']);
        $this->assertEquals('Category 10 has been added to queue', $response[0]['message']);
    }

    public function testDeleteActionRoute()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/bridge/catalog/category/delete/10',
                'httpMethod'   => Request::HTTP_METHOD_DELETE,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, []);
        $this->assertEquals(200, $response[0]['code']);
    }
}
