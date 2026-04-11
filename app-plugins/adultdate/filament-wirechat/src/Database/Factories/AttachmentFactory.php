<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Database\Factories;

use AdultDate\FilamentWirechat\Models\Attachment;
use AdultDate\FilamentWirechat\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->uuid().'.'.fake()->fileExtension();
        $originalName = fake()->word().'.'.fake()->fileExtension();
        $mimeType = fake()->mimeType();

        return [
            'attachable_type' => Message::class,
            'attachable_id' => Message::factory(),
            'file_path' => 'attachments/'.$fileName,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'url' => '/storage/attachments/'.$fileName,
        ];
    }

    /**
     * Indicate that the attachment is an image.
     */
    public function image(): static
    {
        return $this->state(function (array $attributes) {
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = fake()->randomElement($extensions);
            $fileName = fake()->uuid().'.'.$ext;
            $mimeType = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };

            return [
                'file_name' => $fileName,
                'original_name' => fake()->word().'.'.$ext,
                'file_path' => 'attachments/'.$fileName,
                'mime_type' => $mimeType,
                'url' => '/storage/attachments/'.$fileName,
            ];
        });
    }

    /**
     * Indicate that the attachment is a document.
     */
    public function document(): static
    {
        return $this->state(function (array $attributes) {
            $extensions = ['pdf', 'doc', 'docx', 'txt'];
            $ext = fake()->randomElement($extensions);
            $fileName = fake()->uuid().'.'.$ext;
            $mimeType = match ($ext) {
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                default => 'application/pdf',
            };

            return [
                'file_name' => $fileName,
                'original_name' => fake()->word().'.'.$ext,
                'file_path' => 'attachments/'.$fileName,
                'mime_type' => $mimeType,
                'url' => '/storage/attachments/'.$fileName,
            ];
        });
    }
}
