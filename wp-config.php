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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'jetengine' );

/** Database username */
define( 'DB_USER', 'userjetEngine' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'db' );

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
define( 'AUTH_KEY',         '+wz]NV1y!HUQKIu;NQ|W~mw0EKGi+U,3_<wA+Q_J;a4qE9mvRYuobRI<Q^_thequ' );
define( 'SECURE_AUTH_KEY',  '?$5,O#=xWpcWu_sEWBcW#;);CG%zvE=S0iMoSWl$>wE47%S&5Cw4u1hW{v}3}f.P' );
define( 'LOGGED_IN_KEY',    'D ZugUxi-1T>[BvH-#LG0SfdwESJ;vr_5H/0G@RO%cKZ@jsK:|6C8WD2Hkp,SRF+' );
define( 'NONCE_KEY',        '|):R_7.J]y)MBcZ;?x@W}9ZEZvz}8e#KAxLDge;p;b^Hg}Pxd&Tph|_zz{{78H}@' );
define( 'AUTH_SALT',        'TZv[{w-+7]uXn::?^Y*wyEz+TC,{}Rf`~.EWE,,;dS]ci?w+<MqKIzqg1%v$vnQ_' );
define( 'SECURE_AUTH_SALT', 'no7j~*_Hu_;xg_50`:B`x~;ED8Lks|BdJ{rB# [W^i+M>+O/~$&`8OKe8B3c^V={' );
define( 'LOGGED_IN_SALT',   'k/G4fr/JG~AzxI2<DXufZ80nit=DV2IP!-ne%]P%:Bq#rhqqpQE{]8)(]zD-MMUN' );
define( 'NONCE_SALT',       ')_`1:tf{ugLrXP_4P[1;FLugIgPMg:U>wfcDxTXaHJ5?[B|&zv/CeGe=F}Y:+GH?' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_config';

define('JWT_AUTH_SECRET_KEY', "2:*?e@?f-H+o1$yu$FL8IY2Paf:d(W!GnO&[w}#^&^:q9s$)j>T4H(ZL:G>A?h}E");
define('JWT_AUTH_CORS_ENABLE', true);
define('MP_ACCESS_TOKEN', 'TEST-122387057603424-080715-b8dbdded796e37c54b6b56eafdc6a61f-519568344');

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