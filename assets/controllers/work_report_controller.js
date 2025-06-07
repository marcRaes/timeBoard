import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["fileInput", "error"]

    connect() {
        if (this.errorTarget) {
            this.errorTarget.style.display = 'none';
        }
    }

    validateFile() {
        const input = this.fileInputTarget;
        const errorDiv = this.errorTarget;
        const maxSize = 2 * 1024 * 1024; // 2 Mo en octets

        if (input.files && input.files.length > 0) {
            if (input.files[0].size > maxSize) {
                errorDiv.textContent = "Le justificatif de transport ne doit pas dépasser 2 Mo.";
                errorDiv.style.display = "block";
                input.value = "";
            } else {
                errorDiv.textContent = "";
                errorDiv.style.display = "none";
            }
        }
    }
}
