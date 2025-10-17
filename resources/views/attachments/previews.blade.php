@php
    $attachmentsCollection = collect($attachments ?? []);
@endphp

<div component="file-previews"
     option:file-previews:empty-text="{{ trans('entities.attachments_preview_none') }}"
     option:file-previews:instructions-text="{{ trans('entities.attachments_preview_instructions') }}"
     option:file-previews:unavailable-text="{{ trans('entities.attachments_preview_unavailable') }}"
     option:file-previews:download-text="{{ trans('entities.attachments_preview_download') }}"
     class="file-previews flex-container-column gap-m">
    <div class="file-previews-selector flex-container-column gap-xs">
        @foreach($attachmentsCollection as $attachment)
            @php
                /** @var \BookStack\Uploads\Attachment $attachment */
                $previewData = $attachment->pagePreviewData();
            @endphp
            <button type="button"
                    refs="file-previews@preview-button"
                    class="text-button block text-left px-s py-xs rounded"
                    data-preview-available="{{ $previewData['available'] ? 'true' : 'false' }}"
                    data-preview-url="{{ $previewData['frameUrl'] ? e($previewData['frameUrl']) : '' }}"
                    data-preview-name="{{ e($attachment->name) }}"
                    data-preview-download="{{ e($attachment->getUrl()) }}"
                    data-preview-type="{{ $previewData['type'] ?? '' }}">
                <span class="block">{{ $attachment->name }}</span>
                <span class="text-muted text-small text-uppercase">{{ strtoupper($attachment->extension) }}</span>
            </button>
        @endforeach
    </div>
    <div class="file-previews-content flex-container-column gap-s">
        <p refs="file-previews@empty" class="text-muted">{{ trans('entities.attachments_preview_none') }}</p>
        <p refs="file-previews@message" class="text-muted" hidden>{{ trans('entities.attachments_preview_instructions') }}</p>
        <iframe refs="file-previews@frame" width="100%" height="600" hidden loading="lazy"></iframe>
        <div class="flex-container-row items-center gap-xs">
            <span refs="file-previews@file-name" class="text-strong" hidden></span>
            <a refs="file-previews@download" class="text-button" target="_blank" rel="noopener" hidden>{{ trans('entities.attachments_preview_download') }}</a>
        </div>
    </div>
</div>
