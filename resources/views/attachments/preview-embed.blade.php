@php
    $frameData = $attachment->getPreviewFrameData();
@endphp

@if (is_null($frameData))
    @if ($attachment->external)
        <p class="text-muted">{{ trans('entities.attachments_preview_external') }}</p>
    @else
        <p class="text-muted">{{ trans('entities.attachments_preview_not_supported') }}</p>
    @endif
@else
    <div class="attachment-preview">
        <iframe
            class="attachment-preview-frame"
            src="{{ $frameData['src'] }}"
            title="{{ $attachment->name }}"
            loading="lazy"
            allowfullscreen>
        </iframe>
    </div>
@endif
