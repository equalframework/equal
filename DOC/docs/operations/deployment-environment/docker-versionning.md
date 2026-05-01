## Dockerhub - Versioning and Tagging conventions

1. **PHP Compatibility**  
   - An eQual version is always compatible with **at least two PHP versions**.

2. **Dependencies**  
   - eQual versions depend on their usage but are **always based on Linux, Apache, and PHP**.

3. **Variants by Database Management System (DBMS)**  
   - There are distinct eQual versions depending on the **supported DBMS** (e.g., MySQL, SQL Server, SQLite).  
   - Each image is tagged accordingly to reflect the compatible DBMS.

### Tagging Scheme

| **eQual Version** | **DBMS Version** | **PHP Version** |
|-------------------|------------------|-----------------|
| `equal:2.0-mysql` | MySQL | (Compatible with at least 2 PHP versions) |
| `equal:dev2.0-mysql` | MySQL | (Dev version) |
| `equal:dev2.0-sqlsrv` | SQL Server | (Dev version) |
| `equal:3.0-sqlite` | SQLite | (New 3.0 version) |

### Managing the `latest` Tag
- The `latest` tag must **always point to the most recent eQual version**.  
- Example:
  ```sh
  docker tag equal:3.0-sqlite equal:latest
  docker push equal:latest
  ```
- If a new version `equal:4.0-mysql` is released, `latest` should be updated to point to it.

---