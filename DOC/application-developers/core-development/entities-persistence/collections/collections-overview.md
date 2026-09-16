# Collections Overview

Collections are widely used throughout the eQual framework to simplify the manipulation of entities (objects). They allow developers to execute the same operation on multiple objects in a single call.

In eQual, a collection is a series of partial objects (the ORM loads only requested fields) on which one or more CRUD operations can be applied. Using collections enables method chaining, making code in controllers shorter and easier to read.

## Lifecycle-Aware and Explicit Technical Operations

The formal distinction is behavioral:

* `create()`, `read()`, `update()` and `delete()` call `assertLifecycle()` and therefore invoke their matching `can...()` entity guard;
* `draft()`, `write()` and `instantiate()` do not call `assertLifecycle()`; `remove()` is only available as a privileged `ObjectManager` operation;
* each method separately defines whether `state` changes implicitly, explicitly or remains unchanged.

`create()` and `draft()` both invoke creation hooks. When `create()` produces an instance directly, it also invokes `onafterinstantiate()` after the creation hooks. Drafts reach the instance lifecycle later through `update()` or `instantiate()`. Remember that `update()` targets `instance` when `state` is omitted: inside a creation hook, prefer `write()` to preserve a draft, or read and pass its current state explicitly to `update()`.

`CRUD` and `DWIR` can help remember the current method names, but they are only informal acronyms—not framework operation categories. See the [complete lifecycle contract](../entities.md#lifecycle-contract-by-operation).

## Immutable Data Flow

eQual complies with principle #3 of the Data-Oriented Programming paradigm: **treat data as immutable**.

For collections, this means that values obtained through `read()` are treated as snapshots. Changing a returned array or object does not persist anything by itself. A persistent change must be expressed through an explicit operation such as `update()`, `write()` or a named entity action.

The principle applies to the data flow, not to the PHP `Collection` object's internal implementation. A collection is a typed handle to a set of identifiers and supports lazy loading and method chaining; callers should not assume that it already contains every field of the selected records.

The same rule applies when a collection is injected into a callback as `$self`: the callback is responsible for requesting the data it needs with `read()`. This keeps dependencies visible and avoids coupling handler behavior to fields that happen to be present in the ORM cache. See the [callback data contract](../computed-fields.md#callback-data-contract).

## Key Features of Collections

- **Bulk Operations**: Perform operations on multiple objects simultaneously.
- **Lazy Loading**: Load only requested fields, reducing memory usage and improving performance.
- **Consistent API**: Provides a unified interface for searching, retrieving, and modifying objects.
- **Structural Checks**: Enforces entity [Capabilities](../entities.md#capabilities) before ACLs and business rules on generic CRUD operations.
- **Method Chaining**: Write concise and readable code by chaining operations.

For more details on the ORM and its integration with collections, see [ORM Documentation](../orm.md).

---

