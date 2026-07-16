<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $teachers = Teacher::with('user')
            ->withCount(['advisedSections', 'subjectAssignments'])
            ->orderBy('last_name')->orderBy('first_name')
            ->paginate(20);

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return $this->index()->with('openModal', 'create');
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $teacher = DB::transaction(function () use ($data, $request) {
            $teacher = Teacher::create($this->teacherAttributes($data));

            if ($request->boolean('create_account')) {
                $user = User::create([
                    'name' => trim("{$teacher->first_name} {$teacher->last_name}"),
                    'email' => $data['account_email'],
                    'password' => Hash::make($data['account_password']),
                    'role' => User::ROLE_TEACHER,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $teacher->update(['user_id' => $user->id]);
            }

            return $teacher;
        });

        $this->audit->created($teacher, "Teacher {$teacher->last_name} created");

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher created.');
    }

    public function edit(Teacher $teacher)
    {
        return $this->index()->with(['openModal' => 'edit', 'editModel' => $teacher->load('user')]);
    }

    /** Grant or revoke owner-comped 100% free access on the linked account. */
    public function toggleFreeAccess(Teacher $teacher): RedirectResponse
    {
        $user = $teacher->user;
        abort_unless($user !== null, 404, 'This teacher has no login account.');

        $user->update(['free_access' => ! $user->free_access]);
        $verb = $user->free_access ? 'granted' : 'revoked';
        $this->audit->log('free_access_'.$verb, $user, "Free access {$verb} for {$user->email}");

        return back()->with('success', $user->free_access
            ? "{$user->name} now has 100% free access — no subscription needed."
            : "Free access revoked for {$user->name}; their normal trial/subscription state applies again.");
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validated();
        $original = $teacher->getOriginal();

        DB::transaction(function () use ($teacher, $data, $request) {
            $teacher->update($this->teacherAttributes($data));

            if ($request->boolean('create_account')) {
                if ($teacher->user) {
                    $teacher->user->update(array_filter([
                        'email' => $data['account_email'] ?? null,
                        'password' => ! empty($data['account_password']) ? Hash::make($data['account_password']) : null,
                    ]));
                } else {
                    $user = User::create([
                        'name' => trim("{$teacher->first_name} {$teacher->last_name}"),
                        'email' => $data['account_email'],
                        'password' => Hash::make($data['account_password']),
                        'role' => User::ROLE_TEACHER,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);
                    $teacher->update(['user_id' => $user->id]);
                }
            }
        });

        $this->audit->updated($teacher, $original);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);
        $name = "{$teacher->last_name}, {$teacher->first_name}";
        $teacher->delete();
        $this->audit->deleted($teacher, "Teacher {$name} deleted");

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deleted.');
    }

    /** @return array<string, mixed> */
    private function teacherAttributes(array $data): array
    {
        return collect($data)->only([
            'first_name', 'middle_name', 'last_name', 'suffix',
            'gender', 'employee_no', 'email', 'contact', 'is_active',
        ])->all();
    }
}
