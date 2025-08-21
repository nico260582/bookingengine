<?php defined('_JEXEC') or die; ?>
<form action="index.php?option=com_bookingmanager&view=searchresults" method="get">
    <div class="form-group">
        <label for="main_region_id">Main Region</label>
        <select name="main_region_id" id="main_region_id" class="form-control">
            <option value="">All Regions</option>
            <?php foreach ($mainRegions as $region) : ?>
                <option value="<?php echo (int) $region->id; ?>">
                    <?php echo htmlspecialchars($region->name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="guests">Guests</label>
        <input type="number" name="guests" id="guests" class="form-control" min="1" value="2">
    </div>
    <div class="form-group">
        <label for="date-range-picker">Check-in / Check-out</label>
        <input id="date-range-picker" type="text" name="dates" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <input type="hidden" name="option" value="com_bookingmanager">
    <input type="hidden" name="view" value="searchresults">
</form>
