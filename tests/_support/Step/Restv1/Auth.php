<?php

namespace Step\Restv1;

trait Auth  {
	/**
	 * Authenticates a user with a role for the scope of the test.
	 *
	 * The method will create a user in WordPress with the "user" login and password, create a valid "wp_rest" nonce
	 * for the user and set the nonce on the "X-WP-Nonce" header.
	 *
	 * @param string $role A valid WordPress user role, e.g. 'subscriber' or `administrator`; use 'visitor' to indicate
	 *                     a user with an ID of 0.
	 *
	 * @see https://codex.wordpress.org/Roles_and_Capabilities#Summary_of_Roles
	 *
	 * @return string The generated and valid nonce.
	 */
	public function generate_nonce_for_role( $role ): string {
		$I = $this;

		$user_id = 0;

		if ( 'visitor' !== $role ) {
			$user_id = $I->haveUserInDatabase( 'user', $role, [ 'user_pass' => 'user' ] );

			// Login to get the cookies.
			$I->loginAs( 'user', 'user' );
		}

		wp_set_current_user( $user_id );

		// This will leverage the code in the `restv1-wp-verify-nonce.php` mu-plugin.
		$nonce = wp_create_nonce( "{$role}|{$user_id}" );

		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		return $nonce;
	}
}
