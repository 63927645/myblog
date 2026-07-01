<?php
/**
 * FileLogModel class.
 *
 * @package File_Manager_Advanced_Pro
 */

namespace AFMP\Modules\FileLogs;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AFMP\\Modules\\FileLogs\\FileLogModel' ) ) :
	/**
	 * FileLogModel class.
	 */
	class FileLogModel {
		/**
		 * ID pf the file log.
		 *
		 * @since 2.8
		 * @var int $id ID of the file log.
		 */
		public $id = 0;

		/**
		 * User ID of the user who performed the action.
		 *
		 * @since 2.8
		 * @var int $user_id User ID of the user who performed the action.
		 */
		public $user_id = 0;

		/**
		 * Action performed.
		 *
		 * @since 2.8
		 * @var string $action Action performed.
		 */
		public $action = '';

		/**
		 * Path of the file.
		 *
		 * @since 2.8
		 * @var string $path Path of the file.
		 */
		public $path = '';

		/**
		 * Type of the file.
		 *
		 * @since 2.8
		 * @var string $type Type of the file.
		 */
		public $type = '';

		/**
		 * Time of the action.
		 *
		 * @since 2.8
		 * @var int $time Time of the action.
		 */
		public $time = 0;

		/**
		 * IP address of the user who performed the action.
		 *
		 * @since 2.8
		 * @var string $ip IP address of the user who performed the action.
		 */
		public $ip = '0.0.0.0';

		/**
		 * Create the file logs table.
		 *
		 * @since 2.8
		 */
		public static function create_table() {
			global $wpdb;
			$table_name   = $wpdb->prefix . 'fm_filelogs';
			$sql          = 'SHOW TABLES LIKE %s';
			$prepared_sql = $wpdb->prepare( $sql, $table_name );
			$table_exists = $wpdb->get_var( $prepared_sql );

			if ( $table_exists !== $table_name ) {
				$charset = $wpdb->get_charset_collate();
				$sql = 'CREATE TABLE IF NOT EXISTS %i (
    				`id`      INT     ( 11 )  NOT NULL AUTO_INCREMENT,
    				`user_id` INT     ( 11 )  NOT NULL,
    				`action`  VARCHAR ( 255 ) NOT NULL,
    				`path`    VARCHAR ( 255 ) NOT NULL,
    				`type`    VARCHAR ( 255 ) NOT NULL,
    				`ip`      VARCHAR ( 255 ) NOT NULL,
    				`time`    VARCHAR ( 255 ) NOT NULL,
    				PRIMARY KEY ( `id` )
				)' . $charset . ';';

				$prepared_sql = $wpdb->prepare( $sql, $table_name );
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $prepared_sql );
			}
		}

        /**
         * Get the client IP address.
         *
         * @since 2.8
         * @return string
         */
        private static function get_client_ip() {
            $ip_attrs = array(
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR',
            );
            $ip       = '127.0.0.1';

            foreach ( $ip_attrs as $ip_attr ) {
                if ( ! isset( $_SERVER[ $ip_attr ] ) ) continue;

                if ( empty( $_SERVER[ $ip_attr ] ) ) continue;

                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $ip_attr ] ) );
            }

            return apply_filters(
                'afmp__get_client_ip',
                $ip,
                '127.0.0.1' === $ip ? 'localhost' : ''
            );
        }

		/**
		 * Insert a new file log entry.
		 *
		 * @since 2.8
		 * @param string $action Action performed (added, removed, renamed, etc.).
		 * @param string $path Path of the file.
		 * @param array  $args Additional arguments (e.g., mime type).
		 *
		 * @return int
		 */
		public static function insert_item( $action, $path, $args ) {
			$filelog = new self();

			$filelog->user_id = get_current_user_id();
			$filelog->action  = $action;
			$filelog->path    = $path;
			$filelog->type    = $args['mime'];
			$filelog->ip      = self::get_client_ip();
			$filelog->time    = current_time( 'timestamp' );

			return $filelog->save();
		}

		/**
		 * Save the file log entry to the database.
		 *
		 * @since 2.8
		 * @return int
		 */
		public function save() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'fm_filelogs';

			$data = array(
				'user_id' => $this->user_id,
				'action'  => $this->action,
				'path'    => $this->path,
				'type'    => $this->type,
				'time'    => $this->time,
				'ip'      => $this->ip
			);

            $wpdb->insert( $table_name, $data );
            $this->id = $wpdb->insert_id;

			return $this->id;
		}
	}
endif;