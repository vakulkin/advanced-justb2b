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
						'valid' => false,
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

	public function renderValue( int $parentId ): string {
		return static::renderEntities(
			$this->getValue( $parentId ),
			fn( $id ) => get_userdata( $id ),
			fn( $user ) => get_author_posts_url( $user->ID ),
			fn( $user ) => $user->display_name
		);
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'user';
		$field['multiple'] = 1;
		return $field;
	}
}
