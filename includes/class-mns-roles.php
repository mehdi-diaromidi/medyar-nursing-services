<?php
/**
 * Roles and capabilities.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Roles {

	public const ROLE_NURSE = 'mns_nurse';

	public const CAP_MANAGE         = 'manage_mns';
	public const CAP_MANAGE_ORDERS  = 'mns_manage_orders';
	public const CAP_MANAGE_NURSES  = 'mns_manage_nurses';
	public const CAP_NURSE_PANEL    = 'mns_view_nurse_panel';
	public const CAP_PATIENT_PANEL  = 'mns_view_patient_panel';

	public static function register(): void {
		$nurse_caps = array(
			'read'               => true,
			self::CAP_NURSE_PANEL => true,
		);

		$role = get_role( self::ROLE_NURSE );
		if ( ! $role ) {
			add_role( self::ROLE_NURSE, 'پرستار', $nurse_caps );
		} else {
			foreach ( $nurse_caps as $cap => $grant ) {
				if ( $grant ) {
					$role->add_cap( $cap );
				}
			}
		}

		$admin_caps = array(
			self::CAP_MANAGE,
			self::CAP_MANAGE_ORDERS,
			self::CAP_MANAGE_NURSES,
			self::CAP_NURSE_PANEL,
			self::CAP_PATIENT_PANEL,
		);

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( $admin_caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}

		foreach ( array( 'subscriber', 'customer' ) as $role_name ) {
			$patient_role = get_role( $role_name );
			if ( $patient_role ) {
				$patient_role->add_cap( self::CAP_PATIENT_PANEL );
			}
		}
	}

	public static function remove_caps_on_deactivate(): void {
		$admin = get_role( 'administrator' );
		if ( ! $admin ) {
			return;
		}

		$admin->remove_cap( self::CAP_MANAGE );
		$admin->remove_cap( self::CAP_MANAGE_ORDERS );
		$admin->remove_cap( self::CAP_MANAGE_NURSES );
		// Keep nurse role and patient caps so users are not broken.
	}

	public static function user_is_nurse( ?int $user_id = null ): bool {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
		if ( ! $user || ! $user->ID ) {
			return false;
		}
		return in_array( self::ROLE_NURSE, (array) $user->roles, true )
			|| user_can( $user, self::CAP_NURSE_PANEL );
	}

	public static function user_can_manage( ?int $user_id = null ): bool {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
		if ( ! $user || ! $user->ID ) {
			return false;
		}
		return user_can( $user, self::CAP_MANAGE ) || user_can( $user, 'manage_options' );
	}
}
