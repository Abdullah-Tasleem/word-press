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
define( 'DB_NAME', 'wp_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         '(3fhfLGc8m?ua9Ry[QJsXe4v>o6hRMa;p;sU_CdCyhZvs`*DfFW<8xzS$ApKo-k(' );
define( 'SECURE_AUTH_KEY',  '[)I*7 S^,8;S$!!K}1s?k$.Rj93).1eJx9UYW}@btekdW_~~,+l0KYpV]Z9K|[t_' );
define( 'LOGGED_IN_KEY',    's niW1h!g(0U]X^Rh.?,&pL7l?Wprsro.^i*)yK-zaYBBWcph*wG-/D0![Y7hP?S' );
define( 'NONCE_KEY',        's)_PCqL.iH@6U>J(EcHwEa`NVGJ3vKd/S<9M<B6j=rm4`:`#PCU$-tVw%.`deR27' );
define( 'AUTH_SALT',        'uUNcS#{P[+blKzhw=YT13bkmcuS@GW[mmuZ<IL;1|XLZpBWl7wGftuC$(?h+LRcG' );
define( 'SECURE_AUTH_SALT', '~y[#}K^?x@{hJ(/8,+o#INBkO3N1(v39AI%c 6oK|NWoI:M>*u4 Qy8GMYsPIG<3' );
define( 'LOGGED_IN_SALT',   '$YE8|oEu*hHO{IwvXz td2}; E`RQxA6b`)DNS[j`g(i$;QkM^q#Qr3Gkm8`s1DX' );
define( 'NONCE_SALT',       'i*/??aXs:iv+1q+S;*c=$^I(eVDJ*jz%3P&HoVf|!ZG2N}zk,#Y9%7*x8:rG3E,o' );

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



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
