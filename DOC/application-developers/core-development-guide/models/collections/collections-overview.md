# Collections Overview

Collections are widely used throughout the eQual framework to simplify the manipulation of entities (objects). They allow developers to execute the same operation on multiple objects in a single call.

In eQual, a collection is a series of partial objects (the ORM loads only requested fields) on which one or more CRUD operations can be applied. Using collections enables method chaining, making code in controllers shorter and easier to read.

## Key Features of Collections

- **Bulk Operations**: Perform operations on multiple objects simultaneously.
- **Lazy Loading**: Load only requested fields, reducing memory usage and improving performance.
- **Consistent API**: Provides a unified interface for searching, retrieving, and modifying objects.
- **Method Chaining**: Write concise and readable code by chaining operations.

For more details on the ORM and its integration with collections, see [ORM Documentation](../orm.md).

---

