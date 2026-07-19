<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display the task checklist.
     */
    public function index()
    {
        $wedding = Auth::user()->wedding;

        // Statistics
        $totalTasks = $wedding->tasks()->count();
        $completedTasks = $wedding->tasks()->where('is_completed', true)->count();
        $progressPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Tasks list ordered by incomplete first
        $tasks = $wedding->tasks()
            ->orderBy('is_completed', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return view('tasks.index', compact('tasks', 'totalTasks', 'completedTasks', 'progressPct'));
    }

    /**
     * Store a new task.
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
        ]);

        $wedding = Auth::user()->wedding;
        
        $wedding->tasks()->create([
            'task_name' => trim($request->task_name),
        ]);

        return redirect()->route('tasks.index')->with('status', 'Task added successfully!');
    }

    /**
     * Toggle the task status (Complete / Incomplete).
     */
    public function toggle(Task $task)
    {
        // Security check: make sure this task belongs to the user's wedding
        if ($task->wedding_id !== Auth::user()->wedding->id) {
            abort(403, 'Unauthorized action.');
        }

        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        return redirect()->route('tasks.index');
    }

    /**
     * Delete the task.
     */
    public function destroy(Task $task)
    {
        // Security check
        if ($task->wedding_id !== Auth::user()->wedding->id) {
            abort(403, 'Unauthorized action.');
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Task deleted successfully!');
    }
}