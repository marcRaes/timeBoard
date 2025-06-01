import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'container',
        'addButton',
        'hasLunchTicket',
        'isFullDay',
        'workPeriodTemplate',
        'workPeriodEntry',
        'lunchTicketMessage'
    ];

    initialize() {
        this.containerTarget.addEventListener('change', (event) => {
            if (event.target.matches('input[name$="[timeStart]"], input[name$="[timeEnd]"]')) {
                this.handleTimeChange(event);
            }
        });
    }

    connect() {
        this.index = this.workPeriodEntryTargets.length;
        this.updateAddButtonVisibility();
        this.updateLunchTicketVisibility();
        this.updateRemoveButtonsVisibility();
    }

    addPeriod(event) {
        event.preventDefault();
        if (this.index >= 4 || this.isFullDayTarget.checked) return;

        const template = this.workPeriodTemplateTarget.innerHTML.replace(/__name__/g, this.index);
        const fragment = document.createRange().createContextualFragment(template);
        this.containerTarget.appendChild(fragment);
        this.index++;
        this.updateAddButtonVisibility();
        this.updateRemoveButtonsVisibility();
    }

    removePeriod(event) {
        event.preventDefault();
        if (this.isFullDayTarget.checked && this.workPeriodEntryTargets.length <= 1) return;

        const entry = event.target.closest('.work-period-entry');
        if (entry) {
            entry.remove();
            this.index--;
            this.updateAddButtonVisibility();
            this.updateLunchTicketVisibility();
        }
    }

    updateAddButtonVisibility() {
        if (this.index >= 4 || this.isFullDayTarget.checked) {
            this.addButtonTarget.classList.add('d-none');
        } else {
            this.addButtonTarget.classList.remove('d-none');
        }
    }

    updateLunchTicketVisibility() {
        let showLunchTicket = false;
        this.workPeriodEntryTargets.forEach(entry => {
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
        const entry = event.target.closest('.work-period-entry');
        if (!entry) return;

        const timeStartInput = entry.querySelector('input[name$="[timeStart]"]');
        const timeEndInput = entry.querySelector('input[name$="[timeEnd]"]');
        const durationDisplayInput = entry.querySelector('input[name$="[durationDisplay]"]');
        const durationInput = entry.querySelector('input[name$="[duration]"]');

        if (timeStartInput && timeEndInput && durationDisplayInput && durationInput) {
            if (!timeStartInput.value || !timeEndInput.value) {
                durationDisplayInput.value = '';
                durationInput.value = '';
                return;
            }

            const [startHours, startMinutes] = timeStartInput.value.split(':').map(Number);
            const [endHours, endMinutes] = timeEndInput.value.split(':').map(Number);

            let start = startHours * 60 + startMinutes;
            let end = endHours * 60 + endMinutes;
            let duration = end - start;

            if (endHours >= 11) {
                duration -= 30;
            }

            if (duration < 0) {
                duration = 0;
            }

            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;

            durationDisplayInput.value = `${hours}H${minutes.toString().padStart(2, '0')}`;
            durationInput.value = duration;
        }

        this.updateLunchTicketVisibility();
    }

    updateRemoveButtonsVisibility() {
        this.workPeriodEntryTargets.forEach((entry, index) => {
            const removeButton = entry.querySelector('.remove-period-button');

            if (removeButton) {
                if (index >= 2) {
                    removeButton.classList.remove('d-none');
                } else {
                    removeButton.classList.add('d-none');
                }
            }
        });
    }

    handleFullDayChange(event) {
        if (event.target.checked) {
            // Supprimer tous les créneaux sauf le premier
            while (this.workPeriodEntryTargets.length > 1) {
                this.workPeriodEntryTargets[1].remove();
            }
            this.index = 1;
        } else {
            if (this.workPeriodEntryTargets.length < 2) {
                const template = this.workPeriodTemplateTarget.innerHTML.replace(/__name__/g, this.index);
                const fragment = document.createRange().createContextualFragment(template);
                this.containerTarget.appendChild(fragment);
                this.index++;
            }
        }
        this.updateAddButtonVisibility();
        this.updateLunchTicketVisibility();
        this.updateRemoveButtonsVisibility();
    }
}
