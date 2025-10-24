<?php
namespace Bhm\Component\Bookingmanager\Site\Router;

use Joomla\CMS\Component\Router\RouterBase;

class Router extends RouterBase
{
    public function build(&$query)
    {
        $segments = [];
        if (isset($query['view'])) {
            $segments[] = $query['view'];
            unset($query['view']);
        }
        if (isset($query['id'])) {
            $segments[] = $query['id'];
            unset($query['id']);
        }
        return $segments;
    }

    public function parse(&$segments)
    {
        $vars = [];
        if (isset($segments[0])) {
            $vars['view'] = $segments[0];
        }
        if (isset($segments[1])) {
            $vars['id'] = $segments[1];
        }
        return $vars;
    }
}
