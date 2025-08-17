<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

// Load validation behavior
HTMLHelper::_('behavior.formvalidator');

// Custom script for the rates table
$script = "
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('item-form');

    if (container) {
        // Use event delegation for the checkboxes
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('override-commission-checkbox')) {
                const checkbox = e.target;
                const commissionInput = checkbox.closest('td').nextElementSibling.querySelector('.commission-value-input');
                if (commissionInput) {
                    commissionInput.disabled = !checkbox.checked;
                }
            }
        });

        // Set the initial state for all checkboxes on page load
        document.querySelectorAll('.override-commission-checkbox').forEach(function(checkbox) {
            const commissionInput = checkbox.closest('td').nextElementSibling.querySelector('.commission-value-input');
            if (commissionInput) {
                commissionInput.disabled = !checkbox.checked;
            }
        });
    }
});
";
Factory::getDocument()->addScriptDeclaration($script);
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <fieldset class="form-horizontal">
        <legend>Property Details</legend>
        <?php echo $this->form->renderField('article_id'); ?>
        <?php echo $this->form->renderField('max_guests'); ?>
        <?php echo $this->form->renderField('number_of_units'); ?>
        <?php echo $this->form->renderField('complexes'); ?>
    </fieldset>

    <hr>

    <fieldset class="form-horizontal">
        <legend>Property Rates</legend>
        <?php if (isset($this->item->ratesData) && !empty($this->item->ratesData->seasons) && !empty($this->item->ratesData->markets)) : ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle;">Season</th>
                        <?php foreach ($this->item->ratesData->markets as $market) : ?>
                            <th colspan="3" class="text-center"><?php echo $this->escape($market->market_name); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($this->item->ratesData->markets as $market) : ?>
                            <th>Rate (<?php echo $this->escape($market->currency); ?>)</th>
                            <th>Override Commission</th>
                            <th>Commission %</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->item->ratesData->seasons as $season) :
                        $seasonRates = $this->item->ratesData->rates[$season->name]->rates ?? [];
                    ?>
                    <tr>
                        <td>
                            <?php echo $this->escape($season->name); ?><br/>
                            <small><?php echo HTMLHelper::_('date', $season->start_date, 'd M Y'); ?> to <?php echo HTMLHelper::_('date', $season->end_date, 'd M Y'); ?></small>
                        </td>
                        <?php foreach ($this->item->ratesData->markets as $market) :
                            $marketName = $market->market_name;
                            $marketRateData = $seasonRates[$marketName] ?? [];
                            $rateValue = $marketRateData['rate'] ?? '';
                            $overrideChecked = !empty($marketRateData['override_commission']) ? 'checked' : '';
                            $commissionValue = $marketRateData['commission'] ?? '';
                            $supplierCommission = $season->admin_commission ?? 0;
                        ?>
                            <td>
                                <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][rate]" value="<?php echo $this->escape($rateValue); ?>" class="input-small" />
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][override_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> />
                            </td>
                            <td>
                                <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][commission]" value="<?php echo $this->escape($commissionValue); ?>" class="input-small commission-value-input" />
                                <div class="commission-source-info small" style="color: #666;">
                                    (Default: <?php echo $this->escape($supplierCommission); ?>%)
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($this->item->ratesData) && isset($this->item->ratesData->error)) : ?>
            <div class="alert alert-warning"><?php echo $this->escape($this->item->ratesData->error); ?></div>
        <?php else : ?>
            <div class="alert">No seasons found. Please define seasons for the supplier assigned to this property.</div>
        <?php endif; ?>
    </fieldset>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
