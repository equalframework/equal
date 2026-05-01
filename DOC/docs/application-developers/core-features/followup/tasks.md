

# Followup Tasks



Followup Tasks provide a flexible way to track, organize, and automate operational activities around key business events (e.g., booking confirmation, contract signing).
 They are used to:

- Remind staff about pending actions.
- Verify the validity of a booking.
- Anticipate internal organization needs.
- Notify or confirm with external providers.

The system is based on three core models:

- **Task**: a single actionable item.
- **TaskModel**: a template that defines when and how tasks are created.
- **TaskEvent**: an event (status change or date offset) that triggers or constrains tasks.



## TaskEvent

A **TaskEvent** represents a point in time where a task becomes relevant or due.
 It can be based on:

- **Status changes** in an entity workflow (e.g., booking → *confirmed*).
- **Date fields** with optional offsets (e.g., *15 days before arrival*).

### Fields

- `name` — Human-readable event label.
- `object_class` — Namespace of the associated entity (e.g., `sale\booking\Booking`).
- `event_type` — `status_change` or `date_field`.
- `entity_status` — Status value, if event type is `status_change`.
- `entity_date_field` — Date field name, if event type is `date_field`.
- `offset` — Number of days relative to the event (default `0`).

**Examples**

- `confirmation`
- `confirmation +15 days`
- `1 month before reservation`
- `15 days before reservation`



## TaskModel

A **TaskModel** defines a reusable **template** for tasks. It specifies **when a task should appear** and **when it must be completed at the latest**.

### Characteristics

- Bound to a specific entity type (`object_class`).
- Defines **trigger** and **deadline** events.
- Can be grouped into `task_group` for organizational purposes.
- Automatically spawns tasks when the trigger condition is met.

### Fields

- `name` — Template label.
- `description` — Text description.
- `task_group` — Group name for filtering tasks.
- `trigger_event_id` — Event that activates the task.
- `deadline_event_id` — Event that sets the latest completion date.
- `tasks_ids` — Generated tasks linked to this template.

**Example**

- Task Model *“Send Allergy List”*
  - Trigger: Booking → *confirmed*.
  - Deadline: *15 days before arrival*.



## Task

A **Task** is an actionable item derived either manually or automatically from a TaskModel.

### Types of Tasks

- **Informational** — automatically marked as done (e.g., *“contract received signed”*).
- **Actionable** — requires responsibility (*“Who must do what?”*) or communication (*“Who must be informed?”*).

### Fields

- `id` — Identifier.
- `name` — Label describing the action.
- `is_done` — Boolean flag.
- `done_by` — User who completed the task.
- `done_date` — Completion date.
- `visible_date` — When the task becomes visible.
- `deadline_date` — Calculated deadline (from TaskEvent).
- `has_task_model` — Whether linked to a TaskModel.
- `task_model_id` — Originating model (nullable if created manually).
- `notes` — Free text notes.
- `trigger_event_id` / `deadline_event_id` — Event references.
- `object_class` / `object_id` — Linked entity reference.

### Sorting & Display

- **Priority**: non-completed tasks first.
- **Order**: by `deadline_date`.
- **Completed tasks**: sorted by `done_date` or deadline order.
- **Grouping**: possible by deadline event.



## Task Lifecycle

### Creation

A task line is created when:

1. **Manual creation**: arbitrary, via code or user action.
2. **Automatic creation**: when a TaskModel’s trigger condition is met.

👉 **Constraint**: there can only be **one task line per TaskModel per object**. If one already exists, it is removed and replaced.

### Execution Logic

On each status change of an entity (e.g., `Booking`):

1. Fetch all **TaskModels** relevant for the management team.
2. Compare the entity’s status with the **trigger_event_id**.
3. If matching, create a new Task line.
4. Remove any previous task from the same TaskModel.



## Example Scenarios

### Example: Valrance

1. **Confirmed**
   - Receive contract.
   - Send access codes.
   - Prepare stay binder.
2. **Confirmed +15 days**
   - Draft schedule.
   - Send message code.
   - Publish blog.
3. **Confirmed (max. 1 month before stay)**
   - Prepare summary sheet.
   - Collect allergy info.
   - Finalize waiting list.
4. **Validated (before stay)**
   - Assign room numbers.
5. **Terminated (+1 day max)**
   - Archive pedagogical sheets.

### Example: Lathus

- Used mainly as a **contact history**.
- Each line has:
  - A date.
  - A description of what was done.
  - A “validated” status.

