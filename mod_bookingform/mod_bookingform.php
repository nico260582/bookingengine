<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
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

    // Load language strings and pass them to JS
    $langStrings = [
        'infant_cot_notice' => Text::_('MOD_BOOKINGFORM_INFANT_COT_NOTICE'),
        'teen_as_adult_notice' => Text::_('MOD_BOOKINGFORM_TEEN_AS_ADULT_NOTICE'),
        'child_supplement_payable' => Text::_('MOD_BOOKINGFORM_CHILD_SUPPLEMENT_PAYABLE'),
        'child_stay_free' => Text::_('MOD_BOOKINGFORM_CHILD_STAY_FREE'),
        'child_supplement_may_apply' => Text::_('MOD_BOOKINGFORM_CHILD_SUPPLEMENT_MAY_APPLY'),
        'min_stay_error' => Text::_('MOD_BOOKINGFORM_MIN_STAY_ERROR'),
        'date_range_error' => Text::_('MOD_BOOKINGFORM_DATE_RANGE_ERROR'),
        'child_ages_error' => Text::_('MOD_BOOKINGFORM_CHILD_AGES_ERROR'),
        'price_estimate_label' => Text::_('MOD_BOOKINGFORM_PRICE_ESTIMATE_LABEL'),
        'price_na' => Text::_('MOD_BOOKINGFORM_PRICE_NA'),
        'nights_count_label' => Text::_('MOD_BOOKINGFORM_NIGHTS_COUNT_LABEL'),
        'night_singular' => Text::_('MOD_BOOKINGFORM_NIGHT_SINGULAR'),
        'night_plural' => Text::_('MOD_BOOKINGFORM_NIGHT_PLURAL'),
        'unit_count_plural' => Text::_('MOD_BOOKINGFORM_UNIT_COUNT_PLURAL'),
        'unit_count_singular' => Text::_('MOD_BOOKINGFORM_UNIT_COUNT_SINGULAR'),
        'coupon_invalid' => Text::_('MOD_BOOKINGFORM_COUPON_INVALID'),
        'coupon_error' => Text::_('MOD_BOOKINGFORM_COUPON_ERROR'),
        'submit_sending' => Text::_('MOD_BOOKINGFORM_SUBMIT_SENDING'),
        'submit_button_text' => Text::_('MOD_BOOKINGFORM_SUBMIT_BUTTON_TEXT'),
        'submit_error_generic' => Text::_('MOD_BOOKINGFORM_SUBMIT_ERROR_GENERIC'),
        'submit_error_try_again' => Text::_('MOD_BOOKINGFORM_SUBMIT_ERROR_TRY_AGAIN'),
        'submit_error_network' => Text::_('MOD_BOOKINGFORM_SUBMIT_ERROR_NETWORK'),
        'recalculate_button_text' => Text::_('MOD_BOOKINGFORM_RECALCULATE_BUTTON_TEXT'),
    ];

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