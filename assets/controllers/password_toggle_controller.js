import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'toggleIcon'];

    toggle() {
        const input = this.inputTarget;
        const icon = this.toggleIconTarget.querySelector('i');
        const isPassword = input.getAttribute('type') === 'password';

        input.setAttribute('type', isPassword ? 'text' : 'password');

        icon.classList.replace(
            isPassword ? 'bi-eye' : 'bi-eye-slash',
            isPassword ? 'bi-eye-slash' : 'bi-eye'
        );
    }
}