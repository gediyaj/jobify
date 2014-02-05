<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'jobify-jb');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '3_1JWxtR+H-8?,#r)_b=DSCoBZ<V2O`o}xDH;cOXaP*5F}+Y6:u=a-F+JUyfr=-G');
define('SECURE_AUTH_KEY',  '+X`GFCP+;o&O0,5UK1H)Z$u*HwSb*)`KW1g&_t2@3mGY8#[)0i_pI_A7IM/]S~f@');
define('LOGGED_IN_KEY',    'a-At(YiJ*lb+).;?#*m+p1GliT^*%B+zJEYwK!A8X9$333J{+C;nVujIBn9`1z_Y');
define('NONCE_KEY',        'D^Rt<eT|+[:{&S,zC:?{7BN/H,4ypdW/&w;r^.|(Nc5Z@8z+L#WOFN,HA(1I,wzy');
define('AUTH_SALT',        'JclX>U9%yQ|z:CF<.|GW4R=m-lK:u0+(xW<%k|Ug{(uQn`:t<pPL)sYV@dz~q3wN');
define('SECURE_AUTH_SALT', '>9nL|OW]l9$2&(`c FJ@z 5lLIXjPWW-pQQ|AA7/QjUxZ)+>/~N[=P~BJ!o`b7FM');
define('LOGGED_IN_SALT',   '8D`lTdDxbbd-lfO?8yoGY;,O ]B[mjNx)D]*,eT*uf^uD|$6z;db+|e8Tu?8sZE]');
define('NONCE_SALT',       ',LP^1OQPdvcR:bWJX|W!U!8WF</[ B(ZR/-p*&T;8jCb0sIs2,)f1=c+!K$}x*RS');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
