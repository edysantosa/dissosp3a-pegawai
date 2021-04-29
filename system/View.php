<?php namespace sys;

use \Psr\Http\Message\ResponseInterface as Response;

class View
{
    private $container ;
    private $response;
    private $twig ;
    private $contentIdentifier ;
    private $js     = [];
    private $css    = [];
    private $params = [] ;
    private $frame  = null;

    public function __construct($path, $settings = [])
    {
        $this->twig = new \Slim\Views\Twig(APPATH . '/view', [
            'cache' => false,
            'debug' => true
        ]);
    }

    public function __get($name)
    {
        return $this->params[$name];
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function __unset($name)
    {
        unset($this->params[$name]);
    }

    public function setContainer($container)
    {
        $this->container = $container;
        $this->setResponse($container->response);
        return $this;
    }

    public function setFrame($frame, $identifier = "content")
    {
        $this->frame = $frame;
        $this->contentIdentifier = $identifier;
        return $this;
    }

    public function setTitle($title)
    {
        $this->params[ '__site_title' ] = $title;
        return $this;
    }

    public function setFavicon($url)
    {
        $this->params[ '__site_favicon' ] = $url;
        return $this;
    }

    public function setBaseUrl($url)
    {
        $this->twig['baseUrl'] = $url;
        return $this;
    }

    public function setTwig($name, $value)
    {
        $this->twig[$name] = $value;
        return $this;
    }

    public function addExtension($extension)
    {
        $this->twig->addExtension($extension);
        return $this;
    }

    public function addJs($js, array $attributes = [], $zindex = 0, $nostamp = false)
    {
        $this->sourceAddition($this->js, $js, $attributes, $zindex, $nostamp);
        return $this;
    }

    public function addCss($css, array $attributes = [], $zindex = 0, $nostamp = false)
    {
        $this->sourceAddition($this->css, $css, $attributes, $zindex, $nostamp);
        return $this;
    }

    private function sourceAddition(array &$container, $source, array $attributes = [], $zindex, $nostamp = false)
    {
        if (is_array($source)) {
            foreach ($source as $data) {
                $temp = [];

                if (!isset($data['src'])) {
                    return;
                }

                $temp[ 'src' ] = $nostamp ? $data['src'] : $this->imbueStamp($data['src']);
                
                if (isset($data['attr'])) {
                    if (is_array($data['attr'])) {
                        $temp['attr'] = $data['attr'];
                    } else {
                        $temp['attr'] = [];
                    }
                }

                $temp[ 'zindex' ] = 0;

                if (isset($data['zindex'])) {
                    $temp[ 'zindex' ] = $zindex;
                }

                $container[] = $temp;
            }
        } else {
            $container[] = [
                'src'   => $nostamp ? $source : $this->imbueStamp($source),
                'attr'  => $attributes
            ];
        }
    }

    private function imbueStamp($src)
    {
        $newsrc = $src;

        if (strpos($newsrc, 'stamp') !== false) {
            return $newsrc;
        }

        $stamp = Sitebase::get('versionstamp', null);
        $versionstamp = is_null($stamp) ? '' : 'stamp=' . $stamp;

        $queries = explode('?', $newsrc);

        if (count($queries) == 1) {
            $queries[1] = '';
        }

        if (strlen($queries[1]) > 0) {
            $queries[1] = '&'. $versionstamp;
        } else {
            $queries[1] = $versionstamp;
        }

        return implode('?', $queries);
    }

    private function getJs()
    {
        $dom = '';

        foreach ($this->js as $js) {
            $attr = '';
            if (count($js['attr']) > 0) {
                foreach ($js['attr'] as $field => $val) {
                    $attr .= $field .'="'. $val .'" ';
                }
            }

            $dom .= '<script src="'. $js['src'] .'" '. $attr .'></script>';
        }

        return $dom;
    }

    private function getCss()
    {
        $dom = '';

        foreach ($this->css as $css) {
            $attr = '';

            if (count($css['attr']) > 0) {
                foreach ($css['attr'] as $field => $val) {
                    $attr .= $field .'="'. $val .'" ';
                }
            }

            $dom .= '<link rel="stylesheet" type="text/css" href="'. $css['src'] .'" '. $attr .' />';
        }

        return $dom;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function render($template, $data = [])
    {
        $this->params['__site_rendered_js'] = $this->getJs();
        $this->params['__site_rendered_css'] = $this->getCss();
        $this->params['__sitebase'] = \sys\Sitebase::getInTwig();
        
        $data = array_merge($this->params, $data);

        //Masukkan session ke twig
        $this->twig->getEnvironment()->addGlobal('session', $_SESSION);

        if (is_null($this->frame)) {
            return $this->twig->render($this->response, $template, $data);
        } else {
            $data[$this->contentIdentifier] = $template;
            return $this->twig->render($this->response, $this->frame, $data);
        }
    }

    public function fetchHtml($template, $data = [])
    {
        return $this->twig->fetch($template, $data);
    }
}
