<?php namespace Cviebrock\EloquentLoggable;

use Cviebrock\EloquentLoggable\Models\Change;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Auth\User as Authenticatable;


class LoggableObserver
{

    /**
     * @var mixed
     */
    private $userId;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $events;

    /**
     * LoggableObserver constructor.
     *
     * @param \Illuminate\Foundation\Auth\User $user
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(Authenticatable $user, Dispatcher $events)
    {
        $this->userId = $user->getKey();
        $this->events = $events;
    }

    /**
     * Log a created model.
     *
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function created(Loggable $model): void
    {
        $data = $this->buildDataToLog('create', $model);

        Change::create($data);
    }

    /**
     * Log an updated model.
     *
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function updated(Loggable $model): void
    {
        $data = $this->buildDataToLog('update', $model);

        foreach ($this->getLoggableAttributes($model) as $change) {

            $data['attribute'] = $change['attribute'];
            $data['old_value'] = $change['old_value'];
            $data['new_value'] = $change['new_value'];

            Change::create($data);
        }
    }

    /**
     * Log a deleted model.
     *
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function deleted(Loggable $model): void
    {
        $data = $this->buildDataToLog('delete', $model);

        Change::create($data);
    }

    /**
     * Log a restored model.
     *
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function restored(Loggable $model): void
    {
        $data = $this->buildDataToLog('restore', $model);

        Change::create($data);
    }

    /**
     * Build up the array of data to log.
     *
     * @param string $type
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    protected function buildDataToLog(string $type, Loggable $model): array
    {
        $data = [
            'change_type' => $type,
            'user_id'     => $this->userId,
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
        ];

        $data['change_set'] = $this->generateChangeSet($data);

        return $data;
    }

    /**
     * Return a "unique" hash to identify sets of changes.
     *
     * @param array $data
     *
     * @return string
     */
    protected function generateChangeSet(array $data): string
    {
        return substr(md5(serialize($data)), 0, 8) . '.' . microtime(true);
    }

    /**
     * Return an array of old and new values that should be logged.
     *
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    private function getLoggableAttributes(Loggable $model): array
    {
        // Get the list of all the changed attributes
        $attributes = $model->getDirty();

        // The list of timestamp attributes to filter out
        $timestamps = $this->getTimestampAttributes($model);

        // If the model has a defined list of attributes to log,
        // use that and filter the timestamp list accordingly
        if ($only = $model->getLoggableAttributes()) {
            $attributes = array_only($attributes, $only);
            $timestamps = array_diff($timestamps, $only);
        }

        // Finally, filter out any unloggable attributes,
        // and any remaining unloggable timestamp attributes
        if ($except = $model->getUnloggableAttributes()) {
            $attributes = array_except($attributes, array_merge($except, $timestamps));
        }

        // Get a list of attributes to be obfuscated
        $hidden = $model->getHidden();

        return array_map(
            function($attribute, $value) use ($model, $hidden) {
                if (in_array($attribute, $hidden, null)) {
                    return [
                        'attribute' => $attribute,
                        'old_value' => '** HIDDEN **',
                        'new_value' => '** HIDDEN **',
                    ];
                }

                return [
                    'attribute' => $attribute,
                    'old_value' => $model->getOriginal($attribute),
                    'new_value' => $value,
                ];
            },
            array_keys($attributes), $attributes
        );
    }

    /**
     * Get the timestamp attributes on the model that are normally not logged.
     *
     * @param \Cviebrock\EloquentLoggable\Loggable|\Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    private function getTimestampAttributes(Loggable $model): array
    {
        if (!$model->usesTimestamps()) {
            return [];
        }

        $attributes = [
            $model->getCreatedAtColumn(),
            $model->getUpdatedAtColumn(),
        ];
        if (method_exists($model, 'getDeletedAtColumn')) {
            $attributes[] = $model->getDeletedAtColumn();
        }

        return $attributes;
    }

}
