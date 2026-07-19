<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Checklist — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/checklist.css') }}">
    </x-slot>

    <div class="row g-3">
        <!-- Left Column: Progress + Add Form -->
        <div class="col-lg-4">
            <!-- Progress Card -->
            <div class="card progress-card">
                <div class="progress-pct">{{ $progressPct }}<span>%</span></div>
                <p class="progress-label">Wedding planning complete</p>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $progressPct }}%;"></div>
                </div>
                <p class="progress-sub">{{ $completedTasks }} of {{ $totalTasks }} tasks done</p>
            </div>

            <!-- Add Task Card -->
            <div class="card add-task-card">
                <h5>Add a Task</h5>
                <form method="POST" action="{{ route('tasks.store') }}" class="add-task-form">
                    @csrf
                    <input type="text" name="task_name" class="add-task-input"
                        id="task-input" placeholder="What needs to be done?" required>
                    <button type="submit" class="btn-add-task">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
                
                <!-- Quick suggestions list -->
                <div class="suggestions">
                    @php
                        $suggestions = ['Book photographer', 'Book caterer', 'Saree fitting', 'Send invitations', 'Book florist', 'Thank-you cards', 'Confirm venue', 'Book DJ / Band', 'Order cake', 'Hair & makeup trial'];
                    @endphp
                    @foreach ($suggestions as $s)
                    <button class="suggestion-chip" onclick="document.getElementById('task-input').value='{{ $s }}'">
                        {{ $s }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Task List -->
        <div class="col-lg-8">
            <div class="card tasks-card">
                <div class="tasks-card-header">
                    <h5>Your Tasks ({{ $totalTasks }})</h5>
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="filterTasks('all', this)">All</button>
                        <button class="filter-tab" onclick="filterTasks('pending', this)">Pending</button>
                        <button class="filter-tab" onclick="filterTasks('done', this)">Done</button>
                    </div>
                </div>

                @if ($tasks->count() > 0)
                <ul class="task-list" id="task-list">
                    @foreach ($tasks as $task)
                    <li class="task-item" data-status="{{ $task->is_completed ? 'done' : 'pending' }}">
                        
                        <!-- Toggle Task Form (Laravel Standard PATCH) -->
                        <form action="{{ route('tasks.toggle', $task) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="task-toggle {{ $task->is_completed ? 'done' : '' }}">
                                @if ($task->is_completed)
                                    <i class="fas fa-check"></i>
                                @endif
                            </button>
                        </form>

                        <span class="task-name {{ $task->is_completed ? 'done' : '' }}">
                            {{ $task->task_name }}
                        </span>

                        <!-- Delete Task Form (Laravel Standard DELETE) -->
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remove this task?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="task-del">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="empty-tasks">
                    <i class="fas fa-tasks"></i>
                    <p>No tasks yet. Add your first task to start tracking your wedding planning!</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <script>
        function filterTasks(filter, btn) {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('#task-list .task-item').forEach(item => {
                const s = item.dataset.status;
                item.style.display = (filter === 'all' || s === filter) ? '' : 'none';
            });
        }
        </script>
    </x-slot>

</x-app-layout>