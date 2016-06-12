<?php
/**
 * This file contains class::UnexpectedContentException
 * @package Runalyze\Import\Exception
 */

namespace Runalyze\Import\Exception;

/**
 * Exception for files with unexpected content
 *
 * Files throwing this exception should be submitted as support tickets,
 * as the file seems valid but our parser is not able to read it completely.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Exception
 */
class UnexpectedContentException extends ParserException
{

}