<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     * Uses model type and ID for organized folder structure.
     */
    public function getPath(Media $media): string
    {
        // Get model class name without namespace (e.g., "Applications")
        $modelType = class_basename($media->model_type);

        // Create path like: applications/1/
        return strtolower($modelType) . '/' . $media->model_id . '/';
    }

    /**
     * Get the path for conversions of the given media.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    /**
     * Get the path for responsive images of the given media.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive-images/';
    }
}
