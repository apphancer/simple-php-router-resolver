<?php

class RouterResolver
{
    private $templateOptions = [
        '/:lang'                                 => '([a-z]{2})',
        '/:lang/products'                        => '([a-z]{2})\/(products)',
        '/:lang/products/:id'                    => '([a-z]{2})\/(products)\/([0-9]+)',
        '/:lang/products/:id/compare/:compareId' => '([a-z]{2})\/(products)\/([0-9]+)\/(compare)\/([0-9]+)',
        '/:lang/products/:id/images/[/:imageId]' => '([a-z]{2})\/(products)\/([0-9]+)\/(images)\/?([0-9]+)?',
    ];

    private $validActions = [
        'products' => 'id',
        'compare'  => 'compareId',
        'images'   => 'imageId',
    ];

    private $langRouteDepth = 1;
    private $action1RouteDepth = 2;
    private $action2RouteDepth = 4;

    private $url;

    public function resolve($url, $template)
    {
        $data = [];

        $this->setUrl($url);
        $path = $this->getPath();
        $parameters = $this->getParameters($path, $template);

        if ($this->isValidUrl($path, $parameters))
        {
            $data = [
                'scheme'     => $this->getScheme(),
                'host'       => $this->getHost(),
                'path'       => $path,
                'parameters' => $parameters,
            ];
        }

        return $this->buildOutput($data);
    }

    private function isValidUrl($path, $parameters)
    {
        if (!parse_url($this->getUrl(), PHP_URL_SCHEME))
        {
            return false;
        }

        if (empty($parameters) && $path !== '/')
        {
            return false;
        }

        return true;
    }

    private function templateHasPattern($template)
    {
        return isset($this->templateOptions[$template]);
    }

    private function getTemplatePattern($template)
    {
        return '/^\/' . $this->templateOptions[$template] . '$/';
    }

    private function getScheme()
    {
        return parse_url($this->getUrl(), PHP_URL_SCHEME);
    }

    private function getHost()
    {
        return parse_url($this->getUrl(), PHP_URL_HOST);
    }

    private function getPath()
    {
        return parse_url($this->getUrl(), PHP_URL_PATH) ?: '/';
    }

    private function getParameters($path, $template)
    {
        $parameters = [];
        $urlLevels = [];

        if ($this->templateHasPattern($template))
        {
            preg_match($this->getTemplatePattern($template), $path, $urlLevels);
        }

        $this->language($urlLevels, $parameters);

        if (\count($urlLevels) > $this->action1RouteDepth)
        {
            $this->action($urlLevels, $this->action1RouteDepth, $parameters);
        }

        if (\count($urlLevels) > $this->action2RouteDepth)
        {
            $this->action($urlLevels, $this->action2RouteDepth, $parameters);
        }

        return $parameters;
    }

    private function language($matches, &$parameters)
    {
        if (!isset($matches[$this->langRouteDepth]))
        {
            return;
        }

        $parameters['lang'] = $matches[$this->langRouteDepth];
    }

    private function action($matches, $depth, &$parameters)
    {
        if (!isset($matches[$depth], $matches[$depth + 1]))
        {
            return;
        }

        $parameters[$this->validActions[$matches[$depth]]] = $matches[$depth + 1];
    }

    private function buildOutput($result)
    {
        return json_encode($result, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
}


$resolver = new RouterResolver();

if (true)
{
    $url = 'non-url-string';
    $template = '/';
    var_dump($resolver->resolve($url, $template));
}
if (true)
{
    $url = 'http://www.apphancer.com';
    $template = '/';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'https://www.apphancer.com';
    $template = '/';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'http://www.apphancer.com/en';
    $template = '/:lang';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'http://www.apphancer.com/en/products';
    $template = '/:lang/products';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'https://www.apphancer.com/en/products/22';
    $template = '/:lang/products/:id';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'https://www.apphancer.com/en/products/22/compare/33';
    $template = '/:lang/products/:id/compare/:compareId';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'https://www.apphancer.com/en/products/22/images';
    $template = '/:lang/products/:id/images/[/:imageId]';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'https://www.apphancer.com/en/products/22/images/33';
    $template = '/:lang/products/:id/images/[/:imageId]';
    var_dump($resolver->resolve($url, $template));
}

if (true)
{
    $url = 'https://www.apphancer.com/en/merdaccia/22';
    $template = '/:lang/products/:id';
    var_dump($resolver->resolve($url, $template));
}

/*

/
/:lang
/:lang/products
/:lang/products/:id
/:lang/products/:id/compare/:compareId
/:lang/products/:id/images/[/:imageId]
*/

/*
/
{"path":"/", "parameters":{}}

/:lang/products
{"path":"/de/products", lang":"de"}

.. see screenshots
*/

/*
fscanf($handle, "%s", $str);
//$output = $str;
$output = json_encode($result, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);

sprintf("%d\n", 'test');


fwrite(STDOUT, $output);
*/

/*
$ho = fopen('php://stdout', "w");
fwrite($ho, sprintf("%d\n", $n));
fclose($ho);
*/