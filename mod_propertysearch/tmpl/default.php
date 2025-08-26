<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

?>
<div id="mod-propertysearch-wrapper" class="mod-propertysearch-wrapper">
    <form id="mod-propertysearch-form" action="index.php?option=com_bookingmanager&view=searchresults" method="get" class="form-inline">

        <div class="search-field-group" id="search-destination-group">
            <label for="main_region_id" class="form-label">Destination</label>
            <select name="main_region_id" id="main_region_id" class="form-select">
                <option value="">Select Main Region</option>
                <?php foreach ($mainRegions as $region) : ?>
                    <option value="<?php echo $region->id; ?>">
                        <?php echo htmlspecialchars($region->name, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div id="sub-regions-container" class="sub-regions-container">
                <!-- Sub-regions will be dynamically inserted here -->
            </div>
        </div>

        <div class="search-field-group" id="search-dates-group">
            <label for="date-range-picker" class="form-label">Check-in - Check-out</label>
            <input id="date-range-picker" type="text" name="dates" class="form-control" placeholder="Select your dates">
        </div>

        <div class="search-field-group" id="search-occupancy-group">
            <label for="adults" class="form-label">Guests</label>
            <div class="occupancy-inputs">
                <input type="number" name="adults" id="adults" class="form-control" min="1" value="2" title="Adults">
                <input type="number" name="children" id="children" class="form-control" min="0" value="0" title="Children">
            </div>
        </div>

        <div class="search-field-group" id="search-button-group">
            <button type="submit" class="btn btn-primary">
                <span class="icon-search" aria-hidden="true"></span>
                Search
            </button>
        </div>

        <input type="hidden" name="sub_region_ids" id="sub_region_ids" value="">
        <input type="hidden" name="option" value="com_bookingmanager">
        <input type="hidden" name="view" value="searchresults">
    </form>
</div>
