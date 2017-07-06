<?php namespace Cviebrock\EloquentLoggable\Models;

use Cviebrock\EloquentLoggable\Loggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;


class Change extends Model
{

    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_RESTORE = 'restore';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loggable_changes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'attribute',
        'old_value',
        'new_value',
        'model_id',
        'model_type',
        'type',
        'set',
    ];

    /**
     * Relation to user who made the change.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::get('loggable.user_model'));
    }

    /**
     * Polymorphic relation to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to find changes of a given type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to find changes in a given set.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $set
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInSet(Builder $query, string $set): Builder
    {
        return $query->where('set', $set);
    }

    /**
     * Scope to group changes by set.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroupedBySet(Builder $query): Builder
    {
        return $query->groupBy('set');
    }

    /**
     * Scope to find changes for a given model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForModel(Builder $query, Loggable $model): Builder
    {
        return $query->where('model_type', get_class($model))
            ->where('model_id', $model->getKey());
    }
}

