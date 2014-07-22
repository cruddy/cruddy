__0.3.0__

*   [feature] Add `states` column type for defining states for rows
*   [feature] Add possibility to provide extra attributes for embedded model
*   [feature] Support Laravel 4.2
*   [feature] A better date and time inputs
*   [feature] A layout for forms
*   [feature] Input addons
*   [feature] Added `cruddy:compile` and `cruddy:clear-compiled` for compiling the schema
*   [api] Removed `DateTime::format()`, the format is fixed now
*   [api] Move lang-related stuff to `cruddy.lang` service
*   [api] Move assets-related stuff to `cruddy.assets` service
*   [api] Rename `SearchProcessorInterface::search` to `constraintBuilder` to support Laravel 4.2
*   [api] `SchemaInterface::entity` now doesn't have any parameters
*   [fix] Fix embedded entities didn't get updated
*   [fix] Fix issues with entity selector
*   [fix] Format images when image field is disabled