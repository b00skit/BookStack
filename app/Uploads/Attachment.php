<?php

namespace BookStack\Uploads;

use BookStack\App\Model;
use BookStack\Entities\Models\Entity;
use BookStack\Entities\Models\Page;
use BookStack\Permissions\Models\JointPermission;
use BookStack\Permissions\PermissionApplicator;
use BookStack\Users\Models\HasCreatorAndUpdater;
use BookStack\Users\Models\OwnableInterface;
use BookStack\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $name
 * @property string $path
 * @property string $extension
 * @property ?Page  $page
 * @property bool   $external
 * @property int    $uploaded_to
 * @property User   $updatedBy
 * @property User   $createdBy
 *
 * @method static Entity|Builder visible()
 */
class Attachment extends Model implements OwnableInterface
{
    use HasCreatorAndUpdater;
    use HasFactory;

    protected $fillable = ['name', 'order'];
    protected $hidden = ['path', 'page'];
    protected $casts = [
        'external' => 'bool',
    ];

    /**
     * Get the downloadable file name for this upload.
     */
    public function getFileName(): string
    {
        if (str_contains($this->name, '.')) {
            return $this->name;
        }

        return $this->name . '.' . $this->extension;
    }

    /**
     * Get the page this file was uploaded to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'uploaded_to');
    }

    public function jointPermissions(): HasMany
    {
        return $this->hasMany(JointPermission::class, 'entity_id', 'uploaded_to')
            ->where('joint_permissions.entity_type', '=', 'page');
    }

    /**
     * Get the url of this file.
     */
    public function getUrl($openInline = false): string
    {
        if ($this->external && !str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        return url('/attachments/' . $this->id . ($openInline ? '?open=true' : ''));
    }

    /**
     * Get the representation of this attachment in a format suitable for the page editors.
     * Detects and adapts video content to use an inline video embed.
     */
    public function editorContent(): array
    {
        $videoExtensions = ['mp4', 'webm', 'mkv', 'ogg', 'avi'];
        if (in_array(strtolower($this->extension), $videoExtensions)) {
            $html = '<video src="' . e($this->getUrl(true)) . '" controls width="480" height="270"></video>';
            return ['text/html' => $html, 'text/plain' => $html];
        }

        return ['text/html' => $this->htmlLink(), 'text/plain' => $this->markdownLink()];
    }

    /**
     * Determine if this attachment can provide preview content for editor insertion.
     */
    public function hasPreviewContent(): bool
    {
        if ($this->external) {
            return false;
        }

        return strtolower($this->extension) === 'pdf';
    }

    /**
     * Get preview-friendly content for insertion into the editors.
     */
    public function editorPreviewContent(): array
    {
        $html = '<div class="attachment-preview">'
            . '<iframe class="attachment-preview-frame" src="' . e($this->getUrl(true)) . '" title="' . e($this->name) . '" loading="lazy"></iframe>'
            . '</div>';

        return ['text/html' => $html, 'text/plain' => $html];
    }

    /**
     * Get metadata describing the preview capabilities of this attachment when shown on a page.
     *
     * @return array{available: bool, frameUrl: string|null, type: string|null}
     */
    public function pagePreviewData(): array
    {
        if ($this->external) {
            return ['available' => false, 'frameUrl' => null, 'type' => null];
        }

        $extension = strtolower($this->extension);

        if ($extension === 'pdf') {
            return ['available' => true, 'frameUrl' => $this->getUrl(true), 'type' => 'pdf'];
        }

        if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx'])) {
            $sourceUrl = $this->getUrl();
            $frameUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode($sourceUrl);

            return ['available' => true, 'frameUrl' => $frameUrl, 'type' => 'office'];
        }

        return ['available' => false, 'frameUrl' => null, 'type' => null];
    }

    /**
     * Determine if this attachment can be previewed directly on a page view.
     */
    public function supportsPagePreview(): bool
    {
        return $this->pagePreviewData()['available'];
    }

    /**
     * Get the frame URL used when rendering page previews for this attachment.
     */
    public function pagePreviewFrameUrl(): ?string
    {
        return $this->pagePreviewData()['frameUrl'];
    }

    /**
     * Generate the HTML link to this attachment.
     */
    public function htmlLink(): string
    {
        return '<a target="_blank" href="' . e($this->getUrl()) . '">' . e($this->name) . '</a>';
    }

    /**
     * Generate a MarkDown link to this attachment.
     */
    public function markdownLink(): string
    {
        return '[' . $this->name . '](' . $this->getUrl() . ')';
    }

    /**
     * Scope the query to those attachments that are visible based upon related page permissions.
     */
    public function scopeVisible(): Builder
    {
        $permissions = app()->make(PermissionApplicator::class);

        return $permissions->restrictPageRelationQuery(
            self::query(),
            'attachments',
            'uploaded_to'
        );
    }
}
