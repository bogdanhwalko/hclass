<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Board;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    /* ------------------------------ Teacher ------------------------------- */

    public function index()
    {
        $teacher = auth()->user();

        $lessons = Lesson::with(['student', 'subject', 'board'])
            ->where('teacher_id', $teacher->id)
            ->orderByRaw("FIELD(status,'planned','done','cancelled')")
            ->orderBy('scheduled_at')
            ->get();

        // Students this teacher can assign: those in classes the teacher is linked to.
        $classIds = \DB::table('class_subject')->where('teacher_id', $teacher->id)->pluck('school_class_id')
            ->merge($teacher->homeroomClasses()->pluck('id'))
            ->unique();

        $students = User::role(Role::Student)
            ->whereIn('id', function ($q) use ($classIds) {
                $q->select('student_id')->from('class_student')->whereIn('school_class_id', $classIds);
            })
            ->orderBy('name')
            ->get();

        // Fallback: if no class links, allow any student (small demo dataset).
        if ($students->isEmpty()) {
            $students = User::role(Role::Student)->orderBy('name')->get();
        }

        $subjects = Subject::orderBy('name')->get();

        return view('lessons.teacher', compact('lessons', 'students', 'subjects'));
    }

    public function store(Request $request)
    {
        $teacher = auth()->user();

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'student_id'   => ['nullable', 'exists:users,id'],
            'subject_id'   => ['nullable', 'exists:subjects,id'],
            'scheduled_at' => ['nullable', 'date'],
            'duration_min' => ['nullable', 'integer', 'min:5', 'max:240'],
            'with_board'   => ['nullable', 'boolean'],
        ]);

        $boardId = null;
        if ($request->boolean('with_board')) {
            $board = Board::create([
                'title' => 'Дошка: '.$data['title'],
                'token' => Str::random(24),
                'teacher_id' => $teacher->id,
            ]);
            // Auto-invite the assigned student so they get access to the board.
            if (! empty($data['student_id'])) {
                $board->invitedStudents()->syncWithoutDetaching([$data['student_id']]);
            }
            $boardId = $board->id;
        }

        Lesson::create([
            'teacher_id'   => $teacher->id,
            'student_id'   => $data['student_id'] ?? null,
            'subject_id'   => $data['subject_id'] ?? null,
            'board_id'     => $boardId,
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'duration_min' => $data['duration_min'] ?? 45,
        ]);

        return back()->with('status', 'Урок створено.');
    }

    public function update(Request $request, Lesson $lesson)
    {
        $this->authorizeTeacher($lesson);

        $data = $request->validate([
            'status' => ['required', 'in:planned,done,cancelled'],
        ]);
        $lesson->update($data);

        return back()->with('status', 'Статус уроку оновлено.');
    }

    public function destroy(Lesson $lesson)
    {
        $this->authorizeTeacher($lesson);
        $lesson->delete();

        return back()->with('status', 'Урок видалено.');
    }

    /** Create a board on demand for an existing lesson, then open it. */
    public function attachBoard(Lesson $lesson)
    {
        $this->authorizeTeacher($lesson);
        $this->ensureBoard($lesson);

        return redirect()->route('board.show', $lesson->board->fresh());
    }

    /** Create a board for a lesson but return to the previous page (e.g. calendar). */
    public function attachBoardBack(Lesson $lesson)
    {
        $this->authorizeTeacher($lesson);
        $this->ensureBoard($lesson);

        return back()->with('status', 'Дошку для уроку створено.');
    }

    /** Create the lesson's board if it doesn't have one yet. */
    private function ensureBoard(Lesson $lesson): void
    {
        if ($lesson->board_id) {
            return;
        }
        $board = Board::create([
            'title' => 'Дошка: '.$lesson->title,
            'token' => Str::random(24),
            'teacher_id' => $lesson->teacher_id,
        ]);
        if ($lesson->student_id) {
            $board->invitedStudents()->syncWithoutDetaching([$lesson->student_id]);
        }
        $lesson->update(['board_id' => $board->id]);
    }

    /* ------------------------------ Student ------------------------------- */

    public function studentIndex()
    {
        $lessons = Lesson::with(['teacher', 'subject', 'board'])
            ->where('student_id', auth()->id())
            ->orderByRaw("FIELD(status,'planned','done','cancelled')")
            ->orderBy('scheduled_at')
            ->get();

        return view('lessons.student', compact('lessons'));
    }

    private function authorizeTeacher(Lesson $lesson): void
    {
        abort_unless($lesson->teacher_id === auth()->id(), 403);
    }
}
