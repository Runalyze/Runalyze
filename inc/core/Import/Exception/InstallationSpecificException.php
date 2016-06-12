<?php
/**
 * This file contains class::InstallationSpecificException
 * @package Runalyze\Import\Exception
 */

namespace Runalyze\Import\Exception;

/**
 * Exception if there's an installation specific problem
 *
 * Files throwing this exception should be reported to the administrator.
 * This may indicate a general problem, the file itself is probably fine.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Exception
 */
class InstallationSpecificException extends ParserException
{

}