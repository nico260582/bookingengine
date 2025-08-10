document.addEventListener('DOMContentLoaded', function() {
    const options = Joomla.getOptions('com_bookingmanager');
    let pricingRules = null;

    // Original booking state
    let originalAdults = 0;
    let originalChildren = 0;
    let originalStartDate = '';
    let originalEndDate = '';

    // Current booking state for calculation
    let currentAdults = 0;
    let currentChildren = 0;
    let currentStartDate = '';
    let currentEndDate = '';

    let numberOfNights = 0;
    let seasonRateCounts = {};

    const elements = {
        adultsInput: document.getElementById('adults_modifier'),
        childrenInput: document.getElementById('children_modifier'),
        childAgesContainer: document.getElementById('child-ages-portal-container'),
        datePickerInput: document.getElementById('date_modifier'),
        priceDisplay: document.getElementById('price-estimate-display-portal'),
        unitDisplay: document.getElementById('unit-count-display-portal'),
        summaryDisplay: document.getElementById('modification-summary'),
        saveButton: document.getElementById('save-changes-btn')
    };

    function init() {
        if (!options || !options.booking_id) return;
        logPageView();
        fetchPricingRules();
        convertDatesToLocalTime();

        if (elements.adultsInput) {
            originalAdults = currentAdults = parseInt(elements.adultsInput.value, 10);
            originalChildren = currentChildren = parseInt(elements.childrenInput.value, 10);
            originalStartDate = currentStartDate = options.start_date;
            originalEndDate = currentEndDate = options.end_date;

            elements.adultsInput.addEventListener('input', handleModification);
            elements.childrenInput.addEventListener('input', updateChildAgeInputs);
            elements.saveButton.addEventListener('click', saveChanges);
        }
    }

    function fetchPricingRules() {
        fetch(options.urls.getPricing)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    pricingRules = data.pricingRules;
                    initializeDatePicker();
                    calculateNightsAndSeasons();
                    updateChildAgeInputs();
                } else {
                    console.error('Failed to fetch pricing rules:', data.message);
                }
            })
            .catch(error => console.error('Error fetching pricing rules:', error));
    }

    function initializeDatePicker() {
        if (!elements.datePickerInput) return;
        new Litepicker({
            element: elements.datePickerInput,
            singleMode: false,
            minDate: new Date(),
            startDate: new Date(originalStartDate),
            endDate: new Date(originalEndDate),
            format: 'DD MMM, YYYY',
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    if (date1 && date2) {
                        currentStartDate = date1.format('YYYY-MM-DD');
                        currentEndDate = date2.format('YYYY-MM-DD');
                        calculateNightsAndSeasons();
                        handleModification();
                    }
                });
            }
        });
    }

    function getSeasonForDate(date) {
        if (!pricingRules || !Array.isArray(pricingRules.seasons)) return null;
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;
        for (const season of pricingRules.seasons) {
            if (dateStr >= season.start_date && dateStr <= season.end_date) return season;
        }
        return null;
    }

    function calculateNightsAndSeasons() {
        if (!currentStartDate || !currentEndDate) return;
        seasonRateCounts = {};
        const date1 = new Date(currentStartDate);
        const date2 = new Date(currentEndDate);
        let currentDate = new Date(date1);
        while (currentDate < date2) {
            const season = getSeasonForDate(currentDate);
            if (season) { seasonRateCounts[season.name] = (seasonRateCounts[season.name] || 0) + 1; }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        numberOfNights = Object.values(seasonRateCounts).reduce((a, b) => a + b, 0);
    }

    function updateChildAgeInputs() {
        const childrenCount = parseInt(elements.childrenInput.value, 10);
        elements.childAgesContainer.innerHTML = '';
        if (childrenCount > 0) {
            let label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = 'Age of Children';
            elements.childAgesContainer.appendChild(label);
            for (let i = 1; i <= childrenCount; i++) {
                const input = document.createElement('input');
                input.type = 'number';
                input.min = '0';
                input.max = '17';
                input.name = `child_ages[]`;
                input.className = 'form-control child-age-input';
                input.placeholder = `Child ${i} Age`;
                input.required = true;
                input.addEventListener('input', handleModification);
                elements.childAgesContainer.appendChild(input);
            }
        }
        handleModification();
    }

    function handleModification() {
        if (!pricingRules) return;
        currentAdults = parseInt(elements.adultsInput.value, 10);
        currentChildren = parseInt(elements.childrenInput.value, 10);
        updateCalculations();
    }

    function updateCalculations() {
        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));
        let totalGuestsForCapacity = currentAdults;
        let requiredUnits = 1;
        const baseCapacity = options.totalAccommodationGuests || 2;
        let infantsNotCounting = childAges.filter(age => age <= (pricingRules.infant_max_age || 5)).length;
        totalGuestsForCapacity += (childAges.length - infantsNotCounting);
        if (baseCapacity > 0) {
            requiredUnits = Math.max(1, Math.ceil(totalGuestsForCapacity / baseCapacity));
        }

        let roomCost = 0;
        for (const [seasonName, nightsInSeason] of Object.entries(seasonRateCounts)) {
            roomCost += (pricingRules.rates[seasonName] || 0) * nightsInSeason;
        }
        let totalCost = roomCost * requiredUnits;

        const formattedPrice = totalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        elements.unitDisplay.textContent = `${requiredUnits} Unit${requiredUnits > 1 ? 's' : ''}`;
        elements.priceDisplay.textContent = `New Est. Price: ${options.currencySymbol}${formattedPrice}`;

        const hasChanged = (currentAdults !== originalAdults || currentChildren !== originalChildren || currentStartDate !== originalStartDate || currentEndDate !== originalEndDate);
        if (hasChanged) {
            elements.summaryDisplay.innerHTML = `You are requesting changes to your booking. Please review and confirm.`;
            elements.summaryDisplay.style.display = 'block';
            elements.saveButton.style.display = 'inline-block';
        } else {
            elements.summaryDisplay.style.display = 'none';
            elements.saveButton.style.display = 'none';
        }
    }

    function saveChanges() {
        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));
        const formData = new FormData();
        formData.append('booking_id', options.booking_id);
        formData.append('adults', currentAdults);
        formData.append('children', currentChildren);
        formData.append('child_ages', childAges.join(', '));
        formData.append('start_date', currentStartDate);
        formData.append('end_date', currentEndDate);
        formData.append('price_estimate', elements.priceDisplay.textContent.replace('New Est. Price: ', ''));
        formData.append('unit_count', elements.unitDisplay.textContent.charAt(0));
        formData.append(options.token, 1);

        elements.saveButton.disabled = true;
        elements.saveButton.textContent = 'Saving...';

        fetch(options.urls.updateBooking, { method: 'POST', body: new URLSearchParams(formData) })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                    elements.saveButton.disabled = false;
                    elements.saveButton.textContent = 'Save Changes & Notify Admin';
                }
            })
            .catch(error => {
                alert('An error occurred while saving.');
                console.error('Save error:', error);
                elements.saveButton.disabled = false;
                elements.saveButton.textContent = 'Save Changes & Notify Admin';
            });
    }

    function logPageView() {
        if (!options || !options.booking_id) return;
        const formData = new FormData();
        formData.append('booking_id', options.booking_id);
        formData.append('action_type', 'Viewed Portal');
        formData.append('screen_size', `${window.screen.width}x${window.screen.height}`);
        formData.append(options.token, 1);

        fetch(options.urls.logActivity, {
            method: 'POST',
            body: new URLSearchParams(formData),
            keepalive: true // a rough equivalent for sendBeacon's purpose
        }).catch(error => console.error('Error logging page view:', error));
    }

    function convertDatesToLocalTime() {
        const dateElements = document.querySelectorAll('[data-utc-date]');
        dateElements.forEach(function(element) {
            const utcDate = element.getAttribute('data-utc-date');
            if (utcDate) {
                const date = new Date(utcDate);
                const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
                element.textContent = date.toLocaleString(undefined, options);
            }
        });
    }

    init();
});
