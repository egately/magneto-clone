<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Options;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Dictionary generator options resolver
 */
class Resolver implements ResolverInterface
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $withContext;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * Resolver construct
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param string $directory
     * @param bool $withContext
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        $directory,
        $withContext
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->directory = $directory;
        $this->withContext = $withContext;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (null === $this->options) {
            if ($this->withContext) {
                $this->directory = rtrim($this->directory, '\\/');
                $moduleDirs = [];
                foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
                    if (strstr($moduleDir, $this->directory)) {
                        $moduleDirs[] = $moduleDir . '/';
                    }
                }

                $themeDirs = [];
                foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeDir) {
                    if (strstr($themeDir, $this->directory)) {
                        $themeDirs[] = $themeDir . '/';
                    }
                }
                $this->options = [
                    [
                        'type' => 'php',
                        'paths' => array_merge($moduleDirs, $themeDirs),
                        'fileMask' => '/\.(php|phtml)$/',
                    ],
                    [
                        'type' => 'html',
                        'paths' => array_merge($moduleDirs, $themeDirs),
                        'fileMask' => '/\.html$/',
                    ],
                    [
                        'type' => 'js',
                        'paths' => array_merge(
                            $moduleDirs,
                            $themeDirs,
                            [
                                $this->directory . '/lib/web/mage/',
                                $this->directory . '/lib/web/varien/',
                            ]
                        ),
                        'fileMask' => '/\.(js|phtml)$/'
                    ],
                    [
                        'type' => 'xml',
                        'paths' => array_merge($moduleDirs, $themeDirs),
                        'fileMask' => '/\.xml$/'
                    ],
                ];
            } else {
                $this->options = [
                    ['type' => 'php', 'paths' => [$this->directory], 'fileMask' => '/\.(php|phtml)$/'],
                    ['type' => 'html', 'paths' => [$this->directory], 'fileMask' => '/\.html/'],
                    ['type' => 'js', 'paths' => [$this->directory], 'fileMask' => '/\.(js|phtml)$/'],
                    ['type' => 'xml', 'paths' => [$this->directory], 'fileMask' => '/\.xml$/'],
                ];
            }
            foreach ($this->options as $option) {
                $this->isValidPaths($option['paths']);
            }
        }
        return $this->options;
    }

    /**
     * @param array $directories
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function isValidPaths($directories)
    {
        foreach ($directories as $path) {
            if (!is_dir($path)) {
                if ($this->withContext) {
                    throw new \InvalidArgumentException('Specified path is not a Magento root directory');
                } else {
                    throw new \InvalidArgumentException('Specified path doesn\'t exist');
                }
            }
        }
    }
}
