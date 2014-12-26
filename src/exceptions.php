<?php

namespace Kalnoy\Cruddy;

use RuntimeException;

class EntityNotFoundException extends RuntimeException {}

class ModelNotFoundException extends RuntimeException {}

class ModelNotSavedException extends RuntimeException {}

class OperationNotPermittedException extends RuntimeException {}

class ActionException extends RuntimeException {}