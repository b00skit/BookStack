<div component="attachment-previewer" class="attachment-previewer">
    <div class="attachment-previewer__list" role="listbox">
        @foreach($attachments as $attachment)
            <button type="button"
                    role="option"
                    class="attachment-previewer__item"
                    aria-selected="false"
                    data-preview-url="{{ $attachment->getPreviewDisplayUrl() }}"
                    data-preview-type="{{ $attachment->getPreviewDisplayType() }}"
                    data-preview-name="{{ $attachment->name }}">
                <span class="attachment-previewer__item-icon">@icon('file')</span>
                <span class="attachment-previewer__item-label">{{ $attachment->name }}</span>
            </button>
        @endforeach
    </div>
    <div class="attachment-previewer__viewer">
        <iframe class="attachment-previewer__frame"
                refs="attachment-previewer@frame"
                title=""
                loading="lazy"
                sandbox="allow-same-origin"
                allowfullscreen></iframe>
        <div class="attachment-previewer__message" refs="attachment-previewer@message" hidden></div>
    </div>
</div>
