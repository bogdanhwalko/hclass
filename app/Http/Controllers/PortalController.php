<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    public function teacherClasses()
    {
        $user = auth()->user();

        $classes = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->orWhereHas('subjects', fn ($q) => $q->where('teacher_id', $user->id))
            ->withCount('students')
            ->with(['students', 'subjects'])
            ->get();

        return view('portal.teacher-classes', compact('classes'));
    }

    public function studentSubjects()
    {
        $user = auth()->user();
        $classIds = $user->classes()->pluck('school_classes.id');

        $subjects = DB::table('class_subject')
            ->join('subjects', 'subjects.id', '=', 'class_subject.subject_id')
            ->leftJoin('users', 'users.id', '=', 'class_subject.teacher_id')
            ->whereIn('class_subject.school_class_id', $classIds)
            ->select('subjects.name', 'subjects.code', 'subjects.description', 'users.name as teacher_name')
            ->get();

        return view('portal.student-subjects', compact('subjects'));
    }

    public function parentChildren()
    {
        $children = auth()->user()->children()
            ->with(['classes.homeroomTeacher', 'classes.subjects'])
            ->get();

        return view('portal.parent-children', compact('children'));
    }
}
