v0.3.0
======

### Improvements

*   Add `states` column type for defining states for rows
*   Add possibility to provide extra attributes for embedded model
*   It's possible to provide a url to the resource on main site using `externalUrl` in the schema
*   Support Laravel 4.2
*   A better date and time inputs
*   A layout for forms
*   Input addons
*   Added `cruddy:compile` and `cruddy:clear-compiled` for compiling the schema
*   Update to intervention/image 2.0
*   A special button is used to display editing form rather than whole row
*   Images in datagrid are clickable now
*   More robust menu builder
*   Allow do define extra properties for dropdown in the menu

### API changes

*   Removed `DateTime::format()`, the format is fixed now
*   Move lang-related stuff to `cruddy.lang` service
*   Move assets-related stuff to `cruddy.assets` service
*   Rename `SearchProcessorInterface::search` to `constraintBuilder` to support Laravel 4.2
*   `SchemaInterface::entity` now doesn't have any parameters

### Bugfixes

*   Fix embedded entities didn't get updated
*   Fix issues with entity selector
*   Format images when image field is disabled

### Other

*   Sentry permissions driver is moved to `cruddy/sentry` package