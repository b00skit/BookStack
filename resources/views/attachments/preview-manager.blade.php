<div
     refs="editor-toolbox@tab-content"
     data-tab-content="file-previews"
     component="attachment-previews"
     class="toolbox-tab-content">

    <h4>{{ trans('entities.attachments_preview') }}</h4>

    <div class="px-l">
        @if ($page->attachments->count() > 0)
            <p class="text-muted small mb-m">{{ trans('entities.attachments_preview_intro') }}</p>
            <div class="grid half gap-xl">
                <div>
                    <ul refs="attachment-previews@list" class="mb-none">
                        @foreach ($page->attachments as $attachment)
                            <li class="mb-xs">
                                <button type="button"
                                        class="button outline block small text-left"
                                        data-attachment-id="{{ $attachment->id }}">
                                    {{ $attachment->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <div refs="attachment-previews@empty-state" class="text-muted small">
                        {{ trans('entities.attachments_preview_intro') }}
                    </div>
                    @foreach ($page->attachments as $attachment)
                        <div refs="attachment-previews@preview"
                             data-attachment-id="{{ $attachment->id }}"
                             hidden>
                            @include('attachments.preview-embed', ['attachment' => $attachment])
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-muted small">{{ trans('entities.attachments_preview_no_files') }}</p>
        @endif
    </div>
</div>
