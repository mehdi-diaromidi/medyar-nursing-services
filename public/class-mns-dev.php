<?php
/**
 * Dev-only page-state switcher (?mns_state=) and component kit gate.
 *
 * Enabled only when: static UI is on, the user can manage_options,
 * and MNS_DEV_TOOLS is not defined as false. Production sets
 * MNS_UI_STATIC (or MNS_DEV_TOOLS) to false, so nothing is rendered there.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Dev {

	public const QV_STATE = 'mns_state';

	public static function enabled(): bool {
		if ( defined( 'MNS_DEV_TOOLS' ) && ! MNS_DEV_TOOLS ) {
			return false;
		}
		if ( ! MNS_Plugin::is_ui_static() ) {
			return false;
		}
		return is_user_logged_in() && current_user_can( 'manage_options' );
	}

	/**
	 * Generic states every page supports; pages add their own via the
	 * `mns_dev_states` filter (args: $states, $panel, $page).
	 *
	 * @return array<string,string> slug => label
	 */
	public static function states( string $panel, string $page ): array {
		$states = array(
			'default' => __( 'عادی', 'medyar-nursing-services' ),
			'loading' => __( 'در حال بارگذاری', 'medyar-nursing-services' ),
			'empty'   => __( 'خالی', 'medyar-nursing-services' ),
			'error'   => __( 'خطا', 'medyar-nursing-services' ),
			'long'    => __( 'دادهٔ طولانی', 'medyar-nursing-services' ),
		);

		/**
		 * @param array<string,string> $states
		 * @param string               $panel  patient|nurse|catalog|signup
		 * @param string               $page   Page slug.
		 */
		$filtered = apply_filters( 'mns_dev_states', $states, $panel, $page );
		return is_array( $filtered ) ? $filtered : $states;
	}

	/**
	 * Active state for the current request. Always `default` when tools are off.
	 */
	public static function state(): string {
		if ( ! self::enabled() ) {
			return 'default';
		}
		$raw = isset( $_GET[ self::QV_STATE ] ) ? sanitize_key( wp_unslash( $_GET[ self::QV_STATE ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '' !== $raw ? $raw : 'default';
	}

	public static function is( string $state ): bool {
		return self::state() === $state;
	}

	/**
	 * Floating switcher markup (printed from wp_footer on MNS templates).
	 */
	public static function render_switcher( string $panel, string $page ): void {
		if ( ! self::enabled() ) {
			return;
		}
		$current = self::state();
		$states  = self::states( $panel, $page );
		$kit_url = '';
		if ( in_array( $panel, array( 'patient', 'nurse' ), true ) ) {
			$kit_url = add_query_arg(
				array(
					MNS_Pages::QV_PANEL => $panel,
					MNS_Pages::QV_PAGE  => 'ui-kit',
				),
				remove_query_arg( array( self::QV_STATE, MNS_Pages::QV_PAGE, MNS_Pages::QV_PANEL ) )
			);
		}
		?>
		<div class="mns-dev" dir="rtl" data-mns-dev>
			<button type="button" class="mns-dev__toggle" data-mns-dev-toggle aria-expanded="false" aria-controls="mns-dev-panel">
				<?php echo esc_html__( 'حالت', 'medyar-nursing-services' ); ?>: <strong><?php echo esc_html( $states[ $current ] ?? $current ); ?></strong>
			</button>
			<div class="mns-dev__panel" id="mns-dev-panel" hidden>
				<p class="mns-dev__title"><?php echo esc_html( MNS_Copy::t( 'dev.title' ) ); ?></p>
				<ul class="mns-dev__list">
					<?php foreach ( $states as $slug => $label ) : ?>
						<?php $url = 'default' === $slug ? remove_query_arg( self::QV_STATE ) : add_query_arg( self::QV_STATE, $slug ); ?>
						<li>
							<a class="mns-dev__link<?php echo $slug === $current ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $slug === $current ? ' aria-current="true"' : ''; ?>>
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( '' !== $kit_url ) : ?>
					<a class="mns-dev__kit" href="<?php echo esc_url( $kit_url ); ?>"><?php echo esc_html( MNS_Copy::t( 'dev.ui_kit' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
