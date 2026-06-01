@props(['progress'])

<div class="d-flex align-items-center gap-3">

    <div class="{{ $progress['assigned'] ? 'text-success' : 'text-muted' }}">
        ● Assigned
    </div>

    <div>→</div>

    <div class="{{ $progress['in_progress'] ? 'text-warning' : 'text-muted' }}">
        ● In Progress
    </div>

    <div>→</div>

    <div class="{{ $progress['completed'] ? 'text-success' : 'text-muted' }}">
        ● Completed
    </div>

</div>