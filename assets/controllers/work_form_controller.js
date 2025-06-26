import { Controller } from '@hotwired/stimulus';
import { Tooltip } from 'bootstrap';

export default class extends Controller {
    static targets = [
        'container',
        'addButton',
        'hasLunchTicket',
        'workFormTemplate',
        'workFormEntry',
        'lunchTicketMessage',
        'timeError',
        'submitButton'
    ];

    initialize() {
        this.containerTarget.addEventListener('change', (event) => {
            if (event.target.matches('input[name$="[timeStart]"], input[name$="[timeEnd]"]')) {
                this.handleTimeChange(event);
            }

            if (event.target.matches('input[name$="[location]"]')) {
                this.validateForm();
            }
        });
    }

    connect() {
        const tooltipWrapper = document.getElementById('submit-button-wrapper');
        if (tooltipWrapper) {
            new Tooltip(tooltipWrapper);
        }

        this.index = this.workFormEntryTargets.length;
        this.updateAddButtonVisibility();
        this.updateLunchTicketVisibility();
        this.updateRemoveButtonsVisibility();
        this.validateForm();
    }

    addPeriod(event) {
        event.preventDefault();
        if (this.index >= 4) return;

        const template = this.workFormTemplateTarget.innerHTML.replace(/__name__/g, this.index);
        const fragment = document.createRange().createContextualFragment(template);
        this.containerTarget.appendChild(fragment);
        this.index++;
        this.updateAddButtonVisibility();
        this.updateRemoveButtonsVisibility();
        this.validateForm();
    }

    removePeriod(event) {
        event.preventDefault();
        if (this.workFormEntryTargets.length <= 1) return;

        const entry = event.target.closest('.work-form-entry');
        if (entry) {
            entry.remove();
            this.index--;
            this.updateAddButtonVisibility();
            this.updateLunchTicketVisibility();
            this.validateForm();
        }
    }

    updateAddButtonVisibility() {
        if (this.index >= 4) {
            this.addButtonTarget.classList.add('d-none');
        } else {
            this.addButtonTarget.classList.remove('d-none');
        }
    }

    updateLunchTicketVisibility() {
        let showLunchTicket = false;
        this.workFormEntryTargets.forEach(entry => {
            const timeEndInput = entry.querySelector('input[name$="[timeEnd]"]');
            if (timeEndInput && timeEndInput.value) {
                const [hours] = timeEndInput.value.split(':').map(Number);
                if (hours >= 11) showLunchTicket = true;
            }
        });

        this.hasLunchTicketTarget.querySelector('input').checked = showLunchTicket;

        if (showLunchTicket) {
            this.lunchTicketMessageTarget.classList.remove('d-none');
        } else {
            this.lunchTicketMessageTarget.classList.add('d-none');
        }
    }

    handleTimeChange(event) {
        const entry = event.target.closest('.work-form-entry');
        if (!entry) return;

        const entries = Array.from(this.workFormEntryTargets);
        const index = entries.indexOf(entry);

        const timeStartInput = entry.querySelector('input[name$="[timeStart]"]');
        const timeEndInput = entry.querySelector('input[name$="[timeEnd]"]');
        const durationDisplayInput = entry.querySelector('input[name$="[durationDisplay]"]');
        const durationInput = entry.querySelector('input[name$="[duration]"]');
        const timeError = entry.querySelector('[data-error-target="timeError"]');

        if (timeStartInput && timeEndInput && durationDisplayInput && durationInput) {
            if (!timeStartInput.value || !timeEndInput.value) {
                durationDisplayInput.value = '';
                durationInput.value = '';
                if (timeError) timeError.classList.add('d-none');
                this.validateForm();

                return;
            }

            const [startHours, startMinutes] = timeStartInput.value.split(':').map(Number);
            const [endHours, endMinutes] = timeEndInput.value.split(':').map(Number);

            let start = startHours * 60 + startMinutes;
            let end = endHours * 60 + endMinutes;

            if (start >= end) {
                durationDisplayInput.value = '';
                durationInput.value = '';
                if (timeError) timeError.classList.remove('d-none');
                this.validateForm();

                return;
            }

            if (timeError) timeError.classList.add('d-none');

            let duration = end - start;

            // Règle 1 : Déduire 30 min si début < 11h
            const isBefore11 = startHours < 11 && endHours >= 11;
            // Règle 2 : Pas de pause si l'heure de début = fin précédente
            let sameAsPreviousEnd = false;

            if (index > 0) {
                const previousEntry = entries[index - 1];
                const prevEndInput = previousEntry.querySelector('input[name$="[timeEnd]"]');
                if (prevEndInput && prevEndInput.value === timeStartInput.value) {
                    sameAsPreviousEnd = true;
                }
            }

            if (isBefore11 && !sameAsPreviousEnd) {
                duration -= 30;
            }
            if (duration < 0) duration = 0;

            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;

            durationDisplayInput.value = `${hours}H${minutes.toString().padStart(2, '0')}`;
            durationInput.value = duration;
        }

        this.updateLunchTicketVisibility();
        this.validateForm();
    }

    updateRemoveButtonsVisibility() {
        this.workFormEntryTargets.forEach((entry, index) => {
            const removeButton = entry.querySelector('.remove-period-button');

            if (removeButton) {
                if (index >= 1) {
                    removeButton.classList.remove('d-none');
                } else {
                    removeButton.classList.add('d-none');
                }
            }
        });
    }

    validateForm() {
        let isValid = true;

        this.workFormEntryTargets.forEach(entry => {
            const timeStart = entry.querySelector('input[name$="[timeStart]"]')?.value;
            const timeEnd = entry.querySelector('input[name$="[timeEnd]"]')?.value;
            const location = entry.querySelector('input[name$="[location]"]')?.value;
            const timeError = entry.querySelector('[data-error-target="timeError"]');

            const hasError = timeError && !timeError.classList.contains('d-none');
            const isEmpty = !timeStart || !timeEnd || !location;

            if (hasError || isEmpty) {
                isValid = false;
            }

            const tooltipWrapper = document.getElementById('submit-button-wrapper');
            const tooltip = Tooltip.getInstance(tooltipWrapper) || new Tooltip(tooltipWrapper);

            if (isValid) {
                tooltip.disable();
            } else {
                tooltip.enable();
            }
        });

        this.submitButtonTarget.disabled = !isValid;
    }

    preventIfInvalid(event) {
        if (this.submitButtonTarget.disabled) {
            event.preventDefault();
        }
    }
}
