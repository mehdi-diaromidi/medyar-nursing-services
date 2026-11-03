<?php
/**
 * Phase 0 admin stub dashboard.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ui_static = MNS_Plugin::is_ui_static();
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'خدمات پرستاری', 'medyar-nursing-services' ); ?></h1>
	<p><?php echo esc_html__( 'پلاگین فعال است. این صفحه اسکلت فاز ۰ است؛ دامنه عملیاتی در فازهای بعد اضافه می‌شود.', 'medyar-nursing-services' ); ?></p>
	<ul>
		<li>
			<?php
			printf(
				/* translators: %s: plugin version */
				esc_html__( 'نسخه پلاگین: %s', 'medyar-nursing-services' ),
				esc_html( MNS_VERSION )
			);
			?>
		</li>
		<li>
			<?php
			printf(
				/* translators: %s: db schema version */
				esc_html__( 'نسخه اسکیما: %s', 'medyar-nursing-services' ),
				esc_html( MNS_DB_VERSION )
			);
			?>
		</li>
		<li>
			<?php
			echo esc_html(
				$ui_static
					? __( 'حالت UI استاتیک: روشن', 'medyar-nursing-services' )
					: __( 'حالت UI استاتیک: خاموش', 'medyar-nursing-services' )
			);
			?>
		</li>
	</ul>
</div>
