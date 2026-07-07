<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'faroars_com' );

/** Database username */
define( 'DB_USER', 'faroars_com' );

/** Database password */
define( 'DB_PASSWORD', 'bQFbjzXzyyNmTAbN' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ')V|7Zi,8.dI ~+(|I/%4( #ORmN=4*XTtZ-1*sljx,?m[xu&qr2}XF>S`*;]4A2S' );
define( 'SECURE_AUTH_KEY',  'h`wyj7hIUA%68!V@ZR(dA0t4O.*FB1F7p4C!rdo|KXY8o?5qgV2id&Gc8G[brw4h' );
define( 'LOGGED_IN_KEY',    '|(MYIPiSRFu<wY8X~.0v%&7  eP0LiPPI3Vnkc~re|706=Is1Rv~}+;62O>2t^:a' );
define( 'NONCE_KEY',        'Gv+%C[Ca-DUr,AN]]1 .hY3|2qOr4[`o0.&1:_?H.-6WfVpI65X[$q v6K[#et=n' );
define( 'AUTH_SALT',        '/etb!uj,zS6GF84M]bPJEHl?>8WKF4,%/BKwS*%%cb3H;elcR_:D5V:r:b# c={1' );
define( 'SECURE_AUTH_SALT', '0iC1)6NVqQa|%;<! @5=HOzETP/Ef]0?3L)oFWAjp!:tY#tXT?CM1/>45hd6Fh?+' );
define( 'LOGGED_IN_SALT',   'f?.M+EEioN=K$OW2tmR!/%o2JbrcsX`GFunR!!qEl}eaE@KYJpYP&fZ}-]-*(.3#' );
define( 'NONCE_SALT',       '@NIlN=E6m$W_MNTUP%AC,mq:!D1^$(OpbD_ao(vNe!WQ&QzS,j^QZ#~?Xz+$9o^8' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define('ARGON_GITHUB_CLIENT_ID', 'Ov23lizcVWJEQLrwaK6r');
define('ARGON_GITHUB_CLIENT_SECRET', '6ec6e8e129b7c1fada304432f2d03272f8c2e26b');

define('ARGON_CLOGIN_APPID', '1131');
define('ARGON_CLOGIN_APPKEY', 'e51a1a2fce67e04634fdc1bfb6f52fea');
define('ARGON_CLOGIN_ENDPOINT', 'https://login.blogcloud.cn/');
define('ARGON_CLOGIN_TYPE', 'qq');


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
