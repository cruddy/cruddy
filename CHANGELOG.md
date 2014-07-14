__0.3.0__

*   Rename `SearchProcessorInterface::search` to `constraintBuilder` to support Laravel 4.2
*   Support Laravel 4.2
*   Add `states` column type for defining states for rows
*   Removed `DateTime::format()`, the format is fixed now
*   A better date and time inputs
*   Fix issues with entity selector
*   Move lang-related stuff to `cruddy.lang` service
*   Move assets-related stuff to `cruddy.assets` service
*   `SchemaInterface::entity` now doesn't have any parameters