class BookingForm {
    constructor(options) {
        this.options = options;
        this.elements = {
            bookingForm: document.getElementById('bookingForm'),
            guestSelect: document.getElementById('guest-count'),
            childrenSelect: document.getElementById('children-count'),
            childAgesContainer: document.getElementById('child-ages-container'),
            childAgesLabelRow: document.getElementById('child-ages-label-row'),
            childAgeNotificationArea: document.getElementById('child-age-notification-area'),
            unitCountInput: document.getElementById('unit-count-input'),
            unitCountDisplay: document.getElementById('unit-count-display'),
            priceDisplay: document.getElementById('price-estimate-display'),
            priceInput: document.getElementById('price-estimate-input'),
            priceDisclaimer: document.getElementById('price-disclaimer'),
            startDateInput: document.getElementById('start-date'),
            endDateInput: document.getElementById('end-date'),
            datePickerEl: document.getElementById('date-range-picker'),
            nightsDisplay: document.getElementById('nights-count-display'),
            countryResidenceSelect: document.getElementById('country-residence'),
            telephoneInput: document.getElementById('telephone'),
            submitButton: document.getElementById('submit-button'),
            minStayAlert: document.getElementById('min-stay-alert'),
            discountAlert: document.getElementById('discount-applied-alert'),
            discountNoteInput: document.getElementById('discount-note-input'),
            couponCodeInput: document.getElementById('coupon-code'),
            startingFromPrice: document.getElementById('starting-from-price'),
            getQuoteButton: document.getElementById('get-quote-button'),
            priceSummaryContainer: document.getElementById('price-summary-container'),
            quoteStep2: document.getElementById('quote-step-2'),
        };
        this.numberOfNights = 0;
        this.seasonRateCounts = {};
        this.iti = null;
        this.couponDiscount = { percent: 0, message: '' };
    }

    init() {
        if (!this.options || !this.options.pricingRules) return;
        this.initIntlTelInput();
        this.initLitepicker();
        this.initEventListeners();
        this.displayStartingPrice();
        this.updateChildAgeInputs();
    }

    initIntlTelInput() {
        if (this.elements.telephoneInput) {
            this.iti = window.intlTelInput(this.elements.telephoneInput, {
                initialCountry: "auto",
                geoIpLookup: (callback) => {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback("mu"));
                },
                separateDialCode: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            });
        }
    }

    initLitepicker() {
        if (typeof Litepicker !== 'undefined') {
            new Litepicker({
                element: this.elements.datePickerEl,
                singleMode: false,
                minDate: new Date(),
                format: 'DD MMM, YYYY',
                tooltipText: { 'one': 'day', 'other': 'days' },
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        if (!date1 || !date2) return;
                        this.elements.startDateInput.value = date1.format('YYYY-MM-DD');
                        this.elements.endDateInput.value = date2.format('YYYY-MM-DD');
                        this.calculateNightsAndSeasons(date1, date2);
                    });
                }
            });
        }
    }

    initEventListeners() {
        this.elements.bookingForm.addEventListener('submit', (e) => this.submitForm(e));
        this.elements.childrenSelect.addEventListener('change', () => this.updateChildAgeInputs());
        this.elements.getQuoteButton.addEventListener('click', () => this.handleGetQuote());
        if (this.elements.countryResidenceSelect) {
            this.elements.countryResidenceSelect.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const isoCode = selectedOption.getAttribute('data-iso-code');
                if (this.iti && isoCode) {
                    this.iti.setCountry(isoCode.toLowerCase());
                }
            });
        }
    }

    displayStartingPrice() {
        const rates = this.options.pricingRules.rates;
        if (!rates || Object.keys(rates).length === 0) return;
        const lowestRate = Math.min(...Object.values(rates));
        if (this.elements.startingFromPrice && lowestRate > 0) {
            this.elements.startingFromPrice.textContent = `From ${this.options.currencySymbol}${lowestRate} / night`;
        }
    }

    calculateNightsAndSeasons(date1, date2) {
        this.seasonRateCounts = {};
        let currentDate = date1.toJSDate();
        while (currentDate < date2.toJSDate()) {
            const season = this.getSeasonForDate(currentDate);
            if (season) {
                this.seasonRateCounts[season.name] = (this.seasonRateCounts[season.name] || 0) + 1;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        this.numberOfNights = Object.values(this.seasonRateCounts).reduce((a, b) => a + b, 0);
        this.checkMinStay();
    }

    getSeasonForDate(date) {
        const rules = this.options.pricingRules;
        if (!rules || !Array.isArray(rules.seasons)) return null;
        const dateStr = date.toISOString().slice(0, 10);
        return rules.seasons.find(season => dateStr >= season.start_date && dateStr <= season.end_date) || null;
    }

    checkMinStay() {
        let minStay = 0;
        let minStaySeason = '';
        if (this.options.pricingRules && Array.isArray(this.options.pricingRules.seasons)) {
            for (const season of this.options.pricingRules.seasons) {
                if (this.seasonRateCounts[season.name] && season.min_stay > minStay) {
                    minStay = season.min_stay;
                    minStaySeason = season.name;
                }
            }
        }
        if (this.elements.minStayAlert) {
            if (this.numberOfNights > 0 && this.numberOfNights < minStay) {
                this.elements.minStayAlert.textContent = `A minimum stay of ${minStay} nights is required for the selected period (${minStaySeason} season).`;
                this.elements.minStayAlert.style.display = 'block';
            } else {
                this.elements.minStayAlert.style.display = 'none';
            }
        }
    }

    updateChildAgeInputs() {
        const childrenCount = parseInt(this.elements.childrenSelect.value, 10);
        this.elements.childAgesContainer.innerHTML = '';
        this.elements.childAgesLabelRow.style.display = childrenCount > 0 ? 'flex' : 'none';

        for (let i = 1; i <= childrenCount; i++) {
            const col = document.createElement('div');
            col.className = 'col-md-4 col-sm-6 mb-2';
            const input = document.createElement('input');
            input.type = 'number';
            input.min = '0';
            input.max = '17';
            input.name = `child_ages[]`;
            input.className = 'form-control child-age-input';
            input.placeholder = `Child ${i} Age`;
            input.required = true;
            col.appendChild(input);
            this.elements.childAgesContainer.appendChild(col);
        }
    }

    async handleGetQuote() {
        if (this.numberOfNights === 0) {
            alert('Please select your check-in and check-out dates first.');
            return;
        }
        await this.validateCouponCode();
        this.updateCalculations();
        this.elements.priceSummaryContainer.style.display = 'block';
        this.elements.quoteStep2.style.display = 'block';
        this.elements.startingFromPrice.style.display = 'none';
        this.elements.getQuoteButton.textContent = 'Recalculate Price';
    }

    async validateCouponCode() {
        const couponCode = this.elements.couponCodeInput.value.trim();
        if (!couponCode) {
            this.couponDiscount = { percent: 0, message: '' };
            return;
        }
        const url = `${this.options.submissionUrl.split('task=')[0]}task=validateCoupon&${Joomla.getFormToken()}=1`;
        const formData = new FormData();
        formData.append('coupon_code', couponCode);
        formData.append('article_id', this.options.articleId);

        try {
            const response = await fetch(url, { method: 'POST', body: new URLSearchParams(formData) });
            const data = await response.json();
            if (data.success) {
                this.couponDiscount = { percent: data.discount, message: data.message };
            } else {
                this.couponDiscount = { percent: 0, message: data.message || 'Invalid coupon.' };
                alert(this.couponDiscount.message);
            }
        } catch (error) {
            console.error('Coupon validation error:', error);
            this.couponDiscount = { percent: 0, message: 'Error validating coupon.' };
        }
    }

    updateCalculations() {
        const adults = parseInt(this.elements.guestSelect.value, 10);
        const rules = this.options.pricingRules;
        if (!rules) return;

        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));

        let totalGuestsForCapacity = adults;
        let mattressCost = 0;
        let requiredUnits = 1;
        const baseCapacity = this.options.totalAccommodationGuests || 2;

        if (rules.pricing_model === 'CapacityBased') {
            let childrenCounting = childAges.filter(age => age > rules.free_with_parents_age).length;
            totalGuestsForCapacity += childrenCounting;
            const extraGuests = totalGuestsForCapacity - baseCapacity;
            if (extraGuests === 1) { mattressCost = (rules.extra_mattress_fee || 0) * this.numberOfNights; }
            else if (extraGuests > 1) { requiredUnits = Math.ceil(totalGuestsForCapacity / baseCapacity); }
        } else {
             let infantsNotCounting = childAges.filter(age => age <= rules.infant_max_age).length;
             totalGuestsForCapacity += (childAges.length - infantsNotCounting);
             if (baseCapacity > 0) { requiredUnits = Math.max(1, Math.ceil(totalGuestsForCapacity / baseCapacity)); }
        }

        let roomCost = 0;
        for (const [seasonName, nightsInSeason] of Object.entries(this.seasonRateCounts)) {
            let nightlyRate = rules.rates[seasonName] || 0;
            const currentSeason = rules.seasons.find(s => s.name === seasonName);
            if (rules.pricing_model === 'SupplementPerGuest' && currentSeason) {
                let chargeableAdults = adults;
                let chargeableChildren = 0;
                childAges.forEach(age => {
                    if (age > rules.child_max_age) chargeableAdults++;
                    else if (age > rules.infant_max_age && currentSeason.apply_child_supplement === "1") { chargeableChildren++; }
                });
                const extraAdults = Math.max(0, chargeableAdults - 2);
                nightlyRate += (extraAdults * (rules.adult_supplement || 0)) + (chargeableChildren * (rules.child_supplement || 0));
            }
            roomCost += (nightlyRate * nightsInSeason);
        }

        let totalCost = (roomCost * requiredUnits) + mattressCost;

        const selectedCountry = this.elements.countryResidenceSelect.value;
        let discountPercent = 0;
        let discountNote = '';

        if (this.couponDiscount.percent > 0) {
            discountPercent = this.couponDiscount.percent;
            discountNote = this.couponDiscount.message;
        } else if (rules.country_discounts && selectedCountry) {
            const countryRule = rules.country_discounts.find(d => d.country === selectedCountry);
            if (countryRule && countryRule.discount_percent) {
                discountPercent = parseFloat(countryRule.discount_percent);
                discountNote = countryRule.note || `A ${discountPercent}% discount has been applied!`;
            }
        }
        if (discountPercent > 0) {
            totalCost *= (1 - (discountPercent / 100));
        }

        if (this.elements.discountAlert) {
            if (discountPercent > 0 && totalCost > 0) {
                this.elements.discountAlert.textContent = discountNote;
                this.elements.discountAlert.style.display = 'block';
                this.elements.discountNoteInput.value = discountNote;
            } else {
                this.elements.discountAlert.style.display = 'none';
                this.elements.discountNoteInput.value = '';
            }
        }

        this.elements.unitCountInput.value = requiredUnits;
        this.elements.unitCountDisplay.textContent = `${requiredUnits} Unit${requiredUnits > 1 ? 's' : ''}`;

        if (this.numberOfNights > 0) {
            const formattedPrice = totalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            this.elements.priceDisplay.textContent = `Est. Price: ${this.options.currencySymbol}${formattedPrice}`;
            this.elements.priceInput.value = `${this.options.currencySymbol}${formattedPrice}`;
            this.elements.nightsDisplay.textContent = `(${this.numberOfNights} ${this.numberOfNights > 1 ? 'Nights' : 'Night'})`;
            this.elements.priceDisclaimer.style.display = 'block';
        } else {
            this.elements.priceDisplay.textContent = 'Est. Price: -';
            this.elements.priceInput.value = 'N/A';
            this.elements.nightsDisplay.textContent = '';
            this.elements.priceDisclaimer.style.display = 'none';
        }
    }

    submitForm(event) {
        event.preventDefault();
        const formData = new FormData(this.elements.bookingForm);
        const spinner = this.elements.submitButton.querySelector('.spinner-border');
        const buttonText = this.elements.submitButton.querySelector('.button-text');
        this.elements.submitButton.disabled = true;
        if (buttonText) buttonText.textContent = 'Sending...';
        if (spinner) spinner.style.display = 'inline-block';

        fetch(this.options.submissionUrl, { method: 'POST', body: new URLSearchParams(formData) })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('booking-form-wrapper').style.display = 'none';
                    const thankYouEl = document.getElementById('thank-you-message');
                    thankYouEl.style.display = 'block';
                    document.getElementById('booking-ref-display').textContent = data.bookingRef;
                } else {
                    alert('An error occurred: ' + (data.message || 'Please try again.'));
                }
            })
            .catch(error => {
                console.error('Submission Error:', error);
                alert('A network error occurred.');
            })
            .finally(() => {
                this.elements.submitButton.disabled = false;
                if(buttonText) buttonText.textContent = 'Send Booking Request';
                if (spinner) spinner.style.display = 'none';
            });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const options = Joomla.getOptions('mod_bookingform');
    if (options) {
        const bookingForm = new BookingForm(options);
        bookingForm.init();
    }
});