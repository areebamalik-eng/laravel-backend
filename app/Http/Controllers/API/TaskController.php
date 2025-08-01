<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // ✅ GET /api/tasks – Show current user's own + shared group tasks
    public function index()
    {
        $groupCode = auth()->user()->group_code;
        $userId = auth()->id();

        $tasks = Task::with('user')
            ->where(function ($query) use ($groupCode, $userId) {
                $query->where('user_id', $userId)
                    ->orWhere(function ($subQuery) use ($groupCode, $userId) {
                        $subQuery->where('is_shared', true)
                            ->whereHas('user', function ($q) use ($groupCode, $userId) {
                                $q->where('group_code', $groupCode)
                                    ->where('id', '!=', $userId);
                            });
                    });
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($tasks, 200);
    }

    // ✅ POST /api/tasks – Create a new task (with notifications)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i:s',
            'is_shared' => 'boolean',
        ]);

        $task = Task::create([
            'user_id' => auth()->id(),
            'group_code' => auth()->user()->group_code,
            'name' => $request->name,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'is_shared' => $request->is_shared ?? false,
        ]);

        // ✅ Send notifications to group members (excluding self)
        if ($task->is_shared) {
            $groupCode = auth()->user()->group_code;
            $senderName = auth()->user()->name;

            $groupMembers = User::where('group_code', $groupCode)
                                ->where('id', '!=', auth()->id())
                                ->get();

            foreach ($groupMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'task',
                    'resource_id' => $task->id,
                    'message' => "$senderName added a new shared task: {$task->name}",
                    'is_read' => false,
                ]);
            }
        }

        return response()->json($task, 201);
    }

    // ✅ GET /api/tasks/{id}
    public function show(string $id)
    {
        $groupCode = auth()->user()->group_code;

        $task = Task::with('user')
            ->where('id', $id)
            ->whereHas('user', function ($q) use ($groupCode) {
                $q->where('group_code', $groupCode);
            })
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task, 200);
    }

    // ✅ PUT /api/tasks/{id}
    public function update(Request $request, string $id)
    {
        $groupCode = auth()->user()->group_code;

        $task = Task::where('id', $id)
            ->whereHas('user', function ($q) use ($groupCode) {
                $q->where('group_code', $groupCode);
            })
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i:s',
            'is_shared' => 'boolean',
        ]);

        $task->update([
            'name' => $request->name,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'is_shared' => $request->is_shared ?? false,
        ]);

        return response()->json($task, 200);
    }

    // ✅ DELETE /api/tasks/{id}
    public function destroy(string $id)
    {
        $groupCode = auth()->user()->group_code;

        $task = Task::where('id', $id)
            ->whereHas('user', function ($q) use ($groupCode) {
                $q->where('group_code', $groupCode);
            })
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted'], 200);
    }

    // ✅ PATCH /api/tasks/{id}/toggle – Toggle task done status
    public function toggleDone($id)
    {
        $groupCode = auth()->user()->group_code;

        $task = Task::where('id', $id)
            ->whereHas('user', function ($q) use ($groupCode) {
                $q->where('group_code', $groupCode);
            })
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->is_done = !$task->is_done;
        $task->save();

        return response()->json($task, 200);
    }
}
