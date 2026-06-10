# Task Management

The task management system provides two distinct ways to handle tasks:

- **Followup Tasks**: Track actionable items derived from entity [workflows](../workflows/workflows.md), typically assigned to users and requiring completion. Tasks can be created manually or automatically based on task models that define when tasks should appear and their deadlines.
- **Scheduled Tasks**: Represent automated, system-level actions managed by the scheduler or CRON service, executed at specific times or intervals.

Understanding the distinction between these two types is important for proper usage and integration.

---

## Task


## Types of Tasks

There are two main types of tasks in the eQual framework:

### 1. Followup Tasks

**Followup Tasks** are actionable items derived either manually or automatically from a TaskModel. They are user-driven and track completion status, deadlines, and related workflow events.

#### Types of Followup Tasks

| Type              | Description                                                                               |
| ----------------- | ----------------------------------------------------------------------------------------- |
| **Informational** | Automatically marked as done (e.g., "contract received signed").                          |
| **Actionable**    | Requires responsibility ("Who must do what?") or communication ("Who must be informed?"). |

#### Fields

| Field               | Description                                       |
| ------------------- | ------------------------------------------------- |
| `id`                | Identifier.                                       |
| `name`              | Label describing the action.                      |
| `is_done`           | Boolean flag indicating completion status.        |
| `done_by`           | User who completed the task.                      |
| `done_date`         | Completion date.                                  |
| `visible_date`      | When the task becomes visible.                    |
| `deadline_date`     | Calculated deadline (from TaskEvent).             |
| `has_task_model`    | Whether linked to a TaskModel.                    |
| `task_model_id`     | Originating model (nullable if created manually). |
| `notes`             | Free text notes.                                  |
| `trigger_event_id`  | Reference to the triggering event.                |
| `deadline_event_id` | Reference to the deadline event.                  |
| `object_class`      | Linked entity class.                              |
| `object_id`         | Linked entity identifier.                         |

#### Sorting and Display

- **Priority**: Non-completed tasks appear first.
- **Order**: Sorted by `deadline_date`.
- **Completed tasks**: Sorted by `done_date` or deadline order.
- **Grouping**: Can be grouped by deadline event.

### 2. Scheduled Tasks

**Scheduled Tasks** are automated, system-level actions managed by the scheduler or CRON service. They are not directly tied to user workflows, but rather to system operations and background jobs.

#### Features

- Scheduled execution based on `moment`, `repeat_axis`, and `repeat_step`.
- Recurrence and exclusivity controls.
- Status tracking (`idle`, `running`).
- Controller/action invocation with parameters.
- Logging and process management.

#### Fields (Key Examples)

| Field             | Description                                                                 |
| ----------------- | --------------------------------------------------------------------------- |
| `name`            | Name of the task.                                                           |
| `status`          | Current status (`idle`, `running`).                                         |
| `pid`             | Process Identifier of the script running the task.                          |
| `last_run`        | Timestamp of the last execution.                                            |
| `moment`          | Scheduled time for execution.                                               |
| `after_execution` | How to handle non-recurring task after execution.                           |
| `is_recurring`    | Whether the task repeats.                                                   |
| `repeat_axis`     | Basis for repetition (minute, hour, day, etc.).                             |
| `repeat_step`     | Steps to wait before running again.                                         |
| `is_exclusive`    | If true, prevents concurrent execution with other tasks.                    |
| `controller`      | Full notation of the action controller to invoke (ex. core_example_action). |
| `params`          | JSON object holding the parameters to relay to the controller.              |
| `logs_ids`        | Associated logs.                                                            |

#### Use Cases

- Automated background jobs
- Periodic maintenance
- System-level actions (e.g., sending emails, cleaning up data)

---

---

## Task Events


A **TaskEvent** represents a point in time where a followup task becomes relevant or due. Events can be based on:

- **Status changes** in an entity workflow (e.g., booking → *confirmed*).
- **Date fields** with optional offsets (e.g., *15 days before arrival*).

### Fields

| Field                            | Description                                                        |
| -------------------------------- | ------------------------------------------------------------------ |
| `name`                           | Human-readable event label.                                        |
| `object_class`                   | Namespace of the associated entity (e.g., `sale\booking\Booking`). |
| `event_type`                     | Either `status_change` or `date_field`.                            |
| `entity_status`                  | Status value, if event type is `status_change`.                    |
| `entity_date_field`              | Date field name, if event type is `date_field`.                    |
| `offset`                         | Number of days relative to the event (default `0`).                |
| `trigger_event_task_models_ids`  | TaskModels that use the event as a trigger.                        |
| `deadline_event_task_models_ids` | TaskModels that use the event as a deadline.                       |

### Examples

- `confirmation`
- `confirmation +15 days`
- `1 month before reservation`
- `15 days before reservation`

---

## Task Models


A **TaskModel** defines a reusable template for followup tasks. It specifies when a task should appear and when it must be completed.

### Characteristics

- Bound to a specific entity type (`object_class`).
- Defines **trigger** and **deadline** events.
- Can be grouped into `task_group` for organizational purposes.
- Automatically spawns tasks when the trigger condition is met.

### Fields

| Field               | Description                                 |
| ------------------- | ------------------------------------------- |
| `name`              | Template label.                             |
| `description`       | Text description.                           |
| `object_class`      | Namespace of the concerned entity.          |
| `tasks_group_name`  | Group name for filtering tasks.             |
| `trigger_event_id`  | Event that activates the task.              |
| `deadline_event_id` | Event that sets the latest completion date. |
| `tasks_ids`         | Generated tasks linked to this template.    |

### Example

Task Model: *"Send Allergy List"*
- **Trigger**: Booking → *confirmed*
- **Deadline**: *15 days before arrival*

---

## Task Lifecycle

### Creation

#### Followup Tasks
A followup task is created when:

1. **Manual creation**: Via code or user action.
2. **Automatic creation**: When a TaskModel's trigger condition is met.

!!! note "Constraint"
    There can only be one followup task per TaskModel per object. If one already exists, it is removed and replaced.

#### Scheduled Tasks
Scheduled tasks are created and managed by the system administrator or application code, and are executed automatically by the scheduler or CRON service based on their configuration.

### Execution Logic

#### Followup Tasks
On each status change of an entity:

1. Fetch all TaskModels relevant for the management team.
2. Compare the entity's status with the `trigger_event_id`.
3. If matching, create a new followup task.
4. Remove any previous task from the same TaskModel.

#### Scheduled Tasks
Scheduled tasks are executed automatically when their scheduled `moment` is reached, according to their recurrence and exclusivity settings. The scheduler service ensures tasks are run at the appropriate time and manages concurrent execution.

## Example Scenarios

### Scenario: Event Management (Followup Tasks)

| Phase                                    | Tasks                                                              |
| ---------------------------------------- | ------------------------------------------------------------------ |
| **Confirmed**                            | Receive contract, Send access codes, Prepare stay binder           |
| **Confirmed +15 days**                   | Draft schedule, Send message code, Publish blog                    |
| **Confirmed (max. 1 month before stay)** | Prepare summary sheet, Collect allergy info, Finalize waiting list |
| **Validated (before stay)**              | Assign room numbers                                                |
| **Terminated (+1 day max)**              | Archive pedagogical sheets                                         |

### Scenario: Contact History (Followup Tasks)

Used as a contact history where each line has:
* A date
* A description of what was done
* A "validated" status

---

## Summary: Task Types

| Task Type      | Purpose                          | Managed By      | Typical Fields/Features             |
| -------------- | -------------------------------- | --------------- | ----------------------------------- |
| Followup Task  | User-driven, workflow completion | Workflow engine | Completion status, deadlines, user  |
| Scheduled Task | Automated, system-level actions  | Scheduler/CRON  | Scheduling, recurrence, exclusivity |

Use followup tasks for tracking user actions and workflow steps. Use scheduled tasks for automating background operations and system maintenance.

---
