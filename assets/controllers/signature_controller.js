import { Controller } from '@hotwired/stimulus'
import SignaturePad from 'signature_pad'

export default class extends Controller {
    static targets = ['canvas', 'input', 'submit']

    connect() {
        this.initialized = false
        this.handleModalShown = this.handleModalShown.bind(this)

        const modalEl = this.element.closest('.modal')
        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', this.handleModalShown)
        } else {
            this.initializePad()
        }

        this.toggleSubmit(false)
    }

    disconnect() {
        const modalEl = this.element.closest('.modal')
        if (modalEl) {
            modalEl.removeEventListener('shown.bs.modal', this.handleModalShown)
        }
        if (this.signaturePad) {
            this.signaturePad.off()
        }
    }

    handleModalShown() {
        if (!this.initialized) {
            this.initializePad()
            this.initialized = true
        } else {
            this.resizeCanvas()
            this.signaturePad.clear()
            this.toggleSubmit(false)
        }
    }

    initializePad() {
        this.resizeCanvas()

        const canvas = this.canvasTarget
        this.signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255,255,255)',
            penColor: 'rgb(0,0,0)',
        })

        // Nouveaux événements SignaturePad v4
        this.signaturePad.addEventListener('beginStroke', () => {
            this.toggleSubmit(false)
        })

        this.signaturePad.addEventListener('endStroke', () => {
            if (!this.signaturePad.isEmpty()) {
                this.toggleSubmit(true)
            }
        })
    }

    resizeCanvas() {
        const canvas = this.canvasTarget
        const ratio = Math.max(window.devicePixelRatio || 1, 1)
        const width = canvas.parentElement.clientWidth || 400
        const height = 150

        canvas.width = width * ratio
        canvas.height = height * ratio
        canvas.style.width = width + 'px'
        canvas.style.height = height + 'px'
        canvas.getContext('2d').scale(ratio, ratio)
    }

    clear() {
        if (this.signaturePad) {
            this.signaturePad.clear()
            this.toggleSubmit(false)
        }
    }

    toggleSubmit(enable) {
        if (this.hasSubmitTarget) {
            this.submitTarget.disabled = !enable
        }
    }

    save(event) {
        if (!this.signaturePad || this.signaturePad.isEmpty()) {
            event.preventDefault()
            alert('Merci de signer avant d’envoyer la fiche.')
            return
        }

        this.inputTarget.value = this.signaturePad.toDataURL('image/png')
    }
}
