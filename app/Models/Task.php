<?php

namespace App\Models;

use App\DTOs\TaskFilterDto;
use App\Enums\TaskStatus;
use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([TaskObserver::class])]
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'assignee_id',
        'title',
        'status',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_date' => 'datetime:Y-m-d',
    ];

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Scope a query to filter projects based on the provided filters.
     *
     * @param  Builder<Task>  $query
     */
    #[Scope]
    protected function filter(Builder $query, ?TaskFilterDto $filters): void
    {
        $query->when(filled($filters?->status), function () use ($query, $filters) {
            $query->where('status', $filters?->status);
        })
            ->when(filled($filters?->due_date), function () use ($query, $filters) {
                $query->whereDate('due_date', $filters?->due_date);
            });
    }
}
