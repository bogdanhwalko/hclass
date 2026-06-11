<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Board;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BoardController extends Controller
{
    /**
     * Show the board. Only the owning teacher or invited registered students.
     */
    public function show(Board $board)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403, 'Вас не запрошено до цієї дошки.');
        abort_unless($board->is_open || $isTeacher, 403, 'Доступ до дошки закрито.');

        $canDraw = $isTeacher || $board->students_can_draw;

        $invitedStudents = collect();
        $availableStudents = collect();
        $otherBoards = collect();
        if ($isTeacher) {
            $invitedStudents = $board->invitedStudents()->orderBy('name')->get();
            $availableStudents = $this->teacherStudents($user)
                ->whereNotIn('id', $invitedStudents->pluck('id'))
                ->values();
            // Teacher's other boards — for "link to board" widgets.
            $otherBoards = Board::where('teacher_id', $user->id)
                ->where('id', '!=', $board->id)
                ->orderBy('title')
                ->get(['title', 'token']);
        }

        return view('board.show', [
            'board'             => $board->load('teacher'),
            'isTeacher'         => $isTeacher,
            'canDraw'           => $canDraw,
            'invitedStudents'   => $invitedStudents,
            'availableStudents' => $availableStudents,
            'otherBoards'       => $otherBoards,
        ]);
    }

    /** Poll strokes since a given id (lightweight realtime sync). */
    public function strokes(Board $board, Request $request)
    {
        abort_unless($board->isAccessibleBy(auth()->user()), 403);

        $since = (int) $request->query('since', 0);

        // Load all of this board's strokes once, then derive the three views in memory
        // (the polling endpoint runs every ~0.9s, so collapsing 4 queries into 2 matters).
        $all = $board->strokes()
            ->orderBy('id')
            ->get(['id', 'type', 'color', 'width', 'points', 'text', 'author_name', 'created_at', 'updated_at']);

        $nonImage = $all->where('type', '!=', 'image');

        // New strokes since the client's cursor.
        $strokes = $nonImage->where('id', '>', $since)
            ->map(fn ($s) => $s->only(['id', 'type', 'color', 'width', 'points', 'text', 'author_name']))
            ->values();

        // Images are sent in full each poll (their position/size can change in place,
        // which wouldn't bump the incremental `since` id cursor).
        $images = $all->where('type', 'image')->map(fn ($img) => [
            'id'  => $img->id,
            'url' => $img->text,
            'x'   => $img->points[0][0] ?? 0,
            'y'   => $img->points[0][1] ?? 0,
            'w'   => $img->points[1][0] ?? 0.3,
            'rev' => optional($img->updated_at)->timestamp,
        ])->values();

        // Strokes/text repositioned after creation (moved in place — the incremental
        // `since` cursor can't track that). Sent each poll so peers update.
        $moved = $nonImage->filter(fn ($s) => $s->updated_at && $s->created_at && $s->updated_at->gt($s->created_at))
            ->map(fn ($s) => ['id' => $s->id, 'points' => $s->points])
            ->values();

        // All non-image ids so peers can prune strokes deleted by others.
        $ids = $nonImage->pluck('id')->values();

        // Widgets (frames / quizzes / flashcards / links) — full list each poll.
        $isTeacher = auth()->id() === $board->teacher_id;
        $widgets = $board->widgets()->orderBy('z')->orderBy('id')->get()->map(function ($w) use ($isTeacher) {
            $data = $w->data;
            // Hide the correct answer from students so they can't peek.
            if ($w->type === 'quiz' && ! $isTeacher) {
                unset($data['answer']);
            }

            return [
                'id'      => $w->id,
                'type'    => $w->type,
                'x'       => $w->x,
                'y'       => $w->y,
                'w'       => $w->w,
                'h'       => $w->h,
                'z'       => $w->z,
                'opacity' => $w->opacity,
                'data'    => $data,
                'rev'     => optional($w->updated_at)->timestamp,
            ];
        });

        return response()->json([
            'strokes'           => $strokes,
            'images'            => $images,
            'moved'             => $moved,
            'ids'               => $ids,
            'widgets'           => $widgets,
            'background'        => $board->background,
            'background_mode'   => $board->background_mode,
            'students_can_draw' => $board->students_can_draw,
            'is_open'           => $board->is_open,
            'cleared_at'        => optional($board->cleared_at)->toISOString(),
            'last_id'           => $strokes->last()['id'] ?? $since,
        ]);
    }

    /** Append a stroke/shape/text. Teacher always; students only if permitted. */
    public function draw(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403, 'Малювання заборонено вчителем.');

        $data = $request->validate([
            'type'       => ['required', 'in:pen,line,rect,ellipse,arrow,text'],
            'color'      => ['required', 'string', 'max:16'],
            'width'      => ['required', 'integer', 'min:1', 'max:60'],
            'points'     => ['required', 'array', 'min:1'],
            'points.*'   => ['array', 'size:2'],
            'points.*.*' => ['numeric'],
            'text'       => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['type'] === 'text' && trim((string) ($data['text'] ?? '')) === '') {
            abort(422, 'Порожній текст.');
        }

        $stroke = $board->strokes()->create([
            'user_id'     => $user->id,
            'author_name' => $user->name,
            'type'        => $data['type'],
            'color'       => $data['color'],
            'width'       => $data['width'],
            'points'      => $data['points'],
            'text'        => $data['text'] ?? null,
        ]);

        return response()->json(['id' => $stroke->id]);
    }

    /** Reposition a stroke/shape/text to new absolute normalized points. */
    public function moveStroke(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate([
            'id'         => ['required', 'integer'],
            'points'     => ['required', 'array', 'min:1'],
            'points.*'   => ['array', 'size:2'],
            'points.*.*' => ['numeric'],
            'width'      => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $stroke = $board->strokes()->where('type', '!=', 'image')->find($data['id']);
        abort_unless($stroke, 404);

        $payload = ['points' => $data['points']];
        if (array_key_exists('width', $data) && $data['width'] !== null) {
            $payload['width'] = $data['width'];
        }
        $stroke->update($payload);

        return response()->json(['ok' => true]);
    }

    /** Delete a single stroke/shape/text. */
    public function deleteStroke(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate(['id' => ['required', 'integer']]);
        $stroke = $board->strokes()->where('type', '!=', 'image')->find($data['id']);
        abort_unless($stroke, 404);
        $stroke->delete();

        return response()->json(['ok' => true]);
    }

    /** Upload an image file and place it on the board as a movable layer. */
    public function uploadImage(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403, 'Завантаження заборонено вчителем.');

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:8192'], // 8 MB
        ]);

        $path = $request->file('image')->store("boards/{$board->id}", 'public');
        $url = Storage::disk('public')->url($path);

        // points = [[x, y], [w, h]] — normalized by board width (consistent with strokes).
        $stroke = $board->strokes()->create([
            'user_id'     => $user->id,
            'author_name' => $user->name,
            'type'        => 'image',
            'color'       => '#000000',
            'width'       => 0,
            'points'      => [[0.08, 0.08], [0.3, 0.3]],
            'text'        => $url,
        ]);

        return response()->json([
            'id'  => $stroke->id,
            'url' => $url,
            'x'   => 0.08,
            'y'   => 0.08,
            'w'   => 0.3,
        ]);
    }

    /** Upload an image file and just return its URL (e.g. flashcard backgrounds). */
    public function uploadAsset(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403, 'Завантаження заборонено вчителем.');

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:8192'],
        ]);

        $path = $request->file('image')->store("boards/{$board->id}/assets", 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    /** Move / resize an existing image layer. */
    public function moveImage(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate([
            'id' => ['required', 'integer'],
            'x'  => ['required', 'numeric'],
            'y'  => ['required', 'numeric'],
            'w'  => ['required', 'numeric', 'min:0.02', 'max:3'],
        ]);

        $image = $board->strokes()->where('type', 'image')->find($data['id']);
        abort_unless($image, 404);

        $h = ($image->points[1][1] ?? $data['w']); // keep stored h slot; client tracks aspect
        $image->update([
            'points' => [[$data['x'], $data['y']], [$data['w'], $h]],
        ]);

        return response()->json(['ok' => true]);
    }

    /** Delete a single image layer. */
    public function deleteImage(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate(['id' => ['required', 'integer']]);
        $image = $board->strokes()->where('type', 'image')->find($data['id']);
        abort_unless($image, 404);

        // Remove the stored file too.
        $path = str_replace(Storage::disk('public')->url(''), '', (string) $image->text);
        Storage::disk('public')->delete($path);
        $image->delete();

        return response()->json(['ok' => true]);
    }

    /* ----------------------- Widgets: frames / quizzes / flashcards / links ----------------------- */

    /** Create a frame, quiz, flashcard or link widget on the board. */
    public function storeWidget(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $base = $request->validate([
            'type' => ['required', 'in:frame,quiz,flashcard,link'],
            'x'    => ['nullable', 'numeric'],
            'y'    => ['nullable', 'numeric'],
            'w'    => ['nullable', 'numeric', 'min:0.05', 'max:3'],
            'h'    => ['nullable', 'numeric', 'min:0.05', 'max:3'],
        ]);

        $data = match ($base['type']) {
            'frame'     => $this->validateFrame($request),
            'quiz'      => $this->validateQuiz($request),
            'flashcard' => $this->validateFlashcard($request),
            'link'      => $this->validateLink($request),
        };

        // Frames are containers/backdrops → go to the BACK; everything else to the front.
        $z = $base['type'] === 'frame'
            ? (int) $board->widgets()->min('z') - 1
            : (int) $board->widgets()->max('z') + 1;

        $widget = $board->widgets()->create([
            'user_id' => $user->id,
            'type'    => $base['type'],
            'z'       => $z,
            'h'       => $base['h'] ?? ($base['type'] === 'frame' ? 0.3 : null),
            'x'       => $base['x'] ?? 0.1,
            'y'       => $base['y'] ?? 0.1,
            'w'       => $base['w'] ?? ($base['type'] === 'frame' ? 0.4 : 0.28),
            'opacity' => 1,
            'data'    => $data,
        ]);

        return response()->json(['id' => $widget->id]);
    }

    /** Update a widget's opacity (0.1 .. 1). */
    public function styleWidget(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate([
            'id'      => ['required', 'integer'],
            'opacity' => ['required', 'numeric', 'min:0.1', 'max:1'],
        ]);

        $widget = $board->widgets()->find($data['id']);
        abort_unless($widget, 404);
        $widget->update(['opacity' => $data['opacity']]);

        return response()->json(['ok' => true]);
    }

    /** Move / resize a widget. */
    public function moveWidget(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate([
            'id' => ['required', 'integer'],
            'x'  => ['required', 'numeric'],
            'y'  => ['required', 'numeric'],
            'w'  => ['required', 'numeric', 'min:0.05', 'max:3'],
            'h'  => ['nullable', 'numeric', 'min:0.05', 'max:3'],
        ]);

        $widget = $board->widgets()->find($data['id']);
        abort_unless($widget, 404);
        $payload = ['x' => $data['x'], 'y' => $data['y'], 'w' => $data['w']];
        if (array_key_exists('h', $data)) {
            $payload['h'] = $data['h'];
        }
        $widget->update($payload);

        return response()->json(['ok' => true]);
    }

    /** Change a widget's stacking order (layer). dir: front|back|forward|backward */
    public function layerWidget(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate([
            'id'  => ['required', 'integer'],
            'dir' => ['required', 'in:front,back,forward,backward'],
        ]);

        $widget = $board->widgets()->find($data['id']);
        abort_unless($widget, 404);

        $min = (int) $board->widgets()->min('z');
        $max = (int) $board->widgets()->max('z');

        $z = match ($data['dir']) {
            'front'    => $max + 1,
            'back'     => $min - 1,
            'forward'  => $widget->z + 1,
            'backward' => $widget->z - 1,
        };
        $widget->update(['z' => $z]);

        return response()->json(['ok' => true, 'z' => $widget->z]);
    }

    /** Delete a widget. */
    public function deleteWidget(Board $board, Request $request)
    {
        $user = auth()->user();
        $isTeacher = $user->id === $board->teacher_id;

        abort_unless($board->isAccessibleBy($user), 403);
        abort_unless($isTeacher || ($board->is_open && $board->students_can_draw), 403);

        $data = $request->validate(['id' => ['required', 'integer']]);
        $widget = $board->widgets()->find($data['id']);
        abort_unless($widget, 404);
        $widget->delete();

        return response()->json(['ok' => true]);
    }

    /** Check a student's answer to a quiz widget (answer kept server-side). */
    public function checkQuiz(Board $board, Request $request)
    {
        abort_unless($board->isAccessibleBy(auth()->user()), 403);

        $data = $request->validate([
            'id'     => ['required', 'integer'],
            'choice' => ['required', 'integer', 'min:0'],
        ]);

        $widget = $board->widgets()->where('type', 'quiz')->find($data['id']);
        abort_unless($widget, 404);

        $answer = (int) ($widget->data['answer'] ?? -1);

        return response()->json([
            'correct' => $data['choice'] === $answer,
            'answer'  => $answer,
        ]);
    }

    /** Frame: a resizable rectangle with a background (color or image) + title. */
    private function validateFrame(Request $request): array
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:16'],
            'bg'    => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        return [
            'title' => $request->input('title') ?: null,
            'color' => $request->input('color') ?: '#ffffff',
            'bg'    => $request->input('bg') ?: null,
        ];
    }

    private function validateQuiz(Request $request): array
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

        return ['question' => $request->input('question'), 'options' => $options, 'answer' => $answer];
    }

    private function validateFlashcard(Request $request): array
    {
        $request->validate([
            'front'    => ['required', 'string', 'max:500'],
            'back'     => ['required', 'string', 'max:500'],
            'bg_front' => ['nullable', 'url:http,https', 'max:2048'],
            'bg_back'  => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        return [
            'front'    => $request->input('front'),
            'back'     => $request->input('back'),
            'bg_front' => $request->input('bg_front') ?: null,
            'bg_back'  => $request->input('bg_back') ?: null,
        ];
    }

    /** Link/button widget pointing to another board (by token) or any URL. */
    private function validateLink(Request $request): array
    {
        $request->validate([
            'label'       => ['required', 'string', 'max:120'],
            'board_token' => ['nullable', 'string', 'max:64'],
            // Restrict to http/https so a `javascript:` URL can't be injected into an href.
            'url'         => ['nullable', 'url:http,https', 'max:2048'],
            'style'       => ['nullable', 'in:primary,secondary'],
        ]);

        $target = null;
        // Prefer linking to one of the teacher's own boards.
        if ($token = $request->input('board_token')) {
            $dest = Board::where('token', $token)->where('teacher_id', auth()->id())->first();
            abort_unless($dest, 422, 'Дошку для посилання не знайдено.');
            $target = route('board.show', $dest);
        } elseif ($url = $request->input('url')) {
            $target = $url;
        } else {
            abort(422, 'Вкажіть дошку або URL для посилання.');
        }

        return [
            'label' => $request->input('label'),
            'url'   => $target,
            'style' => $request->input('style', 'primary'),
        ];
    }

    /** Teacher toggles whether students may draw. */
    public function permission(Board $board, Request $request)
    {
        abort_unless($this->isOwner($board), 403);

        $board->update(['students_can_draw' => $request->boolean('allow')]);

        return response()->json(['students_can_draw' => $board->students_can_draw]);
    }

    /** Teacher clears the board (signals all clients via cleared_at). */
    public function clear(Board $board)
    {
        abort_unless($this->isOwner($board), 403);

        // Remove uploaded image files for this board, then wipe all strokes & widgets.
        Storage::disk('public')->deleteDirectory("boards/{$board->id}");
        $board->strokes()->delete();
        $board->widgets()->delete();
        $board->update(['cleared_at' => now()]);

        return response()->json(['ok' => true, 'cleared_at' => $board->cleared_at->toISOString()]);
    }

    /** Teacher invites a registered student to the board. */
    public function invite(Board $board, Request $request)
    {
        abort_unless($this->isOwner($board), 403);

        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::role(Role::Student)->find($data['student_id']);
        abort_unless($student, 422, 'Це не учень.');

        $board->invitedStudents()->syncWithoutDetaching([$student->id]);

        if ($request->expectsJson()) {
            return response()->json(['student' => ['id' => $student->id, 'name' => $student->name]]);
        }

        return back()->with('status', "Учня {$student->name} запрошено до дошки.");
    }

    /** Teacher removes a student's access. */
    public function uninvite(Board $board, User $user, Request $request)
    {
        abort_unless($this->isOwner($board), 403);

        $board->invitedStudents()->detach($user->id);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Доступ учня скасовано.');
    }

    private function isOwner(Board $board): bool
    {
        return auth()->check() && auth()->id() === $board->teacher_id;
    }

    /** Students in classes linked to this teacher (homeroom or subject). */
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

        // Demo fallback: small dataset with no class links yet.
        return $students->isEmpty()
            ? User::role(Role::Student)->orderBy('name')->get()
            : $students;
    }
}
