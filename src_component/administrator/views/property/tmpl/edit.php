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
    document.querySelectorAll('.override-commission-checkbox').forEach(function(checkbox) {
        var commissionInput = checkbox.closest('tr').querySelector('.commission-value-input');

        function toggleCommissionInput() {
            commissionInput.disabled = !checkbox.checked;
        }

        checkbox.addEventListener('change', toggleCommissionInput);
        toggleCommissionInput(); // Initial state
    });
});
";
Factory::getDocument()->addScriptDeclaration($script);
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <fieldset class="form-horizontal">
        <legend>Property Details</legend>
        <?php echo $this->form->renderField('article_id'); ?>
        <?php echo $this->form->renderField('max_guests'); ?>
        <?php echo $this->form->renderField('complex_id'); ?>
    </fieldset>

    <hr>

    <fieldset class="form-horizontal">
        <legend>Property Rates</legend>
        <?php if (isset($this->item->ratesData) && !empty($this->item->ratesData->seasons)) : ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Base Rate (per night)</th>
                        <th class="nowrap">Override Admin Commission?</th>
                        <th>Admin Commission %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->item->ratesData->seasons as $season) :
                        $rate = $this->item->ratesData->rates[$season->name] ?? null;
                        $rateValue = ($rate && isset($rate->base_rate)) ? $rate->base_rate : '';
                        $overrideChecked = ($rate && isset($rate->override_admin_commission) && $rate->override_admin_commission) ? 'checked' : '';
                        $commissionValue = ($rate && isset($rate->admin_commission)) ? $rate->admin_commission : '';
                        $supplierCommission = $season->admin_commission ?? 0;
                    ?>
                    <tr>
                        <td><?php echo $this->escape($season->name); ?><br/><small><?php echo $season->start_date . ' to ' . $season->end_date; ?></small></td>
                        <td><input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][base_rate]" value="<?php echo $this->escape($rateValue); ?>" class="input-small" /></td>
                        <td><input type="checkbox" name="jform[rates][<?php echo $this->escape($season->name); ?>][override_admin_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> /></td>
                        <td>
                            <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][admin_commission]" value="<?php echo $this->escape($commissionValue); ?>" class="input-small commission-value-input" />
                            <div class="commission-source-info" style="font-size: 0.9em; color: #666;">
                                <?php if ($overrideChecked) : ?>
                                    <span style="color: green;">Property Override</span>
                                <?php else : ?>
                                    Inherited from Supplier (<?php echo $this->escape($supplierCommission); ?>%)
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($this->item->ratesData) && isset($this->item->ratesData->error)) : ?>
            <div class="alert alert-warning"><?php echo $this->item->ratesData->error; ?></div>
        <?php else : ?>
            <?php
            // The model failed to load seasons. As a fallback, directly load rates from the DB based on article ID.
            $db = Factory::getDbo();
            $articleId = $this->item->article_id;
            $rates = [];
            if ($articleId) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__bookingmanager_rates'))
                    ->where($db->quoteName('property_id') . ' = ' . (int)$articleId);
                $rates = $db->setQuery($query)->loadObjectList();
            }

            if (!empty($rates)) :
            ?>
                <div class="alert alert-info">Note: The supplier for this property does not have seasons defined. Displaying manually saved rates.</div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Season</th>
                            <th>Base Rate (per night)</th>
                            <th class="nowrap">Override Admin Commission?</th>
                            <th>Admin Commission %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rates as $rate) :
                            $rateValue = isset($rate->base_rate) ? $rate->base_rate : '';
                            $overrideChecked = (isset($rate->override_admin_commission) && $rate->override_admin_commission) ? 'checked' : '';
                            $commissionValue = isset($rate->admin_commission) ? $rate->admin_commission : '';
                        ?>
                        <tr>
                            <td><?php echo $this->escape($rate->season_name); ?></td>
                            <td><input type="number" step="0.01" name="jform[rates][<?php echo $this->escape(trim($rate->season_name)); ?>][base_rate]" value="<?php echo $this->escape($rateValue); ?>" class="input-small" /></td>
                            <td><input type="checkbox" name="jform[rates][<?php echo $this->escape(trim($rate->season_name)); ?>][override_admin_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> /></td>
                            <td>
                                <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape(trim($rate->season_name)); ?>][admin_commission]" value="<?php echo $this->escape($commissionValue); ?>" class="input-small commission-value-input" />
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="alert">No seasons found. Please define seasons for the supplier assigned to this property.</div>
            <?php endif; ?>
        <?php endif; ?>
    </fieldset>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
