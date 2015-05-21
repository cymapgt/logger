<?php

/*
 * This module name processor adds functionality to add module name to
 * the line formatter of the default logger.
 *
 * (c) Cyril Ogana <cogana@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cymapgt\core\utility\logger\processor;

/**
 * Adds application/module name to the logger for ease of log analysis
 *
 * @author Cyril Ogana <cogana@gmail>
 */
class ModuleNameProcessor
{
    protected $moduleData;

    /**
     * @param mixed $moduleData array or object w/ ArrayAccess that provides access to the modules data
     */
    public function __construct($moduleData = null)
    {
        if (null === $moduleData) {
            $this->moduleData = array('application' => '', 'package' => '', 'module' => '', 'appinfo' => '');
        } elseif (is_array($moduleData) || $moduleData instanceof \ArrayAccess) {
            $this->moduleData = $moduleData;
        } else {
            throw new \UnexpectedValueException('$moduleData must be an array or object implementing ArrayAccess.');
        }
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'application' => $this->moduleData['application'],
                'package'     => $this->moduleData['package'],
                'module'      => $this->moduleData['application'],
                'appinfo'     => $this->moduleData['appinfo']
            )
        );

        return $record;
    }
}
