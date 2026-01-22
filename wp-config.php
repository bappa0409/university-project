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
define( 'DB_NAME', 'university-project' );

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
define( 'AUTH_KEY',         '@4t}Rfe-U5O6G;JQD!,z3.jS6c_=`UHYU`:#Ke15!hU!1{<$}6KrDi)nvNU}3NEg' );
define( 'SECURE_AUTH_KEY',  ',k4!bp_c=Qb%t)+F)pnz_9f~=<~8|mk$j:NGEc%Y`3H4~5ejs)66K2ti~ft@hcYM' );
define( 'LOGGED_IN_KEY',    'Qqka+ZC%fF;b_<7!m5T&:|Ar03f)FvW].DzuGnx)oOY(<qj+~yy=+S70N[#Vuq*;' );
define( 'NONCE_KEY',        '+mi6x}4>94LBsF>xw@N(o0et.t ~32l$xWioq$&4ejko9{F9# F]=?OlFiC*l.C%' );
define( 'AUTH_SALT',        'uGP?{pYD9a<H}mlf6zGC{T$;A,Vzafn,LuFgwTs=]NT8WR&v0~ImAA-ctorOiZm&' );
define( 'SECURE_AUTH_SALT', 'MjI*}69i,Kbxj/T(.VM)<i_4:l*7|f.#XGZDK<M%BnGGJ[#a~I[z6&Q,6(Gd(0xO' );
define( 'LOGGED_IN_SALT',   ']?w6}sBGDCD*2*U)s#8cg*4|44/V[8J+OI2/?A+nS|bWInd&o7{0zcX/ !@z|N;7' );
define( 'NONCE_SALT',       '/-yb{Z-I~Da<d:L1k_ilDU0p`;|=u1GmQE>;XAg@AR8AUafiOc>62WHQ`szY(q_W' );

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
