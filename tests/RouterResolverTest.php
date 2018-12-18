<?php

use PHPUnit\Framework\TestCase;
use Src\RouterResolver;

require_once __DIR__ . '/../src/RouterResolver.php';

class RouterResolverTest extends TestCase
{
    private $resolver;

    public function setUp()
    {
        $this->resolver = new RouterResolver();
    }

    public function testNonUrlString()
    {
        $url = 'sample-non-url-string';
        $template = '/';

        $this->assertJson($this->resolver->resolve($url, $template));
        $this->assertJsonStringEqualsJsonString($this->jsonEncode([]), $this->resolver->resolve($url, $template));
    }


    public function testHttpHost()
    {
        $url = 'http://www.example.com';
        $template = '/';

        $expectedResult = [
            'scheme'     => 'http',
            'host'       => 'www.example.com',
            'path'       => '/',
            'parameters' => [],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testHttpsHost()
    {
        $url = 'https://www.example.com';
        $template = '/';

        $expectedResult = [
            'scheme'     => 'https',
            'host'       => 'www.example.com',
            'path'       => '/',
            'parameters' => [],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testLanguage()
    {
        $url = 'http://www.example.com/en';
        $template = '/:lang';

        $expectedResult = [
            'scheme'     => 'http',
            'host'       => 'www.example.com',
            'path'       => '/en',
            'parameters' => ['lang' => 'en'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));

        $url = 'http://www.example.com/eng';
        $template = '/:lang';

        $expectedResult = [
            'scheme'     => 'http',
            'host'       => 'www.example.com',
            'path'       => '/eng',
            'parameters' => ['lang' => 'eng'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode([]), $this->resolver->resolve($url, $template));
        $this->assertJsonStringNotEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));

        $url = 'http://www.example.com/1e';
        $template = '/:lang';

        $expectedResult = [
            'scheme'     => 'http',
            'host'       => 'www.example.com',
            'path'       => '/1e',
            'parameters' => ['lang' => '1e'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode([]), $this->resolver->resolve($url, $template));
        $this->assertJsonStringNotEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testProductsList()
    {
        $url = 'http://www.example.com/en/products';
        $template = '/:lang/products';

        $expectedResult = [
            'scheme'     => 'http',
            'host'       => 'www.example.com',
            'path'       => '/en/products',
            'parameters' => ['lang' => 'en'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testProductId()
    {
        $url = 'https://www.example.com/en/products/22';
        $template = '/:lang/products/:id';

        $expectedResult = [
            'scheme'     => 'https',
            'host'       => 'www.example.com',
            'path'       => '/en/products/22',
            'parameters' => ['lang' => 'en', 'id' => '22'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testProductIdCompareId()
    {
        $url = 'https://www.example.com/en/products/22/compare/33';
        $template = '/:lang/products/:id/compare/:compareId';

        $expectedResult = [
            'scheme'     => 'https',
            'host'       => 'www.example.com',
            'path'       => '/en/products/22/compare/33',
            'parameters' => ['lang' => 'en', 'id' => '22', 'compareId' => '33'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testProductImages()
    {
        $url = 'https://www.example.com/en/products/22/images';
        $template = '/:lang/products/:id/images/[/:imageId]';

        $expectedResult = [
            'scheme'     => 'https',
            'host'       => 'www.example.com',
            'path'       => '/en/products/22/images',
            'parameters' => ['lang' => 'en', 'id' => '22'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }


    public function testProductImagesId()
    {
        $url = 'https://www.example.com/en/products/22/images/44';
        $template = '/:lang/products/:id/images/[/:imageId]';

        $expectedResult = [
            'scheme'     => 'https',
            'host'       => 'www.example.com',
            'path'       => '/en/products/22/images/44',
            'parameters' => ['lang' => 'en', 'id' => '22', 'imageId' => '44'],
        ];

        $this->assertJsonStringEqualsJsonString($this->jsonEncode($expectedResult), $this->resolver->resolve($url, $template));
    }

    public function testInvalidParameter()
    {
        $url = 'https://www.example.com/en/random-parameter/22';
        $template = '/:lang/products/:id';

        $this->assertJsonStringEqualsJsonString($this->jsonEncode([]), $this->resolver->resolve($url, $template));

        $url = 'https://www.example.com/en/random-parameter';
        $template = '/:lang/products/:id';

        $this->assertJsonStringEqualsJsonString($this->jsonEncode([]), $this->resolver->resolve($url, $template));
    }

    private function jsonEncode($data)
    {
        return json_encode($data, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
    }
}