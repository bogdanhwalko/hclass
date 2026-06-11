<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return match (true) {
            $user->isAdmin()   => $this->admin(),
            $user->isTeacher() => $this->teacher($user),
            $user->isStudent() => $this->student($user),
            $user->isParent()  => $this->parent($user),
            default            => abort(403),
        };
    }

    private function admin()
    {
        return view('dashboards.admin', [
            'stats' => [
                'students' => User::role(Role::Student)->count(),
                'teachers' => User::role(Role::Teacher)->count(),
                'parents'  => User::role(Role::Parent)->count(),
                'classes'  => SchoolClass::count(),
            ],
            'recentUsers' => User::latest()->take(5)->get(),
            'classes'     => SchoolClass::withCount('students')->with('homeroomTeacher')->latest()->take(5)->get(),
        ]);
    }

    private function teacher(User $user)
    {
        $classes = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->orWhereHas('subjects', fn ($q) => $q->where('teacher_id', $user->id))
            ->withCount('students')
            ->with('subjects')
            ->get();

        return view('dashboards.teacher', [
            'classes' => $classes,
            'stats' => [
                'homeroom' => $user->homeroomClasses()->count(),
                'subjects' => DB::table('class_subject')->where('teacher_id', $user->id)->distinct('subject_id')->count('subject_id'),
                'students' => $classes->sum('students_count'),
            ],
        ]);
    }

    private function student(User $user)
    {
        $classes = $user->classes()->with('homeroomTeacher')->get();
        $classIds = $classes->pluck('id');

        $subjects = DB::table('class_subject')
            ->join('subjects', 'subjects.id', '=', 'class_subject.subject_id')
            ->leftJoin('users', 'users.id', '=', 'class_subject.teacher_id')
            ->whereIn('class_subject.school_class_id', $classIds)
            ->select('subjects.name', 'subjects.code', 'users.name as teacher_name')
            ->get();

        return view('dashboards.student', compact('classes', 'subjects'));
    }

    private function parent(User $user)
    {
        $children = $user->children()
            ->with(['classes.homeroomTeacher', 'classes.subjects'])
            ->get();

        return view('dashboards.parent', compact('children'));
    }
}
