<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Show a monthly calendar. Each user sees their own; teachers/parents/admins
     * may view another user's calendar via ?user=ID when authorized.
     */
    public function index(Request $request)
    {
        $viewer = auth()->user();

        // Whose calendar are we viewing?
        $target = $viewer;
        if ($request->filled('user')) {
            $target = User::findOrFail((int) $request->input('user'));
            abort_unless($this->canView($viewer, $target), 403, 'Немає доступу до цього календаря.');
        }

        // Month being viewed (defaults to current). Guard against malformed input
        // so a bad ?month= value can't trigger a 500.
        $month = Carbon::now()->startOfMonth();
        if ($request->filled('month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth();
            } catch (\Throwable $e) {
                // keep current month
            }
        }

        $rangeStart = $month->copy()->startOfMonth();
        $rangeEnd   = $month->copy()->endOfMonth();

        $lessons = $this->lessonsFor($target)
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$rangeStart, $rangeEnd])
            ->with(['teacher', 'student', 'subject', 'board'])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (Lesson $l) => $l->scheduled_at->format('Y-m-d'));

        // Build the grid (Mon-first weeks covering the whole month).
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $days[] = [
                'date'        => $d->copy(),
                'inMonth'     => $d->month === $month->month,
                'isToday'     => $d->isToday(),
                'lessons'     => $lessons->get($key, collect()),
            ];
        }

        // People the viewer may switch to (for the picker).
        $people = $this->viewableUsers($viewer);

        // Teachers can create lessons straight from the calendar.
        $canCreate = $viewer->isTeacher();
        $students = $canCreate ? $this->teacherStudents($viewer) : collect();
        $subjects = $canCreate ? Subject::orderBy('name')->get() : collect();

        return view('calendar.index', [
            'month'     => $month,
            'prev'      => $month->copy()->subMonth()->format('Y-m'),
            'next'      => $month->copy()->addMonth()->format('Y-m'),
            'days'      => $days,
            'target'    => $target,
            'isSelf'    => $target->id === $viewer->id,
            'people'    => $people,
            'upcoming'  => $lessons->flatten()->where('scheduled_at', '>=', Carbon::now())->take(6),
            'canCreate' => $canCreate,
            'students'  => $students,
            'subjects'  => $subjects,
        ]);
    }

    /** Students a teacher may assign lessons to (with demo fallback). */
    private function teacherStudents(User $teacher)
    {
        $classIds = DB::table('class_subject')->where('teacher_id', $teacher->id)->pluck('school_class_id')
            ->merge($teacher->homeroomClasses()->pluck('id'))
            ->unique();

        $students = User::role(Role::Student)
            ->whereIn('id', function ($q) use ($classIds) {
                $q->select('student_id')->from('class_student')->whereIn('school_class_id', $classIds);
            })
            ->orderBy('name')
            ->get();

        return $students->isEmpty()
            ? User::role(Role::Student)->orderBy('name')->get()
            : $students;
    }

    /** Base lesson query for a user, depending on their role. */
    private function lessonsFor(User $user)
    {
        if ($user->isTeacher()) {
            return Lesson::where('teacher_id', $user->id);
        }

        // Students (and anyone else we view) get lessons assigned to them.
        return Lesson::where('student_id', $user->id);
    }

    /** Can $viewer see $target's calendar? */
    private function canView(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }
        if ($viewer->isAdmin()) {
            return true;
        }
        if ($viewer->isParent()) {
            return $viewer->children()->whereKey($target->id)->exists();
        }
        if ($viewer->isTeacher()) {
            // A teacher may view a student they have lessons with.
            return Lesson::where('teacher_id', $viewer->id)
                ->where('student_id', $target->id)
                ->exists();
        }

        return false;
    }

    /** List of users whose calendars the viewer may open (for the switcher). */
    private function viewableUsers(User $viewer)
    {
        if ($viewer->isParent()) {
            return $viewer->children()->orderBy('name')->get(['users.id', 'name']);
        }
        if ($viewer->isTeacher()) {
            return User::whereIn('id', Lesson::where('teacher_id', $viewer->id)
                    ->whereNotNull('student_id')->distinct()->pluck('student_id'))
                ->orderBy('name')->get(['id', 'name']);
        }
        if ($viewer->isAdmin()) {
            return User::role(\App\Enums\Role::Student)->orderBy('name')->get(['id', 'name']);
        }

        return collect();
    }
}
