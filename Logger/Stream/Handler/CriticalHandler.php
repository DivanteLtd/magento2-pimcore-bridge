<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Logger\Stream\Handler;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class CriticalHandler
 */
class CriticalHandler extends Base
{
    /**
     * Filename for info log
     */
    const LOG_FILENAME = 'bridge_critical.log';

    /**
     * Directory name for pimcore magento bridge integration log files
     */
    const LOG_DIR = 'pim_bridge';

    /**
     * Logging level
     */
    protected $loggerType = Logger::CRITICAL;

    /**
     * File name
     */
    protected $fileName = 'var/log/' . self::LOG_DIR . '/' . self::LOG_FILENAME;

    /**
     * Handler constructor.
     *
     * @param File $filesystem
     * @param null $filePath
     */
    public function __construct(File $filesystem, $filePath = null)
    {
        parent::__construct(
            $filesystem,
            $filePath
        );
    }
}
