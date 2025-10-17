import {Component} from './component';

interface PreviewButton extends HTMLButtonElement {
    dataset: {
        previewUrl: string;
        previewType: string;
        previewName: string;
    };
}

export class AttachmentPreviewer extends Component {
    protected buttons!: PreviewButton[];
    protected frame!: HTMLIFrameElement;
    protected message!: HTMLElement;

    setup() {
        this.buttons = Array.from(this.$el.querySelectorAll('.attachment-previewer__item')) as PreviewButton[];
        this.frame = this.$refs.frame as HTMLIFrameElement;
        this.message = this.$refs.message as HTMLElement;

        this.buttons.forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                this.show(button);
            });
        });

        if (this.buttons.length > 0) {
            this.show(this.buttons[0]);
        } else {
            this.setMessage(window.$trans ? window.$trans('entities.attachments_file_previews_empty') : '');
        }
    }

    protected show(button: PreviewButton): void {
        this.buttons.forEach(btn => {
            const active = btn === button;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        const title = button.dataset.previewName;
        const url = button.dataset.previewUrl;

        this.frame.title = title;
        this.frame.src = url;

        this.message.hidden = true;
        this.message.textContent = '';
    }

    protected setMessage(message: string): void {
        if (!message) {
            return;
        }

        this.message.hidden = false;
        this.message.textContent = message;
        this.frame.removeAttribute('src');
    }
}
