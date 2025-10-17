import {Component} from './component';

export class AttachmentPreviews extends Component {

    setup() {
        this.previewArea = this.$refs.previewArea;
        this.previewButtons = this.$manyRefs.previewButton || [];
        this.emptyMessage = this.$opts.emptyMessage ?? '';

        this.setupListeners();
        this.selectInitialPreview();
    }

    setupListeners() {
        for (const button of this.previewButtons) {
            button.addEventListener('click', event => {
                event.preventDefault();
                this.showPreview(button);
            });
        }
    }

    selectInitialPreview() {
        if (this.previewButtons.length === 0) {
            return;
        }

        const activeButton = this.previewButtons[0];
        this.showPreview(activeButton);
    }

    showPreview(button) {
        for (const btn of this.previewButtons) {
            btn.classList.toggle('active', btn === button);
        }

        const previewDataRaw = button.dataset.preview ?? '';

        if (!previewDataRaw) {
            this.renderMessage();
            return;
        }

        let previewData;
        try {
            previewData = JSON.parse(previewDataRaw);
        } catch (error) {
            this.renderMessage();
            return;
        }

        if (!previewData || !previewData.src) {
            this.renderMessage();
            return;
        }

        if (!this.previewArea) {
            return;
        }

        this.previewArea.innerHTML = '';
        const iframe = document.createElement('iframe');
        iframe.src = previewData.src;
        iframe.title = previewData.title ?? '';
        iframe.loading = 'lazy';
        iframe.className = 'attachment-preview-frame';
        iframe.setAttribute('allowfullscreen', 'true');
        this.previewArea.appendChild(iframe);
    }

    renderMessage() {
        if (!this.previewArea) {
            return;
        }

        this.previewArea.innerHTML = '';
        if (!this.emptyMessage) {
            return;
        }

        const message = document.createElement('p');
        message.className = 'text-muted small';
        message.textContent = this.emptyMessage;
        this.previewArea.appendChild(message);
    }
}
