<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardManagerController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PortalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Calendar — available to every role; access control handled in the controller.
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    /* --- Interactive whiteboard: only the owning teacher or invited registered students --- */
    Route::prefix('board/{board}')->name('board.')->group(function () {
        Route::get('/', [BoardController::class, 'show'])->name('show');
        Route::get('strokes', [BoardController::class, 'strokes'])->name('strokes');
        Route::post('draw', [BoardController::class, 'draw'])->name('draw');
        Route::post('stroke/move', [BoardController::class, 'moveStroke'])->name('stroke.move');
        Route::post('stroke/delete', [BoardController::class, 'deleteStroke'])->name('stroke.delete');
        Route::post('image', [BoardController::class, 'uploadImage'])->name('image.upload');
        Route::post('asset', [BoardController::class, 'uploadAsset'])->name('asset.upload');
        Route::post('image/move', [BoardController::class, 'moveImage'])->name('image.move');
        Route::post('image/delete', [BoardController::class, 'deleteImage'])->name('image.delete');
        Route::post('widget', [BoardController::class, 'storeWidget'])->name('widget.store');
        Route::post('widget/move', [BoardController::class, 'moveWidget'])->name('widget.move');
        Route::post('widget/style', [BoardController::class, 'styleWidget'])->name('widget.style');
        Route::post('widget/layer', [BoardController::class, 'layerWidget'])->name('widget.layer');
        Route::post('widget/delete', [BoardController::class, 'deleteWidget'])->name('widget.delete');
        Route::post('widget/check', [BoardController::class, 'checkQuiz'])->name('widget.check');
        Route::post('permission', [BoardController::class, 'permission'])->name('permission');
        Route::post('clear', [BoardController::class, 'clear'])->name('clear');
        Route::post('invite', [BoardController::class, 'invite'])->name('invite');
        Route::delete('invite/{user}', [BoardController::class, 'uninvite'])->name('uninvite');
    });

    Route::view('profile', 'profile')->name('profile');

    // Explicit named logout (app-shell uses a POST form to route('logout')).
    Route::post('logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');

    /* ----------------------------- Admin ----------------------------- */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [AdminController::class, 'users'])->name('users');
        Route::post('users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::patch('users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        Route::get('classes', [AdminController::class, 'classes'])->name('classes');
        Route::post('classes', [AdminController::class, 'storeClass'])->name('classes.store');
        Route::delete('classes/{class}', [AdminController::class, 'destroyClass'])->name('classes.destroy');
        Route::post('classes/{class}/students', [AdminController::class, 'attachStudent'])->name('classes.students.attach');
        Route::post('classes/{class}/subjects', [AdminController::class, 'attachSubject'])->name('classes.subjects.attach');

        Route::get('subjects', [AdminController::class, 'subjects'])->name('subjects');
        Route::post('subjects', [AdminController::class, 'storeSubject'])->name('subjects.store');
        Route::delete('subjects/{subject}', [AdminController::class, 'destroySubject'])->name('subjects.destroy');
    });

    /* ---------------------------- Teacher ---------------------------- */
    Route::middleware('role:teacher')->group(function () {
        Route::get('teacher/classes', [PortalController::class, 'teacherClasses'])->name('teacher.classes');

        // Board manager (saved boards + groups)
        Route::get('teacher/boards', [BoardManagerController::class, 'index'])->name('teacher.boards');
        Route::post('teacher/boards', [BoardManagerController::class, 'store'])->name('teacher.boards.store');
        Route::patch('teacher/boards/{board}', [BoardManagerController::class, 'update'])->name('teacher.boards.update');
        Route::delete('teacher/boards/{board}', [BoardManagerController::class, 'destroy'])->name('teacher.boards.destroy');
        Route::post('teacher/board-groups', [BoardManagerController::class, 'storeGroup'])->name('teacher.board-groups.store');
        Route::get('teacher/board-groups/{group}/present', [BoardManagerController::class, 'present'])->name('teacher.board-groups.present');
        Route::delete('teacher/board-groups/{group}', [BoardManagerController::class, 'destroyGroup'])->name('teacher.board-groups.destroy');
        Route::post('teacher/board-groups/{group}/members', [BoardManagerController::class, 'inviteToGroup'])->name('teacher.board-groups.invite');
        Route::delete('teacher/board-groups/{group}/members/{user}', [BoardManagerController::class, 'uninviteFromGroup'])->name('teacher.board-groups.uninvite');

        Route::get('teacher/lessons', [LessonController::class, 'index'])->name('teacher.lessons');
        Route::post('teacher/lessons', [LessonController::class, 'store'])->name('teacher.lessons.store');
        Route::patch('teacher/lessons/{lesson}', [LessonController::class, 'update'])->name('teacher.lessons.update');
        Route::delete('teacher/lessons/{lesson}', [LessonController::class, 'destroy'])->name('teacher.lessons.destroy');
        Route::post('teacher/lessons/{lesson}/board', [LessonController::class, 'attachBoard'])->name('teacher.lessons.board');
        Route::post('teacher/lessons/{lesson}/board-back', [LessonController::class, 'attachBoardBack'])->name('teacher.lessons.board-back');

        // Courses (builder)
        Route::get('teacher/courses', [CourseController::class, 'index'])->name('teacher.courses');
        Route::post('teacher/courses', [CourseController::class, 'store'])->name('teacher.courses.store');
        Route::get('teacher/courses/{course}/edit', [CourseController::class, 'edit'])->name('teacher.courses.edit');
        Route::get('teacher/courses/{course}/preview', [CourseController::class, 'preview'])->name('teacher.courses.preview');
        Route::patch('teacher/courses/{course}', [CourseController::class, 'update'])->name('teacher.courses.update');
        Route::delete('teacher/courses/{course}', [CourseController::class, 'destroy'])->name('teacher.courses.destroy');
        Route::post('teacher/courses/{course}/lessons', [CourseController::class, 'storeLesson'])->name('teacher.courses.lessons.store');
        Route::delete('teacher/courses/{course}/lessons/{lesson}', [CourseController::class, 'destroyLesson'])->name('teacher.courses.lessons.destroy');
        Route::post('teacher/courses/{course}/lessons/{lesson}/blocks', [CourseController::class, 'storeBlock'])->name('teacher.courses.blocks.store');
        Route::delete('teacher/courses/{course}/lessons/{lesson}/blocks/{block}', [CourseController::class, 'destroyBlock'])->name('teacher.courses.blocks.destroy');
    });

    /* ---------------------------- Student ---------------------------- */
    Route::middleware('role:student')->group(function () {
        Route::get('student/subjects', [PortalController::class, 'studentSubjects'])->name('student.subjects');
        Route::get('student/lessons', [LessonController::class, 'studentIndex'])->name('student.lessons');
        Route::get('student/courses', [CourseController::class, 'studentIndex'])->name('student.courses');
        Route::get('student/courses/{course}', [CourseController::class, 'show'])->name('student.courses.show');
    });

    /* ----------------------------- Parent ---------------------------- */
    Route::middleware('role:parent')->group(function () {
        Route::get('parent/children', [PortalController::class, 'parentChildren'])->name('parent.children');
    });
});

require __DIR__.'/auth.php';
