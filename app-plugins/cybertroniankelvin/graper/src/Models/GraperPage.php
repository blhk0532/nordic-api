<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

class GraperPage extends Model
{
    use HasFactory;

    protected $table = 'graper_pages';

    protected $fillable = [
        'title',
        'slug',
        'project_data',
        'html',
        'css',
        'css_class',
        'is_published',
        'created_by',
        'content',
    ];

    protected $casts = [
        'project_data' => 'array',
        'is_published' => 'boolean',
    ];

    public function getContentAttribute(): ?string
    {
        $html = $this->html;
        $css = $this->css;
        $projectData = $this->project_data;

        if ($html === null && $css === null && ($projectData === null || empty($projectData))) {
            return null;
        }

        return json_encode([
            'html' => $html ?? '',
            'css' => $css ?? '',
            'project_data' => $projectData ?? [],
        ]);
    }

    public function setContentAttribute(?string $value): void
    {
        if ($value === null) {
            return;
        }

        $data = json_decode($value, true) ?? [];
        $this->html = $data['html'] ?? '';
        $this->css = $data['css'] ?? '';
        $this->project_data = $data['project_data'] ?? [];
    }

    public function creator(): BelongsTo
    {
        $userModel = config('filament.user_model', User::class);

        return $this->belongsTo($userModel, 'created_by');
    }
}
