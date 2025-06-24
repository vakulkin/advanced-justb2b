<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class AssociationUsersField extends AbstractAssociationField {

	public function getValue( int $id ): array {
		$users = parent::getValue( $id );
		$result = [];
		if ( is_array( $users ) ) {
			foreach ( $users as $userId ) {
				if ( $userId && ( $user = get_userdata( $userId ) ) ) {
					$result[ $userId ] = [ 
						'key' => $user->user_email,
						'valid' => true,
					];
				} else {
					$result[ $userId ] = [ 
						'key' => "removed user {$userId}",
						'valid' => false,
					];
				}
			}
		}
		return $result;
	}

	public function toACF( $index = 0 ): array {
		$field = parent::toACF( $index );
		$field['type'] = 'user';
		$field['multiple'] = 1;
		return $field;
	}
}
