<?php
defined('_JEXEC') or die;

function BookingmanagerBuildRoute(&$query)
{
    $segments = array();
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

function BookingmanagerParseRoute($segments)
{
    $vars = array();
    if (isset($segments[0])) {
        $vars['view'] = $segments[0];
    }
    if (isset($segments[1])) {
        $vars['id'] = $segments[1];
    }
    return $vars;
}
