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
						'type' => 'user',
						'valid' => true,
					];
				} else {
					$result[ $userId ] = [ 
						'key' => "removed user {$userId}",
						'type' => 'error',
						'valid' => false,
					];
				}
			}
		}
		return $result;
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'user';
		$field['multiple'] = 1;
		return $field;
	}
}
