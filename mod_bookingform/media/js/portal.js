document.addEventListener('DOMContentLoaded', function() {
    const options = Joomla.getOptions('com_bookingmanager');
    let pricingRules = null;
    let originalAdults = 0;
    let originalChildren = 0;
    let numberOfNights = 0;
    let seasonRateCounts = {};

    const elements = {
        adultsInput: document.getElementById('adults_modifier'),
        childrenInput: document.getElementById('children_modifier'),
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
        calculateNightsAndSeasons();

        if (elements.adultsInput) {
            originalAdults = parseInt(elements.adultsInput.value, 10);
            originalChildren = parseInt(elements.childrenInput.value, 10);
            elements.adultsInput.addEventListener('input', handleModification);
            elements.childrenInput.addEventListener('input', handleModification);
            elements.saveButton.addEventListener('click', saveChanges);
        }
    }

    function fetchPricingRules() {
        const url = `${options.baseUrl}index.php?option=com_bookingmanager&task=getPricingForRequest&booking_id=${options.booking_id}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    pricingRules = data.pricingRules;
                } else {
                    console.error('Failed to fetch pricing rules:', data.message);
                }
            })
            .catch(error => console.error('Error fetching pricing rules:', error));
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
        if (!options.start_date || !options.end_date) return;
        const date1 = new Date(options.start_date);
        const date2 = new Date(options.end_date);

        let currentDate = date1;
        while (currentDate < date2) {
            const season = getSeasonForDate(currentDate);
            if (season) {
                seasonRateCounts[season.name] = (seasonRateCounts[season.name] || 0) + 1;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        numberOfNights = Object.values(seasonRateCounts).reduce((a, b) => a + b, 0);
    }

    function handleModification() {
        if (!pricingRules) return;
        updateCalculations();
    }

    function updateCalculations() {
        const adults = parseInt(elements.adultsInput.value, 10);
        const children = parseInt(elements.childrenInput.value, 10);
        const newGuestCount = adults + children;

        let totalGuestsForCapacity = adults + children; // Simplified for portal
        let requiredUnits = 1;
        const baseCapacity = options.totalAccommodationGuests || 2;
        if (baseCapacity > 0) {
            requiredUnits = Math.max(1, Math.ceil(totalGuestsForCapacity / baseCapacity));
        }

        let roomCost = 0;
        for (const [seasonName, nightsInSeason] of Object.entries(seasonRateCounts)) {
            let nightlyRate = pricingRules.rates[seasonName] || 0;
            roomCost += nightlyRate * nightsInSeason;
        }
        let totalCost = roomCost * requiredUnits;

        const formattedPrice = totalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        elements.unitDisplay.textContent = `${requiredUnits} Unit${requiredUnits > 1 ? 's' : ''}`;
        elements.priceDisplay.textContent = `New Est. Price: ${options.currencySymbol}${formattedPrice}`;

        if (adults !== originalAdults || children !== originalChildren) {
            elements.summaryDisplay.innerHTML = `You are requesting to change guests from <strong>${originalAdults} Adults, ${originalChildren} Children</strong> to <strong>${adults} Adults, ${children} Children</strong>. The new estimated price will be ${options.currencySymbol}${formattedPrice}.`;
            elements.summaryDisplay.style.display = 'block';
            elements.saveButton.style.display = 'inline-block';
        } else {
            elements.summaryDisplay.style.display = 'none';
            elements.saveButton.style.display = 'none';
        }
    }

    function saveChanges() {
        const url = `${options.baseUrl}index.php?option=com_bookingmanager&task=updateBookingFromPortal&${options.token}=1`;
        const formData = new FormData();
        formData.append('booking_id', options.booking_id);
        formData.append('adults', elements.adultsInput.value);
        formData.append('children', elements.childrenInput.value);
        formData.append('price_estimate', elements.priceDisplay.textContent);
        formData.append('unit_count', elements.unitDisplay.textContent.charAt(0));

        elements.saveButton.disabled = true;
        elements.saveButton.textContent = 'Saving...';

        fetch(url, { method: 'POST', body: new URLSearchParams(formData) })
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
        const url = `${options.baseUrl}index.php?option=com_bookingmanager&task=logActivity&${options.token}=1`;
        const formData = new FormData();
        formData.append('booking_id', options.booking_id);
        formData.append('action_type', 'Viewed Portal');
        formData.append('screen_size', `${window.screen.width}x${window.screen.height}`);
        navigator.sendBeacon(url, new URLSearchParams(formData));
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
