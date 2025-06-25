<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

abstract class AbstractAssociationPostsField extends AbstractAssociationField {

	protected array $postTypes = [];

	public function setPostTypes( array $postTypes ): static {
		$this->postTypes = $postTypes;
		return $this;
	}

	public function getValue( int $id ): array {
		$posts = parent::getValue( $id );
		$result = [];
		if ( is_array( $posts ) ) {
			foreach ( $posts as $postId ) {
				if ( $postId && get_post_status( $postId ) === 'publish' ) {
					$result[ $postId ] = [ 
						'key' => get_the_title( $postId ),
						'type' => get_post_type($postId),
						'valid' => true,
					];
				} else {
					$result[ $postId ] = [ 
						'key' => "removed post {$postId}",
						'type' => "error",
						'valid' => false,
					];
				}
			}
		}
		return $result;
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'relationship';
		$field['post_type'] = $this->postTypes;
		$field['min'] = 0;
		return $field;
	}

}
