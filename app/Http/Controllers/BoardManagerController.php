<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Board;
use App\Models\BoardGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BoardManagerController extends Controller
{
    /** Compact, filterable & paginated list of the teacher's boards. */
    public function index(Request $request)
    {
        $teacher = auth()->user();

        // Groups for the filter chips / access management (lightweight counts).
        $groups = BoardGroup::where('teacher_id', $teacher->id)
            ->with('members:id,name')
            ->withCount('boards')
            ->orderBy('name')
            ->get();

        $sort = $request->input('sort', 'recent');

        $boards = Board::where('teacher_id', $teacher->id)
            ->with('group:id,name,color')
            ->withCount(['strokes', 'widgets'])
            ->when($request->filled('search'), fn ($q) =>
                $q->where('title', 'like', '%'.$request->input('search').'%'))
            ->when($request->input('group') === 'none', fn ($q) => $q->whereNull('group_id'))
            ->when(is_numeric($request->input('group')), fn ($q) =>
                $q->where('group_id', (int) $request->input('group')))
            ->when($sort === 'name', fn ($q) => $q->orderBy('title'))
            ->when($sort === 'content', fn ($q) => $q->orderByDesc('widgets_count')->orderByDesc('strokes_count'))
            ->when($sort === 'recent', fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        $students = $this->teacherStudents($teacher);

        return view('boards.index', compact('groups', 'boards', 'students', 'sort'));
    }

    /* ------------------------------ Boards ------------------------------- */

    public function store(Request $request)
    {
        if ($request->input('group_id') === '') {
            $request->merge(['group_id' => null]);
        }

        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'group_id' => ['nullable', 'exists:board_groups,id'],
        ]);

        $this->assertGroupOwned($data['group_id'] ?? null);

        Board::create([
            'teacher_id' => auth()->id(),
            'group_id'   => $data['group_id'] ?? null,
            'title'      => $data['title'],
            'token'      => Str::random(24),
        ]);

        return back()->with('status', 'Дошку створено.');
    }

    public function update(Request $request, Board $board)
    {
        $this->assertOwner($board);

        if ($request->input('group_id') === '') {
            $request->merge(['group_id' => null]);
        }

        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'group_id' => ['nullable', 'exists:board_groups,id'],
        ]);
        $this->assertGroupOwned($data['group_id'] ?? null);

        $board->update([
            'title'    => $data['title'],
            'group_id' => $data['group_id'] ?? null,
        ]);

        return back()->with('status', 'Дошку оновлено.');
    }

    public function destroy(Board $board)
    {
        $this->assertOwner($board);
        $board->delete();

        return back()->with('status', 'Дошку видалено.');
    }

    /* ------------------------------ Groups ------------------------------- */

    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'in:indigo,violet,emerald,amber,rose,sky'],
        ]);

        BoardGroup::create([
            'teacher_id' => auth()->id(),
            'name'       => $data['name'],
            'color'      => $data['color'] ?? 'indigo',
        ]);

        return back()->with('status', 'Групу створено.');
    }

    public function destroyGroup(BoardGroup $group)
    {
        abort_unless($group->teacher_id === auth()->id(), 403);
        $group->delete(); // boards.group_id set null via FK

        return back()->with('status', 'Групу видалено. Дошки переміщено в «Без групи».');
    }

    /** Present a group as a slideshow — each board is a slide. */
    public function present(BoardGroup $group)
    {
        abort_unless($group->teacher_id === auth()->id(), 403);

        $slides = $group->boards()->orderBy('created_at')->get(['id', 'title', 'token']);

        return view('boards.present', compact('group', 'slides'));
    }

    /* --------------------------- Group access ---------------------------- */

    /** Grant a student access to every board in the group. */
    public function inviteToGroup(Request $request, BoardGroup $group)
    {
        abort_unless($group->teacher_id === auth()->id(), 403);

        $data = $request->validate(['student_id' => ['required', 'exists:users,id']]);
        $student = User::role(Role::Student)->find($data['student_id']);
        abort_unless($student, 422, 'Це не учень.');

        $group->members()->syncWithoutDetaching([$student->id]);

        return back()->with('status', "Учня {$student->name} додано до групи «{$group->name}».");
    }

    /** Revoke a student's group access. */
    public function uninviteFromGroup(BoardGroup $group, User $user)
    {
        abort_unless($group->teacher_id === auth()->id(), 403);

        $group->members()->detach($user->id);

        return back()->with('status', 'Доступ до групи скасовано.');
    }

    private function assertOwner(Board $board): void
    {
        abort_unless($board->teacher_id === auth()->id(), 403);
    }

    /** Students linked to this teacher's classes (with demo fallback). */
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

    private function assertGroupOwned($groupId): void
    {
        if ($groupId) {
            $group = BoardGroup::find($groupId);
            abort_unless($group && $group->teacher_id === auth()->id(), 403);
        }
    }
}
