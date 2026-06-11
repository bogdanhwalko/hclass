<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Board;
use App\Models\ContentBlock;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Admin ----
        User::updateOrCreate(
            ['email' => 'admin@hclass.test'],
            ['name' => 'Олена Адмін', 'password' => Hash::make('password'), 'role' => Role::Admin]
        );

        // ---- Subjects ----
        $subjectNames = [
            'Математика' => 'MATH',
            'Українська мова' => 'UKR',
            'Англійська мова' => 'ENG',
            'Історія' => 'HIST',
            'Фізика' => 'PHYS',
            'Біологія' => 'BIO',
            'Інформатика' => 'INF',
        ];
        $subjects = collect($subjectNames)->map(fn ($code, $name) =>
            Subject::updateOrCreate(['name' => $name], ['code' => $code])
        )->values();

        // ---- Teachers ----
        $teachers = collect([
            'Ірина Коваль' => 'teacher1@hclass.test',
            'Андрій Шевченко' => 'teacher2@hclass.test',
            'Марія Бондаренко' => 'teacher3@hclass.test',
        ])->map(fn ($email, $name) => User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password'), 'role' => Role::Teacher]
        ))->values();

        // ---- Classes ----
        $class5a = SchoolClass::updateOrCreate(
            ['name' => '5-А'],
            ['grade_level' => 5, 'homeroom_teacher_id' => $teachers[0]->id]
        );
        $class6b = SchoolClass::updateOrCreate(
            ['name' => '6-Б'],
            ['grade_level' => 6, 'homeroom_teacher_id' => $teachers[1]->id]
        );

        // Attach subjects to classes with a teacher
        foreach ([$class5a, $class6b] as $class) {
            $subjects->take(5)->each(function ($subject, $i) use ($class, $teachers) {
                $class->subjects()->syncWithoutDetaching([
                    $subject->id => ['teacher_id' => $teachers[$i % $teachers->count()]->id],
                ]);
            });
        }

        // ---- Students ----
        $studentsData = [
            ['Назар Левчук', 'student1@hclass.test', $class5a],
            ['Софія Мельник', 'student2@hclass.test', $class5a],
            ['Дмитро Ткаченко', 'student3@hclass.test', $class6b],
            ['Вікторія Гнатюк', 'student4@hclass.test', $class6b],
        ];

        foreach ($studentsData as [$name, $email, $class]) {
            $student = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'), 'role' => Role::Student]
            );
            $class->students()->syncWithoutDetaching([$student->id]);
        }

        // ---- Parent linked to first two students ----
        $parent = User::updateOrCreate(
            ['email' => 'parent@hclass.test'],
            ['name' => 'Петро Левчук', 'password' => Hash::make('password'), 'role' => Role::Parent]
        );
        $childrenIds = User::role(Role::Student)->take(2)->pluck('id');
        $parent->children()->syncWithoutDetaching($childrenIds);

        // ---- Demo lesson with an interactive board ----
        $firstStudent = User::role(Role::Student)->orderBy('id')->first();
        $math = $subjects->firstWhere('code', 'MATH');

        $board = Board::updateOrCreate(
            ['title' => 'Дошка: Вступний урок з математики'],
            ['token' => Str::random(24), 'teacher_id' => $teachers[0]->id, 'students_can_draw' => false]
        );

        // Invite the assigned student so they have access.
        if ($firstStudent) {
            $board->invitedStudents()->syncWithoutDetaching([$firstStudent->id]);
        }

        Lesson::updateOrCreate(
            ['teacher_id' => $teachers[0]->id, 'student_id' => $firstStudent?->id, 'title' => 'Вступний урок з математики'],
            [
                'subject_id'   => $math?->id,
                'board_id'     => $board->id,
                'description'  => 'Знайомство, повторення таблиці множення, робота на інтерактивній дошці.',
                'scheduled_at' => now()->addDay()->setTime(14, 0),
                'duration_min' => 45,
                'status'       => 'planned',
            ]
        );

        // ---- Demo published course with content blocks ----
        $course = Course::updateOrCreate(
            ['slug' => 'osnovy-matematyky'],
            [
                'teacher_id'  => $teachers[0]->id,
                'subject_id'  => $math?->id,
                'title'       => 'Основи математики',
                'summary'     => 'Базовий курс для початківців: числа, дроби та прості рівняння.',
                'emoji'       => '➗',
                'cover_color' => 'indigo',
                'is_published' => true,
            ]
        );

        if ($course->lessons()->count() === 0) {
            $m1 = $course->lessons()->create(['title' => 'Знайомство з дробами', 'position' => 1]);
            $m1->blocks()->create(['type' => 'text', 'position' => 1, 'data' => [
                'text' => "Дроб — це частина цілого. Наприклад, якщо розрізати піцу на 4 рівні частини й узяти 1 шматок, отримаємо 1/4 піци.\n\nЧисло зверху (чисельник) показує, скільки частин ми беремо, а число знизу (знаменник) — на скільки частин поділено ціле.",
            ]]);
            $m1->blocks()->create(['type' => 'image', 'position' => 2, 'data' => [
                'url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Cake_fractions.svg/512px-Cake_fractions.svg.png',
                'caption' => 'Візуалізація дробів на прикладі торта',
            ]]);
            $m1->blocks()->create(['type' => 'quiz', 'position' => 3, 'data' => [
                'question' => 'Скільки буде 1/2 + 1/4?',
                'options'  => ['3/4', '2/6', '1/8', '2/4'],
                'answer'   => 0,
            ]]);

            $m2 = $course->lessons()->create(['title' => 'Прості рівняння', 'position' => 2]);
            $m2->blocks()->create(['type' => 'text', 'position' => 1, 'data' => [
                'text' => "Рівняння — це рівність із невідомим. Щоб розв'язати x + 3 = 7, віднімаємо 3 з обох боків: x = 4.",
            ]]);
            $m2->blocks()->create(['type' => 'quiz', 'position' => 2, 'data' => [
                'question' => 'Чому дорівнює x у рівнянні x + 5 = 12?',
                'options'  => ['5', '7', '17', '6'],
                'answer'   => 1,
            ]]);
            $m2->blocks()->create(['type' => 'button', 'position' => 3, 'data' => [
                'label' => 'Додаткові задачі (PDF)',
                'url'   => 'https://example.com/math-tasks.pdf',
                'style' => 'primary',
            ]]);
        }

        if ($firstStudent) {
            $course->students()->syncWithoutDetaching([$firstStudent->id]);
        }
    }
}
