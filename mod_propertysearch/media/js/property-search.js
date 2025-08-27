document.addEventListener('DOMContentLoaded', function() {
    const datePickerEl = document.getElementById('date-range-picker');
    const adultsField = document.getElementById('adults');
    const childrenField = document.getElementById('children');
    const mainRegionSelect = document.getElementById('main_region_id');
    const subRegionsContainer = document.getElementById('sub-regions-container');
    const hiddenSubRegionIds = document.getElementById('sub_region_ids');

    if (!datePickerEl || !adultsField || !childrenField || !mainRegionSelect || !subRegionsContainer) {
        return;
    }

    let availabilityData = {
        main_regions: [],
        sub_regions: []
    };

    const litepicker = new Litepicker({
        element: datePickerEl,
        singleMode: false,
        tooltipText: { one: 'night', other: 'nights' },
        format: 'YYYY-MM-DD',
        onSelect: function(date1, date2) {
            fetchAvailability();
        }
    });

    function fetchAvailability() {
        const startDate = litepicker.getStartDate()?.format('YYYY-MM-DD');
        const endDate = litepicker.getEndDate()?.format('YYYY-MM-DD');
        const adults = adultsField.value;
        const children = childrenField.value;
        const guests = parseInt(adults, 10) + parseInt(children, 10);

        if (!startDate || !endDate || guests < 1) {
            return;
        }

        const url = `index.php?option=com_bookingmanager&task=ajax.getRegionAvailability&format=json&start_date=${startDate}&end_date=${endDate}&guests=${guests}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availabilityData = data.data;
                    updateMainRegionSelect();
                    updateSubRegionDisplay();
                }
            });
    }

    function updateMainRegionSelect() {
        const originalValue = mainRegionSelect.value;
        // Clear existing options but keep the first one ("Select Main Region")
        while (mainRegionSelect.options.length > 1) {
            mainRegionSelect.remove(1);
        }

        availabilityData.main_regions.forEach(region => {
            const option = new Option(`${region.name} (${region.count})`, region.id);
            mainRegionSelect.add(option);
        });

        mainRegionSelect.value = originalValue;
    }

    function updateSubRegionDisplay() {
        const selectedMainRegionId = mainRegionSelect.value;
        subRegionsContainer.innerHTML = ''; // Clear current sub-regions

        const relevantSubRegions = availabilityData.sub_regions.filter(sr => sr.main_region_id == selectedMainRegionId);

        relevantSubRegions.forEach(subRegion => {
            const item = document.createElement('div');
            item.className = 'sub-region-item';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'sub-region-checkbox';
            checkbox.value = subRegion.id;
            checkbox.id = `sub-region-${subRegion.id}`;

            const label = document.createElement('label');
            label.htmlFor = checkbox.id;
            label.textContent = `${subRegion.name} (${subRegion.count})`;

            item.appendChild(checkbox);
            item.appendChild(label);
            subRegionsContainer.appendChild(item);
        });
        updateHiddenSubRegionField();
    }

    function updateHiddenSubRegionField() {
        const selectedCheckboxes = subRegionsContainer.querySelectorAll('.sub-region-checkbox:checked');
        const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
        hiddenSubRegionIds.value = ids.join(',');
    }

    // Event Listeners
    adultsField.addEventListener('change', fetchAvailability);
    childrenField.addEventListener('change', fetchAvailability);
    mainRegionSelect.addEventListener('change', updateSubRegionDisplay);
    subRegionsContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('sub-region-checkbox')) {
            updateHiddenSubRegionField();
        }
    });
});
