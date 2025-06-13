<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class AssociationUsersField extends AssociationField {
	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );

		$this->setPostTypes( [ 
			[ 
				'type' => 'user',
			],
		] );
	}

	public function getPostFieldValue( int $parentId ): false|array {
		$users = parent::getPostFieldValue( $parentId );
		$result = [];
		foreach ( $users as $userId ) {
			if ( $userId && ( $user = get_userdata( $userId ) ) ) {
				$result[ $user->ID ] = [ 
					'id' => $user->ID,
					'display_name' => $user->display_name,
					'user_email' => $user->user_email,
				];
				continue;
			}
			return false;
		}
		return $result;
	}

	public function renderValue( int $parentId ): string {
		$users = $this->getPostFieldValue( $parentId );

		return static::renderEntities(
			$users,
			fn( $id ) => get_userdata( $id ),
			fn( $user ) => get_author_posts_url( $user->ID ),
			fn( $user ) => $user->display_name
		);
	}
}
