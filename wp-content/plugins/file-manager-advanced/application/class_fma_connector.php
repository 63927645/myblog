<?php
/**
 * @package: File Manager Advanced
 * @Class: fma_connector
 */
if ( class_exists( 'class_fma_connector' ) ) {
    return;
}
class class_fma_connector {
    // elfinder defaults:
    //read:https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
    public function fma_local_file_system() {
        $settings = get_option('fmaoptions');
        $path     = ABSPATH;

        if ( isset( $settings['public_path'] ) && ! empty($settings['public_path'] ) ) {
            $path = $settings['public_path'];
        }

        $url = site_url();

        if ( isset( $settings['public_url'] ) && ! empty( $settings['public_url'] ) ) {
            $url = $settings['public_url'];
        }

        if ( isset( $settings['hide_path'] ) && ($settings['hide_path'] == '1' ) ) {
            $url = '';
        }

        if ( !function_exists( 'elFinderAutoloader' ) ) {
            require 'library/php/autoload.php';
        }

        // Load custom filesystem driver for content search
        if (defined('FMAFILEPATH')) {
            require_once FMAFILEPATH . 'application/class_fma_local_filesystem.php';
        } else {
            require_once dirname(__FILE__) . '/class_fma_local_filesystem.php';
        }

        if ( isset( $settings['enable_trash'] ) && ($settings['enable_trash'] == '1' ) ) {
            $trash   = array(
                'id'            => '1',
                'driver'        => 'Trash',
                'path'          => FMAFILEPATH.'application/library/files/.trash/',
                'tmbURL'        => site_url() . '/application/library/files/.trash/.tmb/',
                'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                'uploadDeny'    => array(''),                // Recomend the same settings as the original volume that uses the trash
                'uploadAllow'   => array('all'),// Same as above
                'uploadOrder'   => array('deny', 'allow'),      // Same as above
                'accessControl' => 'access',                    // Same as above
                'attributes'    => array(
                    array(
                        'pattern' => '/.tmb/',
                        'read'    => false,
                        'write'   => false,
                        'hidden'  => true,
                        'locked'  => false,
                    ),
                    array(
                        'pattern' => '/.quarantine/',
                        'read'    => false,
                        'write'   => false,
                        'hidden'  => true,
                        'locked'  => false,
                    ),
                    array(
                        'pattern' => '/.gitkeep/',
                        'read'    => false,
                        'write'   => false,
                        'hidden'  => true,
                        'locked'  => false,
                    )
                ),
            );
            $trash_f = 't1_Lw';
        } else {
            $trash   = array();
            $trash_f = '';
        }

        $hide_htaccess = array(
            'pattern' => '/.htaccess/',
            'read'    => false,
            'write'   => false,
            'hidden'  => true,
            'locked'  => false,
        );

        if ( isset( $settings['enable_htaccess'] ) && ! empty( $settings['enable_htaccess'] ) && $settings['enable_htaccess'] == '1' ) {
            $hide_htaccess = array(
                'pattern' => '/.htaccess/',
                'read'    => true,
                'write'   => false,
                'hidden'  => false,
                'locked'  => false,
            );
        }

        // getting allowed upload
        $allowUpload = array( 'all' );

        if ( isset( $settings['fma_upload_allow'] ) && ! empty( $settings['fma_upload_allow'] ) ) {
            $allowUpload = explode( ',',$settings['fma_upload_allow'] );
        }

        // restricting max upload size
        $max_upload_size = isset( $settings['upload_max_size'] ) ? $settings['upload_max_size']  : '0';

        $opts = array(
            'roots' => array(
                // Items volume
                array(
                    'driver'        => 'fma_local_filesystem',           // custom driver with content search support
                    'path'          => $path,                 // path to files (REQUIRED)
                    'URL'           => $url, // URL to files (REQUIRED)
                    'trashHash'     => $trash_f,                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => current_user_can('manage_options') ? array('all') : array('text/x-php'),                // All Mimetypes not allowed to upload
                    'uploadAllow'   => $allowUpload,// Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder'   => current_user_can('manage_options') ? array('deny','allow') :array('allow', 'deny'),      // allowed Mimetype `image` and `text/plain` only
                    'disabled'      => array('help','preference'),
                    'accessControl' => 'access',
                    'acceptedName'  => current_user_can('manage_options') ? '' : 'afm_plugin_file_validName',
                    'uploadMaxSize' => $max_upload_size,
                    'searchTimeout' => 300,
                    'attributes'    => array(
                        array(
                            'pattern' => '/.tmb/',
                            'read'    => false,
                            'write'   => false,
                            'hidden'  => true,
                            'locked'  => false,
                        ),
                        array(
                            'pattern' => '/.quarantine/',
                            'read'    => false,
                            'write'   => false,
                            'hidden'  => true,
                            'locked'  => false,
                        ),
                        array(
                            'pattern' => '/.gitkeep/',
                            'read'    => false,
                            'write'   => false,
                            'hidden'  => true,
                            'locked'  => false,
                        ),
                        $hide_htaccess
                    ),
                ),
                // Trash volume
                $trash,
            ),
        );
		$opts['bind']['upload'] = array( $this, 'on_upload_event' );
		$opts['bind']['search.pre'] = array( $this, 'on_search_command' );
        $opts = apply_filters( 'fma__opts_override', $opts );

        // run elFinder
        $fma_connector = new elFinderConnector( new elFinder( $opts ) );
        try {
            $fma_connector->run();
            die;
        } catch ( Exception $e ) {

        }
    }

	public function on_upload_event( $cmd, &$args, $files, $elfinder, $volume ) {
		if ( 'upload' === $cmd ) {
			if ( isset( $args['added'] ) && is_array( $args['added'] ) ) {
				foreach ( $args['added'] as $key => $uploaded_file ) {
					if ( isset( $uploaded_file['mime'] ) && ( 'image/svg' === $uploaded_file['mime'] || strpos( $uploaded_file['mime'], 'svg' ) ) ) {
						$this->sanitize_svg_file_content( $uploaded_file, $volume );
					}
				}
			}
		}
	}

	private function sanitize_svg_file_content( $uploaded_file, $volume ) {
		require_once FMAFILEPATH . 'application/svg-sanitizer/includes/autoload.php';
		if ( isset( $uploaded_file['hash'] ) ) {
			$file_path = $volume->getPath( $uploaded_file['hash'] );
			if ( file_exists( $file_path ) ) {
				$file_content  = file_get_contents( $file_path );
				$svg_sanitizer = new enshrined\svgSanitize\Sanitizer();
				$file_content  = $svg_sanitizer->sanitize( $file_content );
				file_put_contents( $file_path, $file_content );
			}
		}
	}

	/**
	 * Intercept search command to handle content search tag parameter
	 * This hook is called before the search command executes
	 * We decode the tag from q parameter which contains special marker
	 */
	public function on_search_command( $cmd, &$args, $elfinder, $volume ) {
		if ( $cmd === 'search' ) {
			$type = !empty( $args['type'] ) ? $args['type'] : '';
			$q = !empty( $args['q'] ) ? trim( $args['q'] ) : '';

			// Content search: type is SearchTag and/or q contains our marker
			if ( $type === 'SearchTag' || strpos( $q, '__CONTENT_SEARCH__:' ) === 0 ) {
				$tag = '';
				if ( strpos( $q, '__CONTENT_SEARCH__:' ) === 0 ) {
					$tag = substr( $q, strlen( '__CONTENT_SEARCH__:' ) );
				} elseif ( $type === 'SearchTag' && $q !== '' ) {
					$tag = $q; // default elFinder sends type=SearchTag and q=term (no marker)
				}
				if ( $tag !== '' ) {
					elFinderVolumefma_local_filesystem::setContentSearchTag( $tag );
					$args['q'] = '';
				} else {
					elFinderVolumefma_local_filesystem::setContentSearchTag( '' );
				}
			} else {
				elFinderVolumefma_local_filesystem::setContentSearchTag( '' );
			}
		}
	}
}
/**
* Hook to fix invalid and malicious files
*/
function afm_plugin_file_validName( $name ) {

    if ( ! empty( $name ) ) {
		// Check for dangerous characters (but allow spaces)
		// Block characters that could be used for path traversal or command injection
		if( preg_match( '/[<>:"|?*\x00-\x1F]/', $name ) ) {
			return false;
		}
		
		// Block files starting with dot (except for special cases handled elsewhere)
		if( strpos($name, '.') === 0 && $name !== '.' && $name !== '..' ) {
			return false;
		}

        $lower_name = strtolower( $name );

		// Block dangerous file extensions
		if(
			  strpos($lower_name, '.php') !== false
		   || strpos($lower_name, '.phtml') !== false
		   || strpos($lower_name, '.ini') !== false
		   || strpos($lower_name, '.htaccess') !== false
		   || strpos($lower_name, '.config') !== false
		   || strpos($lower_name, '.css') !== false
		   || strpos($lower_name, '.js') !== false
		  ) {
			return false;
		}
		
		// Filename is valid (allows spaces)
		return true;
	}
	
	return false;
}
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	//skipping htaccess
	if($basename == '.htaccess') {
		return null;
	} else {
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;   // else elFinder decide it itself
	}
	}