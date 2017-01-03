<?php

namespace KHerGe\Excel\Exception\Database;

use KHerGe\Excel\Exception\DatabaseException;

/**
 * An exception that is thrown for a prepared statement that is currently in use.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PreparedStatementInUseException extends DatabaseException
{
}
