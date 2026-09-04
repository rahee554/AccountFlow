<?php

namespace ArtflowStudio\AccountFlow\Exceptions;

use RuntimeException;

/**
 * Base for every exception AccountFlow raises, so host applications can
 * catch the whole package with one type.
 */
class AccountFlowException extends RuntimeException {}
