import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = []

    load(event) {
        // On force Turbo à charger la frame avec l'URL de l'attribut href
        const url = event.currentTarget.getAttribute('href');
        const frame = document.getElementById('work-report-form-frame');
        frame.src = url;
    }
}
