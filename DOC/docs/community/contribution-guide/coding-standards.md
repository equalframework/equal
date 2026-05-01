## Naming Conventions

The languages involved in eQual are varied: JS, TS, PHP, JSON, HTML, whether it's at the back-end or front-end. 

To facilitate cross-stack work, communication between teams, and code reading, the same logic is used for the syntax for variables, constants, classes, functions, and methods.

Indeed, consistently following naming conventions throughout the codebase of packages enhances readability, maintainability, and collaboration among developers.

### Conventions used across the framework:

1. **Scalar Variables & Functions - snake_case**

   Scalar variables and functions (or var holding an anonymous function) should be named using snake_case, where words are separated by underscores.  
   Examples: `$total_count`, `calculate_area()`

2. **Objects - camelCase**

   Objects (vars holding instances of classes) should be named using camelCase, starting with a lowercase letter and capitalizing subsequent words.  
   Examples: `$myObject`, `$userProfile`

3. **Classes - PascalCase**

   Classes should be named using PascalCase, where each word in the name is capitalized, including the first word.  
   Example: `BankAccount`, `UserProfileManager`

4. **Class Members - camelCase**

   Class members, including attributes and methods, should follow camelCase convention.  
   Example: `$this->myAttribute`, `calculateInterest()`

5. **Entity Properties - snake_case**

   Entity properties (as defined in `getColumns()` for classes or `params` for controllers), should use snake_case.  
   Example: `first_name`, `is_paid`
   
   Maps and objects properties should be named using snake_case.  
   Example: `{origin_country: "BE", is_shipped: true}`

6. **Controllers - kebab-case**

   Controllers, typically used in web development frameworks, should be named using kebab-case, where words are separated by hyphens.  
   Example: `user-controller.php`, `do-pay.php`


## Coding Conventions

Coding conventions guidelines:

* eQual naming conventions should be used.
* Reference guidelines of the code language used should be followed.
* Significant functionality should come with appropriate testing to be run in the automated test suite.
* Licensing guidelines must be followed.

---