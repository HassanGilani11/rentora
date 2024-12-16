<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'github-repo' );

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
define( 'AUTH_KEY',         'tZ1m <Gd;BffJJdYa&[v/`I=;pW6;F,_%1xr*XY.WY`QH[F7uhg]:Sl[rqx4G,5Y' );
define( 'SECURE_AUTH_KEY',  'e=|kNL//05>#@dQn A*<j%Pa7i^2i]wgF]<$jLLEsn^Ua~xOk?qVV.N%wgdY>B,B' );
define( 'LOGGED_IN_KEY',    '5|@Xh$O{|lIu, {aUo|vP6i~h&PPR.N0]YMI$@ k!4Z0R#X^&yE2.$5 BOVtU08 ' );
define( 'NONCE_KEY',        'T@-Sy(O4WjrpID{H_K34&2_;6~2#~KR+w>97MlK[4),?5CIyDw1!Tv$ksW@gPZr?' );
define( 'AUTH_SALT',        'x%sgD}Je=-l$$ W>VTw8J_}w]0t|}<*[3jxYL3 SK/)kY}l_j//IlXBmwPNh4zX%' );
define( 'SECURE_AUTH_SALT', 'L94Pqq>6$D|wO3lGE2XI_Ieeiz.s9ChsrJt8ioGCR[fa*`]%KQw Y35JkX)``1C4' );
define( 'LOGGED_IN_SALT',   'xXJN]B6Kc[yiC8B:?O#ZMT0?yA6wbzOq7EdR!M@KaZn&z74>x4TGw{T-_DWv3WhL' );
define( 'NONCE_SALT',       '(?|KPRA^eLob`ay7?@:D^ao~VBzHJdnygr,Uj/_loT?rU^!n=(wxf@(2Z>{{y.@`' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
