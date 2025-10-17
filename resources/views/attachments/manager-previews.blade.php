<div refs="editor-toolbox@tab-content"
     data-tab-content="file-previews"
     component="attachment-previews"
     option:attachment-previews:empty-message="{{ trans('entities.attachments_preview_unavailable') }}"
     class="toolbox-tab-content">

    <h4>{{ trans('entities.attachments_previews') }}</h4>

    <div class="attachment-preview-toolbox px-l pb-l">
        @php
            $previewableAttachments = $page->attachments->filter(fn($attachment) => $attachment->supportsFullPreview());
        @endphp

        @if($previewableAttachments->isEmpty())
            <p class="text-muted small">{{ trans('entities.attachments_previews_no_files') }}</p>
        @else
            <div class="attachment-preview-list" role="tablist">
                @foreach($previewableAttachments as $attachment)
                    @php $previewData = $attachment->fullPreviewData(); @endphp
                    <button type="button"
                            class="attachment-preview-list-item"
                            role="tab"
                            data-preview='@json($previewData)'
                            data-name="{{ $attachment->name }}"
                            refs="attachment-previews@preview-button">
                        <span class="attachment-preview-name">{{ $attachment->name }}</span>
                        <span class="attachment-preview-meta text-small text-muted text-right">.{{ strtolower($attachment->extension) }}</span>
                    </button>
                @endforeach
            </div>
            <div class="attachment-preview-display" refs="attachment-previews@preview-area">
                <p class="text-muted small">{{ trans('entities.attachments_previews_description') }}</p>
            </div>
        @endif
    </div>
</div>
