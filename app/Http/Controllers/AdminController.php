<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /* ------------------------------- Users -------------------------------- */

    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users', ['users' => $users, 'roles' => Role::options()]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'      => ['required', 'in:'.implode(',', Role::values())],
            'phone'     => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
            'password'  => ['required', 'confirmed', Password::defaults()],
        ]);
        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        User::create($data);

        return back()->with('status', "Користувача «{$data['name']}» створено.");
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role'      => ['required', 'in:'.implode(',', Role::values())],
            'phone'     => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
            'password'  => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Don't let an admin lock themselves out / strip their own admin role.
        $isSelf = $user->id === auth()->id();

        $user->name      = $data['name'];
        $user->email     = $data['email'];
        $user->role      = $isSelf ? $user->role->value : $data['role'];
        $user->phone     = $data['phone'] ?? null;
        $user->is_active = $isSelf ? true : $request->boolean('is_active');
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('status', "Дані користувача «{$user->name}» оновлено.");
    }

    public function destroyUser(User $user)
    {
        abort_if($user->id === auth()->id(), 403);
        $user->delete();

        return back()->with('status', 'Користувача видалено.');
    }

    /* ------------------------------ Classes ------------------------------- */

    public function classes()
    {
        return view('admin.classes', [
            'classes'  => SchoolClass::withCount('students')->with('homeroomTeacher', 'subjects', 'students')->latest()->get(),
            'teachers' => User::role(Role::Teacher)->orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'students' => User::role(Role::Student)->orderBy('name')->get(),
        ]);
    }

    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
        ]);
        SchoolClass::create($data);

        return back()->with('status', 'Клас створено.');
    }

    public function attachStudent(Request $request, SchoolClass $class)
    {
        $request->validate(['student_id' => ['required', 'exists:users,id']]);
        $class->students()->syncWithoutDetaching([$request->student_id]);

        return back()->with('status', 'Учня додано до класу.');
    }

    public function attachSubject(Request $request, SchoolClass $class)
    {
        $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ]);
        $class->subjects()->syncWithoutDetaching([
            $request->subject_id => ['teacher_id' => $request->teacher_id],
        ]);

        return back()->with('status', 'Предмет додано до класу.');
    }

    public function destroyClass(SchoolClass $class)
    {
        $class->delete();

        return back()->with('status', 'Клас видалено.');
    }

    /* ------------------------------ Subjects ------------------------------ */

    public function subjects()
    {
        return view('admin.subjects', [
            'subjects' => Subject::withCount('classes')->latest()->get(),
        ]);
    }

    public function storeSubject(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20', 'unique:subjects,code'],
            'description' => ['nullable', 'string'],
        ]);
        Subject::create($data);

        return back()->with('status', 'Предмет створено.');
    }

    public function destroySubject(Subject $subject)
    {
        $subject->delete();

        return back()->with('status', 'Предмет видалено.');
    }
}
