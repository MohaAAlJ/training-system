<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'departments';
    protected $fillable = [
        'id',
        'name',
        'user_id',
        'moh_dept_user_id',
        'assistant_training_manager_id',
        'is_medical',
        'active',
        'visible',
    ];
    protected $casts = [
        'visible' => 'boolean',
    ];



    /**Scope */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    public function scopeAssignableToAssistantTrainingManager($query)
    {
        return $query
            ->active()
            ->visible();
    }

    /** Relations */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mohUser()
    {
        return $this->belongsTo(User::class, 'moh_dept_user_id');
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'department_section', 'department_id', 'section_id', 'id', 'section_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'department_section');
    }

    public function assistantTrainingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_training_manager_id');
    }

    // =========================================================================
    // CAPACITY METHODS
    // =========================================================================

    /**
     * Get capacity statistics for this department.
     *
     * @return array{total: int, used: int, available: int, is_full: bool}
     */
    public function getCapacityStats(): array
    {
        return Section::getStatsFromQuery($this->sections()->active());
    }

    /**
     * Check if at full capacity (cannot accept new applications).
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->getCapacityStats()['is_full'];
    }

    /**
     * Check if has available capacity (can accept new applications).
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !$this->isFull();
    }
}
