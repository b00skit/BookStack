import {Component} from './component';

const PDF_EXTENSIONS = ['pdf'];
const OFFICE_EXTENSIONS = ['doc', 'docx', 'xls', 'xlsx'];

export class PageAttachmentPreview extends Component {

    setup() {
        this.content = this.$refs.content;
        this.preview = this.$refs.preview;
        this.frame = this.$refs.frame;
        this.select = this.$refs.select;
        this.closeButton = this.$refs.close;
        this.message = this.$refs.message;
        this.title = this.$refs.title;

        this.setupListeners();
    }

    setupListeners() {
        if (this.closeButton) {
            this.closeButton.addEventListener('click', () => {
                this.hidePreview();
            });
        }

        if (this.select) {
            this.select.addEventListener('change', () => {
                const option = this.select.selectedOptions[0];
                if (option) {
                    this.loadOption(option);
                }
            });
        }

        if (window.$events && this.select) {
            window.$events.listen('attachment-preview:open', data => {
                const targetId = String(data.id);
                const option = Array.from(this.select.options).find(opt => opt.value === targetId);
                if (!option) {
                    return;
                }

                this.select.value = option.value;
                this.showPreview();
                this.loadOption(option);
            });
        }
    }

    showPreview() {
        if (this.content) {
            this.content.hidden = true;
        }
        if (this.preview) {
            this.preview.hidden = false;
        }
    }

    hidePreview() {
        if (this.preview) {
            this.preview.hidden = true;
        }
        if (this.content) {
            this.content.hidden = false;
        }
        if (this.frame) {
            this.frame.hidden = true;
            this.frame.src = '';
        }
        if (this.message) {
            this.message.hidden = true;
            this.message.textContent = '';
        }
        if (this.title) {
            this.title.hidden = true;
            this.title.textContent = '';
        }
    }

    loadOption(option) {
        const extension = (option.dataset.extension || '').toLowerCase();
        const name = option.dataset.name || option.textContent || '';
        const inlineUrl = option.dataset.inlineUrl || option.dataset.downloadUrl || '';
        const downloadUrl = option.dataset.downloadUrl || inlineUrl;

        if (this.title) {
            this.title.hidden = false;
            this.title.textContent = name;
        }

        if (!inlineUrl && !downloadUrl) {
            this.showUnsupported();
            return;
        }

        if (PDF_EXTENSIONS.includes(extension)) {
            this.showFrame(inlineUrl || downloadUrl);
            return;
        }

        if (OFFICE_EXTENSIONS.includes(extension)) {
            const encodedUrl = encodeURIComponent(downloadUrl);
            const officeUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodedUrl}`;
            this.showFrame(officeUrl);
            return;
        }

        this.showUnsupported();
    }

    showFrame(sourceUrl) {
        if (this.message) {
            this.message.hidden = true;
        }
        if (this.frame) {
            this.frame.hidden = false;
            if (this.frame.src !== sourceUrl) {
                this.frame.src = sourceUrl;
            }
        }
    }

    showUnsupported() {
        if (this.frame) {
            this.frame.hidden = true;
            this.frame.src = '';
        }
        if (this.message) {
            this.message.hidden = false;
            this.message.textContent = window.$trans
                ? window.$trans('entities.attachments_preview_unsupported')
                : 'Preview not available for this file type.';
        }
    }
}
