<?php
namespace Bhm\Component\Bookingmanager\Component\Router;

defined('_JEXEC') or die;

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

        if (count($segments) > 0) {
            $vars['view'] = $segments[0];
        }

        if (count($segments) > 1) {
            $vars['id'] = $segments[1];
        }

        return $vars;
    }
}
