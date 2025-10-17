<?php

namespace BookStack\Uploads;

use BookStack\Exceptions\NotFoundException;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Symfony\Component\HttpFoundation\Response;

class AttachmentPreviewService
{
    public function __construct(
        protected AttachmentService $attachmentService,
    ) {
    }

    /**
     * Get a preview response for the given attachment.
     */
    public function stream(Attachment $attachment): Response
    {
        return match ($attachment->getPreviewDisplayType()) {
            'document' => $this->documentPreview($attachment),
            'spreadsheet' => $this->spreadsheetPreview($attachment),
            default => throw new NotFoundException(),
        };
    }

    protected function documentPreview(Attachment $attachment): Response
    {
        $filePath = $this->copyAttachmentToTempFile($attachment);

        try {
            $readerType = strtolower($attachment->extension) === 'doc' ? 'MsDoc' : 'Word2007';
            $phpWord = WordIOFactory::load($filePath, $readerType);
            $writer = WordIOFactory::createWriter($phpWord, 'HTML');
            $html = $this->captureWriterOutput($writer);
        } catch (\Throwable $exception) {
            $html = $this->previewErrorHtml($exception);
        } finally {
            @unlink($filePath);
        }

        return $this->htmlResponse($html, $attachment->name);
    }

    protected function spreadsheetPreview(Attachment $attachment): Response
    {
        $filePath = $this->copyAttachmentToTempFile($attachment);

        try {
            $spreadsheet = SpreadsheetIOFactory::load($filePath);
            $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Html');
            $html = $this->captureWriterOutput($writer);
        } catch (\Throwable $exception) {
            $html = $this->previewErrorHtml($exception);
        } finally {
            @unlink($filePath);
        }

        return $this->htmlResponse($html, $attachment->name, true);
    }

    protected function captureWriterOutput(object $writer): string
    {
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    protected function htmlResponse(string $html, string $title, bool $wrapTableStyles = false): Response
    {
        $titleEscaped = e($title);
        $styles = '<style>body{font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;padding:16px;background:#fff;color:#222;}' .
            'table{width:100%;border-collapse:collapse;}' .
            'th,td{border:1px solid #ccc;padding:8px;text-align:left;}' .
            'img{max-width:100%;height:auto;}</style>';

        if ($wrapTableStyles) {
            $styles .= '<style>.table{width:100%;}</style>';
        }

        if (!Str::contains(strtolower($html), '<html')) {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . $titleEscaped . '</title>' .
                $styles . '</head><body>' . $html . '</body></html>';
        } else {
            $html = preg_replace('/<head(.*?)>/', '<head$1>' . $styles, $html, 1) ?? $html;
        }

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    protected function previewErrorHtml(\Throwable $exception): string
    {
        $message = trans('errors.attachment_preview_failed');

        if (config('app.debug')) {
            $message .= '<br><code>' . e($exception->getMessage()) . '</code>';
        }

        return '<div style="padding:24px;font-family:Inter,system-ui,-apple-system,\"Segoe UI\",sans-serif;">' .
            '<h2 style="margin-bottom:16px;">' . e(trans('errors.error')) . '</h2>' .
            '<p>' . $message . '</p>' .
            '</div>';
    }

    /**
     * Copy the given attachment into a local temporary file and return the path.
     */
    protected function copyAttachmentToTempFile(Attachment $attachment): string
    {
        $readStream = $this->attachmentService->streamAttachmentFromStorage($attachment);

        if (!is_resource($readStream)) {
            throw new NotFoundException();
        }

        $tempBasePath = tempnam(sys_get_temp_dir(), 'bs-preview-');
        if ($tempBasePath === false) {
            fclose($readStream);
            throw new NotFoundException();
        }
        $extension = strtolower($attachment->extension);
        $tempPath = $tempBasePath . ($extension ? '.' . $extension : '');
        @rename($tempBasePath, $tempPath);

        $tempHandle = fopen($tempPath, 'w+b');
        if ($tempHandle === false) {
            fclose($readStream);
            throw new NotFoundException();
        }

        stream_copy_to_stream($readStream, $tempHandle);
        fclose($tempHandle);
        fclose($readStream);

        return $tempPath;
    }
}
