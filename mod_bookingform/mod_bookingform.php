<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$app = Factory::getApplication();
$doc = Factory::getDocument();

$articleId = $app->input->getCmd('view') === 'article' ? $app->input->getInt('id') : 0;
$view = $app->input->getCmd('view');
$option = $app->input->getCmd('option');

$articleTitle = '';
$pricingRules = null;
$totalAccommodationGuests = 0;
$countries = [];

// Load portal CSS if we are on the component's communication view
if ($option === 'com_bookingmanager' && $view === 'communication') {
    $portalCssPath = JPATH_SITE . '/modules/mod_bookingform/media/css/portal.css';
    if (file_exists($portalCssPath)) {
        // This adds the file's last modified time to the URL, forcing browsers to reload it when it changes.
        $doc->addStyleSheet(Uri::root(true) . '/modules/mod_bookingform/media/css/portal.css?v=' . filemtime($portalCssPath));
    }
}


if ($articleId && $view === 'article') {
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select($db->quoteName(['title', 'catid']))
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('id') . ' = ' . (int)$articleId);
    $article = $db->setQuery($query)->loadObject();
    
    if ($article && $article->catid > 0) {
        $articleTitle = $article->title;
        $pricingRules = ModBookingFormHelper::getPricingDataForArticle($articleId);

        $fieldsQuery = $db->getQuery(true)
            ->select('fv.value')
            ->from($db->quoteName('#__fields_values', 'fv'))
            ->join('INNER', $db->quoteName('#__fields', 'f') . ' ON fv.field_id = f.id')
            ->where('fv.item_id = ' . (int)$articleId)
            ->where('f.name = ' . $db->quote('accommodation-guests'));
        $totalAccommodationGuests = (int)$db->setQuery($fieldsQuery)->loadResult();
    }
}

// Only render the booking form if we have pricing rules for it
if ($pricingRules) {
    if (ComponentHelper::isEnabled('com_bookingmanager'))
    {
        JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
        if (class_exists('BookingmanagerHelper')) {
            $countries = BookingmanagerHelper::getCountries();
        }
    }

    if (empty($countries)) {
        Log::add('Could not load countries from com_bookingmanager. Module will not render correctly.', Log::ERROR, 'mod_bookingform');
        return;
    }

    $cssPath = JPATH_SITE . '/modules/mod_bookingform/media/css/booking-form.css';
    $doc->addStyleSheet(Uri::root(true) . '/modules/mod_bookingform/media/css/booking-form.css?v=' . filemtime($cssPath));
    $doc->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css');
    $doc->addScript('https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js');
    $doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js');
    $jsPath = JPATH_SITE . '/modules/mod_bookingform/media/js/booking-form.js';
    $doc->addScript(Uri::root(true) . '/modules/mod_bookingform/media/js/booking-form.js?v=' . filemtime($jsPath), ['defer' => 'true']);
    $scriptOptions = [
        'totalAccommodationGuests' => $totalAccommodationGuests,
        'pricingRules'   => $pricingRules,
        'currencySymbol' => $params->get('currency_symbol', '€'),
        'submissionUrl'  => Route::_('index.php?option=com_bookingmanager&task=submitBooking&format=json', false),
        'lang' => $langStrings
    ];
    $doc->addScriptOptions('mod_bookingform', $scriptOptions);

    require ModuleHelper::getLayoutPath('mod_bookingform', $params->get('layout', 'default'));
}