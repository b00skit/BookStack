import {Component} from './component';

interface PreviewButton extends HTMLButtonElement {
    dataset: DOMStringMap & {
        previewAvailable?: string;
        previewUrl?: string;
        previewName?: string;
        previewDownload?: string;
        previewAllow?: string;
    };
}

export class FilePreviews extends Component {

    protected buttons: PreviewButton[] = [];
    protected frame: HTMLIFrameElement | null = null;
    protected emptyMessage: HTMLElement | null = null;
    protected statusMessage: HTMLElement | null = null;
    protected fileName: HTMLElement | null = null;
    protected downloadLink: HTMLAnchorElement | null = null;

    setup(): void {
        const buttonRefs = this.$manyRefs.previewButton || [];
        this.buttons = buttonRefs.filter((el): el is PreviewButton => el instanceof HTMLButtonElement);

        const frameRef = this.$refs.frame;
        this.frame = frameRef instanceof HTMLIFrameElement ? frameRef : null;

        const emptyRef = this.$refs.empty;
        this.emptyMessage = emptyRef instanceof HTMLElement ? emptyRef : null;

        const messageRef = this.$refs.message;
        if (messageRef instanceof HTMLElement) {
            this.statusMessage = messageRef;
            this.statusMessage.textContent = this.$opts.instructionsText || this.statusMessage.textContent || '';
        }

        const fileNameRef = this.$refs.fileName;
        this.fileName = fileNameRef instanceof HTMLElement ? fileNameRef : null;

        const downloadRef = this.$refs.download;
        this.downloadLink = downloadRef instanceof HTMLAnchorElement ? downloadRef : null;

        for (const button of this.buttons) {
            button.addEventListener('click', () => {
                this.showPreview(button);
            });
        }

        if (this.buttons.length === 0) {
            this.showEmptyState();
            return;
        }

        const firstAvailable = this.buttons.find(btn => btn.dataset.previewAvailable === 'true');
        if (firstAvailable) {
            this.showPreview(firstAvailable);
        } else {
            this.showPreview(this.buttons[0]);
        }
    }

    protected showPreview(button: PreviewButton): void {
        this.markActive(button);

        if (this.emptyMessage) {
            this.emptyMessage.hidden = true;
        }

        const available = button.dataset.previewAvailable === 'true';
        const frameUrl = button.dataset.previewUrl || '';
        const fileName = button.dataset.previewName || '';
        const downloadUrl = button.dataset.previewDownload || '';
        const allow = button.dataset.previewAllow || '';

        if (this.fileName) {
            if (fileName) {
                this.fileName.textContent = fileName;
                this.fileName.hidden = false;
            } else {
                this.fileName.hidden = true;
            }
        }

        if (this.downloadLink) {
            if (downloadUrl) {
                this.downloadLink.href = downloadUrl;
                if (this.$opts.downloadText) {
                    this.downloadLink.textContent = this.$opts.downloadText;
                }
                this.downloadLink.hidden = false;
            } else {
                this.downloadLink.hidden = true;
            }
        }

        if (!available || !frameUrl || !this.frame) {
            if (this.frame) {
                this.frame.hidden = true;
                this.frame.removeAttribute('src');
                this.frame.removeAttribute('allow');
            }
            if (this.statusMessage) {
                this.statusMessage.textContent = this.$opts.unavailableText || this.statusMessage.textContent || '';
                this.statusMessage.hidden = false;
            }
            return;
        }

        if (this.statusMessage) {
            this.statusMessage.hidden = true;
        }

        this.frame.hidden = false;
        this.frame.src = frameUrl;
        this.frame.title = fileName;
        if (allow) {
            this.frame.setAttribute('allow', allow);
        } else {
            this.frame.removeAttribute('allow');
        }
    }

    protected markActive(button: PreviewButton): void {
        for (const btn of this.buttons) {
            const isActive = btn === button;
            btn.classList.toggle('primary-background-light', isActive);
            btn.classList.toggle('text-strong', isActive);
        }
    }

    protected showEmptyState(): void {
        if (this.emptyMessage) {
            this.emptyMessage.textContent = this.$opts.emptyText || this.emptyMessage.textContent || '';
            this.emptyMessage.hidden = false;
        }

        if (this.statusMessage) {
            this.statusMessage.hidden = true;
        }

        if (this.frame) {
            this.frame.hidden = true;
            this.frame.removeAttribute('src');
            this.frame.removeAttribute('allow');
        }

        if (this.fileName) {
            this.fileName.hidden = true;
        }

        if (this.downloadLink) {
            this.downloadLink.hidden = true;
        }
    }
}
