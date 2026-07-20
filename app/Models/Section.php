<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'school_year_id',
        'grade_level_id',
        'adviser_id',
        'name',
        'room',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function adviser(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'adviser_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /** Currently enrolled learners in this section. */
    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class)
            ->whereIn('status', ['enrolled', 'transferred_in']);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class);
    }

    public function teacherSchedules(): HasMany
    {
        return $this->hasMany(TeacherSchedule::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Sections a teacher may take attendance for: those they advise, those
     * offering a subject they are assigned to teach, or any section they have
     * added to their own weekly schedule (a subject teacher declaring a class
     * they teach but do not advise).
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where(function (Builder $q) use ($teacherId) {
            $q->where('adviser_id', $teacherId)
                ->orWhereHas('subjectAssignments.teacherAssignments',
                    fn (Builder $t) => $t->where('teacher_id', $teacherId))
                ->orWhereHas('teacherSchedules',
                    fn (Builder $s) => $s->where('teacher_id', $teacherId));
        });
    }
}
