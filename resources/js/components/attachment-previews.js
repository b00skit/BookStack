import {Component} from './component';

export class AttachmentPreviews extends Component {

    setup() {
        this.listElement = this.$refs.list || null;
        this.previewElements = this.$manyRefs.preview || [];
        this.emptyState = this.$refs.emptyState || null;
        this.buttons = [];

        if (this.listElement) {
            this.buttons = Array.from(this.listElement.querySelectorAll('[data-attachment-id]'));
        }

        for (const button of this.buttons) {
            button.addEventListener('click', () => {
                this.showPreview(button.dataset.attachmentId || '');
            });
        }

        if (this.buttons.length > 0) {
            this.showPreview(this.buttons[0].dataset.attachmentId || '');
        } else if (this.emptyState) {
            this.emptyState.removeAttribute('hidden');
        }
    }

    showPreview(attachmentId) {
        if (!attachmentId) {
            return;
        }

        if (this.emptyState) {
            this.emptyState.setAttribute('hidden', 'hidden');
        }

        for (const button of this.buttons) {
            const isActive = button.dataset.attachmentId === attachmentId;
            button.classList.toggle('primary', isActive);
            button.classList.toggle('outline', !isActive);
        }

        for (const preview of this.previewElements) {
            const isMatch = preview.dataset.attachmentId === attachmentId;
            preview.toggleAttribute('hidden', !isMatch);
        }
    }

}
