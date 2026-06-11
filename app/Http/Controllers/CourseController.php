<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Subject;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /* ============================== TEACHER ============================== */

    public function index()
    {
        $courses = Course::where('teacher_id', auth()->id())
            ->withCount(['lessons', 'students'])
            ->latest()
            ->get();

        return view('courses.index', [
            'courses'  => $courses,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'summary'     => ['nullable', 'string'],
            'subject_id'  => ['nullable', 'exists:subjects,id'],
            'emoji'       => ['nullable', 'string', 'max:8'],
            'cover_color' => ['nullable', 'in:indigo,violet,emerald,amber,rose,sky'],
        ]);

        $course = Course::create([
            'teacher_id'  => auth()->id(),
            'title'       => $data['title'],
            'summary'     => $data['summary'] ?? null,
            'subject_id'  => $data['subject_id'] ?? null,
            'emoji'       => $data['emoji'] ?: '📘',
            'cover_color' => $data['cover_color'] ?? 'indigo',
        ]);

        return redirect()->route('teacher.courses.edit', $course)->with('status', 'Курс створено. Наповніть його матеріалом.');
    }

    public function edit(Course $course)
    {
        $this->authorizeTeacher($course);

        $course->load(['lessons.blocks', 'subject']);

        return view('courses.edit', ['course' => $course]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeTeacher($course);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'summary'     => ['nullable', 'string'],
            'subject_id'  => ['nullable', 'exists:subjects,id'],
            'emoji'       => ['nullable', 'string', 'max:8'],
            'cover_color' => ['nullable', 'in:indigo,violet,emerald,amber,rose,sky'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $data['emoji'] = $data['emoji'] ?: '📘';
        $data['is_published'] = $request->boolean('is_published');
        $course->update($data);

        return back()->with('status', 'Курс оновлено.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeTeacher($course);
        $course->delete();

        return redirect()->route('teacher.courses')->with('status', 'Курс видалено.');
    }

    /* ----------------------------- Lessons ----------------------------- */

    public function storeLesson(Request $request, Course $course)
    {
        $this->authorizeTeacher($course);

        $request->validate(['title' => ['required', 'string', 'max:255']]);

        $course->lessons()->create([
            'title'    => $request->title,
            'position' => (int) $course->lessons()->max('position') + 1,
        ]);

        return back()->with('status', 'Модуль додано.');
    }

    public function destroyLesson(Course $course, CourseLesson $lesson)
    {
        $this->authorizeTeacher($course);
        abort_unless($lesson->course_id === $course->id, 404);
        $lesson->delete();

        return back()->with('status', 'Модуль видалено.');
    }

    /* ------------------------------ Blocks ------------------------------ */

    public function storeBlock(Request $request, Course $course, CourseLesson $lesson)
    {
        $this->authorizeTeacher($course);
        abort_unless($lesson->course_id === $course->id, 404);

        $validated = $request->validate([
            'type' => ['required', 'in:text,image,quiz,button'],
        ]);

        $data = $this->blockData($request, $validated['type']);

        $lesson->blocks()->create([
            'type'     => $validated['type'],
            'position' => (int) $lesson->blocks()->max('position') + 1,
            'data'     => $data,
        ]);

        return back()->with('status', 'Блок додано.');
    }

    public function destroyBlock(Course $course, CourseLesson $lesson, ContentBlock $block)
    {
        $this->authorizeTeacher($course);
        abort_unless($lesson->course_id === $course->id && $block->course_lesson_id === $lesson->id, 404);
        $block->delete();

        return back()->with('status', 'Блок видалено.');
    }

    /** Build the JSON payload for a block based on its type. */
    private function blockData(Request $request, string $type): array
    {
        return match ($type) {
            'text' => $request->validate([
                'text' => ['required', 'string'],
            ]),
            'image' => $request->validate([
                'url'     => ['required', 'url:http,https', 'max:2048'],
                'caption' => ['nullable', 'string', 'max:255'],
            ]),
            'button' => $request->validate([
                'label' => ['required', 'string', 'max:120'],
                'url'   => ['required', 'url:http,https', 'max:2048'],
                'style' => ['nullable', 'in:primary,secondary'],
            ]) + ['style' => $request->input('style', 'primary')],
            'quiz' => $this->quizData($request),
        };
    }

    private function quizData(Request $request): array
    {
        $request->validate([
            'question'  => ['required', 'string', 'max:500'],
            'options'   => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'max:255'],
            'answer'    => ['required', 'integer', 'min:0'],
        ]);

        $options = array_values($request->input('options'));
        $answer = (int) $request->input('answer');
        abort_if($answer >= count($options), 422, 'Невірний індекс відповіді.');

        return [
            'question' => $request->input('question'),
            'options'  => $options,
            'answer'   => $answer,
        ];
    }

    /* ============================== STUDENT ============================== */

    public function studentIndex()
    {
        $courses = Course::where('is_published', true)
            ->with('teacher', 'subject')
            ->withCount('lessons')
            ->latest()
            ->get();

        $enrolledIds = auth()->user()->enrolledCourses()->pluck('courses.id');

        return view('courses.student-index', compact('courses', 'enrolledIds'));
    }

    public function show(Course $course)
    {
        abort_unless($course->is_published || $course->teacher_id === auth()->id(), 403);

        $course->load(['lessons.blocks', 'teacher', 'subject']);

        // Auto-enroll students on first view.
        if (auth()->user()->isStudent()) {
            $course->students()->syncWithoutDetaching([auth()->id()]);
        }

        return view('courses.show', ['course' => $course, 'preview' => false]);
    }

    /** Teacher previews their own course exactly as a student would (no enrollment). */
    public function preview(Course $course)
    {
        $this->authorizeTeacher($course);

        $course->load(['lessons.blocks', 'teacher', 'subject']);

        return view('courses.show', ['course' => $course, 'preview' => true]);
    }

    private function authorizeTeacher(Course $course): void
    {
        abort_unless($course->teacher_id === auth()->id(), 403);
    }
}
