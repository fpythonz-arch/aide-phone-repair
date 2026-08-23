<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepairGuideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'difficulty_level' => $this->difficulty_level,
            'difficulty_label' => $this->getDifficultyLabel($this->difficulty_level),
            'estimated_time_minutes' => $this->estimated_time_minutes,
            'estimated_time_formatted' => $this->formatTime($this->estimated_time_minutes),
            'required_tools' => $this->required_tools,
            'required_tools_count' => count($this->required_tools ?? []),
            'required_parts' => $this->required_parts,
            'steps' => $this->formatSteps($this->steps),
            'steps_count' => count($this->steps ?? []),
            'warnings' => $this->warnings,
            'warnings_count' => count($this->warnings ?? []),
            'video_url' => $this->video_url,
            'has_video' => !empty($this->video_url),
            'image_urls' => $this->image_urls,
            'images_count' => count($this->image_urls ?? []),
            'success_rate' => $this->success_rate,
            'success_rate_formatted' => $this->success_rate ? round($this->success_rate, 1) . '%' : null,
            'view_count' => $this->view_count,
            'is_published' => $this->is_published,
            'symptom' => $this->whenLoaded('symptom', function () {
                return [
                    'id' => $this->symptom->id,
                    'name' => $this->symptom->name,
                    'category' => $this->symptom->category,
                ];
            }),
            'component' => $this->whenLoaded('component', function () {
                return [
                    'id' => $this->component->id,
                    'name' => $this->component->name,
                    'slug' => $this->component->slug,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    protected function getDifficultyLabel(int $level): string
    {
        return match ($level) {
            1 => 'Débutant',
            2 => 'Intermédiaire',
            3 => 'Avancé',
            4 => 'Expert',
            5 => 'Professionnel',
            default => 'Inconnu',
        };
    }

    protected function formatTime(?int $minutes): ?string
    {
        if (!$minutes) return null;

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}min";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}min";
        }
    }

    protected function formatSteps(?array $steps): array
    {
        if (!$steps) return [];

        return array_map(function ($step, $index) {
            return [
                'order' => $step['order'] ?? ($index + 1),
                'title' => $step['title'] ?? 'Étape ' . ($index + 1),
                'description' => $step['description'] ?? '',
                'image_url' => $step['image_url'] ?? null,
                'estimated_time' => $step['estimated_time'] ?? null,
            ];
        }, $steps, array_keys($steps));
    }
}