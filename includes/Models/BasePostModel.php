<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

abstract class BasePostModel extends BaseModel
{
    use LazyLoaderTrait;

    protected static string $key;
    protected ?string $title = null;

    public function __construct(int $id)
    {
        parent::__construct($id);
    }

    public function getTitle(): string
    {
        $this->initTitle();
        return $this->title;
    }

    protected function initTitle(): void
    {
        $this->lazyLoad($this->title, function () {
            return get_the_title($this->id);
        });
    }

    public static function getKey(): string
    {
        return static::$key;
    }

    public static function getPrefixedKey(): string
    {
        return Prefixer::getPrefixed(static::getKey());
    }

    protected static function getAssociatedPosts(int $parentId, string $postType): array
    {
        $posts = carbon_get_post_meta($parentId, $postType);
        $published = [];

        foreach ($posts as $post) {
            $postId = (int) ($post['id'] ?? 0);
            if (get_post_status($postId) === 'publish') {
                $published[$postId] = $post;
            }
        }

        return $published;
    }

    protected static function getAssociatedTerms(int $parentId, string $metaKey): array
    {
        $terms = carbon_get_post_meta($parentId, $metaKey);
        $result = [];

        foreach ($terms as $termData) {
            $termId = (int) ($termData['id'] ?? 0);
            if ($termId && ($term = get_term($termId)) && !is_wp_error($term)) {
                $result[$term->term_id] = [
                    'id' => $term->term_id,
                    'taxonomy' => $term->taxonomy,
                ];
            }
        }

        return $result;
    }

    protected static function getAssociatedUsers(int $parentId, string $metaKey): array
    {
        $users = carbon_get_post_meta($parentId, $metaKey);
        $result = [];

        foreach ($users as $userData) {
            $userId = (int) ($userData['id'] ?? 0);
            if ($userId && ($user = get_userdata($userId))) {
                $result[$user->ID] = [
                    'id' => $user->ID,
                    'display_name' => $user->display_name,
                    'user_email' => $user->user_email,
                ];
            }
        }

        return $result;
    }
}
