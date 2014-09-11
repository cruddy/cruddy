class Cruddy.formatters.Plain extends BaseFormatter
    # Plain formatter now uses not escaped value to support feature in issue #46
    # https://github.com/lazychaser/cruddy/issues/46
    format: (value) -> value