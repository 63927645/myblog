<?php
/**
 * @package: File Manager Advanced
 * @Class: elFinderVolumefma_local_filesystem
 * Custom LocalFileSystem driver with content search support
 * This extends elFinderVolumeLocalFileSystem without modifying library files
 */
if ( class_exists( 'elFinderVolumefma_local_filesystem' ) ) {
    return;
}

if (defined('FMAFILEPATH')) {
    require_once FMAFILEPATH . 'application/library/php/elFinderVolumeLocalFileSystem.class.php';
} else {
    require_once dirname(__FILE__) . '/library/php/elFinderVolumeLocalFileSystem.class.php';
}

class elFinderVolumefma_local_filesystem extends elFinderVolumeLocalFileSystem {

    /**
     * Store tag for content search
     */
    protected $contentSearchTag = '';

    /**
     * Override search to handle content-based search via type parameter
     * We completely override parent search to ensure doSearch is called for content searches
     */
    public function search($q, $mimes, $hash = null, $type = null) {
        $this->contentSearchTag = '';
        $tagFromHook = self::getContentSearchTag();

        if (empty($tagFromHook) && strpos($q, '__CONTENT_SEARCH__:') === 0) {
            $tagFromHook = substr($q, strlen('__CONTENT_SEARCH__:'));
            $q = '';
        }
        if (empty($tagFromHook) && $type === 'SearchTag' && $q !== '' && $q !== 'CONTENTSEARCHPLACEHOLDER') {
            $tagFromHook = $q;
        }

        $isContentSearch = ($type === 'SearchTag' || ($tagFromHook !== '' && $tagFromHook !== false && $tagFromHook !== null));

        if ($isContentSearch) {
            $this->contentSearchTag = $tagFromHook;
            if ($q === '') {
                $q = 'CONTENTSEARCHPLACEHOLDER';
            }
        }

        // Start of parent search logic (from elFinderVolumeDriver.class.php line 3160)
        $res = array();
        $matchMethod = empty($type) ? 'searchMatchName' : 'searchMatch' . $type;
        if (!method_exists($this, $matchMethod)) {
            $matchMethod = 'searchMatchName';
        }

        $dir = null;
        if ($hash) {
            $dir = $this->decode($hash);
            $stat = $this->stat($dir);
            if (!$stat || $stat['mime'] !== 'directory' || !$stat['read']) {
                $q = '';
            }
        }
        if ($mimes && $this->onlyMimes) {
            $mimes = array_intersect($mimes, $this->onlyMimes);
            if (!$mimes) {
                $q = '';
            }
        }
        $this->searchStart = time();

        $qs = preg_split('/"([^"]+)"| +/', $q, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $query = $excludes = array();
        foreach ($qs as $_q) {
            $_q = trim($_q);
            if ($_q !== '') {
                if ($_q[0] === '-') {
                    if (isset($_q[1])) {
                        $excludes[] = substr($_q, 1);
                    }
                } else {
                    $query[] = $_q;
                }
            }
        }

        // For content search, ensure we don't return early even if query is empty
        if (!$query && !$isContentSearch) {
            $q = '';
        } else {
            if ($query) {
                $q = join(' ', $query);
            } else {
                // For content search, keep q as placeholder so doSearch is called
                $q = 'CONTENTSEARCHPLACEHOLDER';
            }
            $this->doSearchCurrentQuery = array(
                'q' => $q,
                'excludes' => $excludes,
                'matchMethod' => $matchMethod
            );
            // Set type for content search
            if ($isContentSearch) {
                $this->doSearchCurrentQuery['type'] = 'SearchTag';
            }
        }

        // For content search, don't return early even if q is empty
        if (($q === '' && !$isContentSearch) || $this->commandDisabled('search')) {
            return $res;
        }

        // valided regex $this->options['searchExDirReg']
        if ($this->options['searchExDirReg']) {
            if (false === preg_match($this->options['searchExDirReg'], '')) {
                $this->options['searchExDirReg'] = '';
            }
        }

        // check the leaf root too
        if (!$mimes && (is_null($dir) || $dir == $this->root)) {
            $rootStat = $this->stat($this->root);
            if (!empty($rootStat['phash'])) {
                if ($isContentSearch || $this->stripos($rootStat['name'], $q) !== false) {
                    $res = array($rootStat);
                }
            }
        }

        $result = array_merge($res, $this->doSearch(is_null($dir) ? $this->root : $dir, $q, $mimes));

        return $result;
    }

    /**
     * Static storage for content search tag (set by bind hook)
     */
    protected static $contentSearchTagStorage = '';

    /**
     * Set content search tag (called by bind hook)
     */
    public static function setContentSearchTag($tag) {
        self::$contentSearchTagStorage = $tag;
    }

    /**
     * Get content search tag
     */
    public static function getContentSearchTag() {
        return self::$contentSearchTagStorage;
    }

    /**
     * Override doSearch to implement content-based search
     */
    protected function doSearch($path, $q, $mimes) {
        $result = array();
        $matchMethod = empty($this->doSearchCurrentQuery['matchMethod']) ? 'searchMatchName' : $this->doSearchCurrentQuery['matchMethod'];
        $timeout = $this->options['searchTimeout'] ? $this->searchStart + $this->options['searchTimeout'] : 0;
        if ($timeout && $timeout < time()) {
            $this->setError(elFinder::ERROR_SEARCH_TIMEOUT, $this->path($this->encode($path)));
            return $result;
        }

        // Get tag for content search - check instance property first, then static
        $tag = '';
        if (!empty($this->contentSearchTag)) {
            $tag = $this->contentSearchTag;
        } else {
            $tag = self::getContentSearchTag();
        }

        // Check if this is content search
        // Primary indicator: type is set to SearchTag in doSearchCurrentQuery
        // Secondary: we have a tag stored
        $qInQuery = !empty($this->doSearchCurrentQuery['q']) ? $this->doSearchCurrentQuery['q'] : $q;
        // For content search, q might be 'CONTENTSEARCHPLACEHOLDER' or empty after parent processes it
        $isPlaceholderQ = ($qInQuery === 'CONTENTSEARCHPLACEHOLDER' || $qInQuery === '' || trim($qInQuery) === '');

        // Check if type is already set in doSearchCurrentQuery
        $searchType = !empty($this->doSearchCurrentQuery['type']) ? $this->doSearchCurrentQuery['type'] : '';

        // Determine if this is content search
        $hasTag = ($tag !== '' && $tag !== null && $tag !== false);
        $isContentSearch = false;

        if ($hasTag) {
            // If we have a tag, check if this is content search
            if ($isPlaceholderQ || $searchType === 'SearchTag') {
                $isContentSearch = true;
                // Ensure type is set for recursive calls
                if (!isset($this->doSearchCurrentQuery['type'])) {
                    $this->doSearchCurrentQuery['type'] = 'SearchTag';
                }
            }
        }

        foreach ($this->scandirCE($path) as $p) {
            elFinder::extendTimeLimit($this->options['searchTimeout'] + 30);

            if ($timeout && ($this->error || $timeout < time())) {
                !$this->error && $this->setError(elFinder::ERROR_SEARCH_TIMEOUT, $this->path($this->encode($path)));
                break;
            }

            $stat = $this->stat($p);

            if (!$stat) {
                continue;
            }

            if (!empty($stat['hidden']) || !$this->mimeAccepted($stat['mime'], $mimes)) {
                continue;
            }

            $name = $stat['name'];

            if ($this->doSearchCurrentQuery['excludes']) {
                foreach ($this->doSearchCurrentQuery['excludes'] as $exclude) {
                    if ($this->stripos($name, $exclude) !== false) {
                        continue 2;
                    }
                }
            }

            // For content search, skip directories completely (only search in files)
            if ($isContentSearch && $stat['mime'] === 'directory') {
                // Skip heavy/irrelevant dirs to avoid timeout
                $skipDirs = array('node_modules', 'vendor', '.git', '.svn', 'bower_components');
                if (in_array(strtolower($name), $skipDirs)) {
                    continue;
                }
                // Still recurse into directories for content search, but don't include them in results
                if ($stat['read'] && !isset($stat['alias'])) {
                    if (!$this->options['searchExDirReg'] || !preg_match($this->options['searchExDirReg'], $p)) {
                        $result = array_merge($result, $this->doSearch($p, $q, $mimes));
                    }
                }
                continue; // Skip directories in content search results
            }

            // Check tag match (search inside file contents) for content search
            $tagMatch = true;
            if ($isContentSearch && $stat['mime'] !== 'directory' && is_readable($p)) {
                $tagMatch = false;

                // Only search in text-based files
                $textMimes = array('text/', 'application/javascript', 'application/json', 'application/xml', 'application/x-php', 'text/x-php');
                $isTextFile = false;
                foreach ($textMimes as $textMime) {
                    if (strpos($stat['mime'], $textMime) === 0) {
                        $isTextFile = true;
                        break;
                    }
                }

                // Also check by extension
                $textExtensions = array('txt', 'php', 'js', 'css', 'html', 'htm', 'xml', 'json', 'md', 'log', 'sql', 'csv', 'ini', 'conf', 'config', 'yaml', 'yml');
                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                if (!$isTextFile && in_array($ext, $textExtensions)) {
                    $isTextFile = true;
                }

                if ($isTextFile) {
                    try {
                        $maxRead = 2 * 1024 * 1024; // 2MB max read per file for content search
                        $size = isset($stat['size']) ? (int) $stat['size'] : 0;
                        if ($size > 0 && $size > $maxRead) {
                            $fh = @fopen($p, 'rb');
                            if ($fh) {
                                $content = @fread($fh, $maxRead);
                                @fclose($fh);
                            } else {
                                $content = false;
                            }
                        } else {
                            $content = @file_get_contents($p);
                        }
                        if ($content !== false && $content !== '') {
                            if (stripos($content, $tag) !== false) {
                                $tagMatch = true;
                            }
                        }
                    } catch (Exception $e) {
                        $tagMatch = false;
                    }
                } else {
                    $tagMatch = false;
                }
            }

            // Check name match - skip for content search
            $nameMatch = true;
            if (!$isContentSearch) {
                // Normal name-based search
                $nameMatch = ($q === '' || $this->$matchMethod($name, $q, $p) !== false);
            }
            // For content search, nameMatch is always true (we only care about content match)

            // Include file if both name and tag match
            // For content search, only include files (not directories)
            if ((!$mimes || $stat['mime'] !== 'directory') && $nameMatch && $tagMatch) {
                $stat['path'] = $this->path($stat['hash']);
                if ($this->URL && !isset($stat['url'])) {
                    $path = str_replace($this->separator, '/', substr($p, strlen($this->root) + 1));
                    if ($this->encoding) {
                        $path = str_replace('%2F', '/', rawurlencode($this->convEncIn($path, true)));
                    } else {
                        $path = str_replace('%2F', '/', rawurlencode($path));
                    }
                    $stat['url'] = $this->URL . $path;
                }

                $result[] = $stat;
            }

            // Recurse into directories for non-content searches
            // (For content searches, we already handled recursion above)
            if (!$isContentSearch && $stat['mime'] == 'directory' && $stat['read'] && !isset($stat['alias'])) {
                if (!$this->options['searchExDirReg'] || !preg_match($this->options['searchExDirReg'], $p)) {
                    $result = array_merge($result, $this->doSearch($p, $q, $mimes));
                }
            }
        }

        return $result;
    }
}
