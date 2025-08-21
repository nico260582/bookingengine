<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

$mainRegions = ModPropertysearchHelper::getMainRegions();

$doc = Factory::getDocument();
$doc->addScript('https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js');
$doc->addStyleSheet('https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css');

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    new Litepicker({
        element: document.getElementById('date-range-picker'),
        singleMode: false,
        tooltipText: {
            one: 'night',
            other: 'nights'
        },
        format: 'YYYY-MM-DD'
    });
});
JS;

$doc->addScriptDeclaration($js);

require ModuleHelper::getLayoutPath('mod_propertysearch', $params->get('layout', 'default'));
