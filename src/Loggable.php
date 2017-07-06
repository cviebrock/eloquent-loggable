<?php namespace Cviebrock\EloquentLoggable;

use Cviebrock\EloquentLoggable\Models\Change;
use Illuminate\Database\Eloquent\Relations\MorphMany;


trait Loggable
{

    /**
     * Register events that intercept when model is being saved.
     */
    public static function bootLoggable()
    {
        static::observe(app(LoggableObserver::class));
    }

    /**
     * Get a list of attributes that are loggable.
     *
     * @return array
     */
    public function getLoggableAttributes(): array
    {
        return [];
    }

    /**
     * Get a list of attributes that are unloggable.
     *
     * @return array
     */
    public function getUnloggableAttributes(): array
    {
        return [];
    }

    /**
     * Relation to changes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function changes(): MorphMany
    {
        return $this->morphMany(Change::class, 'loggable', 'model_type', 'model_id')
            ->orderBy('created_at', 'desc');
    }
}
