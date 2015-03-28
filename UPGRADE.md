## From 0.4 to 0.5

### Configuration changes

#### Permissions

The `permissions` option in configuration now references a class name of the permissions driver. The default 
implementation is `'Kalnoy\Cruddy\Service\PermitsEverything'` which simply permits everything.

#### Auth filter

`auth_filter` has been replaces with `middleware` option which specifies global middleware for every request to 
Cruddy API.

### Other changes

`ace` editor has been moved to a separate package `cruddy/ace`.