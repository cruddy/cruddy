v0.5.0
======

*   Support Laravel 5.2
*   A lot refactoring done
*   Fixed many issues
*   `code` and `markdown` fields moved to `cruddy/ace` package

v0.4.0
======

*   Plain formatter now doesn't use escaping
*   #49: fixed image displayed when no data provided
*   External link is moved to after refresh button
*   Added automatic resolution of whether the field is required
*   \#9: fixed entity drop down sending extra request
*   Refactored drop downs
*   \#59: fixed entity events not always registered
*   Added delete action in data grid
*   Refactored routes and controllers
*   Refactored forms
*   The page title is now updated according to current entity and instance
*   Refactored navigation to remove duplicate requests
*   Records are now cached for quicker responses
*   Added `cruddy::custom` layout for custom pages
*   `saving` event is now fired after the model is filled, and the handler receives model, rather than input
*   Data grid is now able to display extra actions for rows
*   Added custom save actions for model
*   #24: Filters can now be passed in query string
*   #63: use query without scopes for getting items

v0.3.2
======

*   Fixed issue #44

v0.3.1
======

*   Refactored image\file lists
*   Extra form buttons moved from the header to the footer
*   Added a button for syncing model with server data
*   Refactored language lines a little bit
*   FileUploader now returns empty array when no files were uploaded when using multiple uploads
*   Copying now works

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
*   Some navigation-related data is included to the url for convinience
*   Filters are applied using a special button now to avoid frequest requests
*   Search input now has submit button
*   When focusing entity selector, entity dropdown is displayed now only when value is not specified
*   An item of entity selector now responds on pressing enter key
*   Embedded items are now deleted softly and can be restored before saving the model

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