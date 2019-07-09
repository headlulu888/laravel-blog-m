<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Storage;

/**
 * Class Post
 * @package App
 */
class Post extends Model
{
    use Sluggable;

    /**
     *
     */
    const IS_DRAFT = 0;
    /**
     *
     */
    const IS_PUBLIC = 1;

    /**
     * @var array
     */
    protected $fillable = ['title', 'content', 'date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',
            'tag_id'
        );
    }

    /**
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * @param $fields
     * @return Post
     */
    public static function add($fields)
    {
        $post = new self;
        $post->fill($fields);
        $post->user_id = 1;
        $post->save();

        return $post;
    }

    /**
     * @param $fields
     */
    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    /**
     * @throws \Exception
     */
    public function remove()
    {
        $this->removeImage();
        $this->delete();
    }

    /**
     *
     */
    public function removeImage()
    {
        if ($this->image != null) {
            Storage::delete('uploads/' . $this->image);
        }
    }

    /**
     * @param $image
     */
    public function uploadImage($image)
    {
        if ($image == null) return;

        $this->removeImage();
        Storage::delete('uploads/' . $this->image);
        $filename = str_random(10) . '.' . $image->extension();
        $image->storeAs('uploads', $filename);
        $this->image = $filename;
        $this->save();
    }

    /**
     * @param $id
     */
    public function setCategory($id)
    {
        if ($id == null) { return; }

        $this->category_id = $id;
        $this->save();
    }

    /**
     * @param $ids
     */
    public function setTags($ids)
    {
        if ($ids == null) { return; }

        $this->tags()->sync($ids);
    }

    /**
     *
     */
    public function setDraft()
    {
        $this->status = Post::IS_DRAFT;
        $this->save();
    }

    /**
     *
     */
    public function setPublic()
    {
        $this->status = Post::IS_PUBLIC;
        $this->save();
    }

    /**
     * @param $value
     */
    public function toggleStatus($value)
    {
        if ($value == null) {
            return $this->setDraft();
        }

        return $this->setPublic();
    }

    /**
     *
     */
    public function setFeatured()
    {
        $this->is_featured = 1;
        $this->save();
    }

    /**
     *
     */
    public function setStandart()
    {
        $this->is_featured = 0;
        $this->save();
    }

    /**
     * @param $value
     */
    public function toggleFeatured($value)
    {
        if ($value == null) {
            return $this->setStandart();
        }

        return $this->setFeatured();
    }

    /**
     * @return string
     */
    public function getImage()
    {
        if ($this->image == null) {
            return '/img/no-image.png';
        }
        return '/uploads/' . $this->image;
    }

    /**
     * @param $value
     */
    public function setDateAttributes($value)
    {
        $date = Carbon::createFromFormat('d/m/y', $value)->format('Y-m-d');
        $this->attributes['date'] = $date;
    }

    /**
     * @param $value
     * @return string
     */
    public function getDateAttributes($value)
    {
        $date = Carbon::createFromFormat('Y-m-d', $value)->format('d/m/y');

        return $date;
    }

    /**
     * @return string
     */
    public function getCategoryTitle()
    {
        return $this->category != null
            ? $this->category->title
            : 'Нет категории';
    }

    /**
     * @return string
     */
    public function getTagsTitles()
    {
        return (!$this->tags->isEmpty())
            ? implode(', ', $this->tags->pluck('title')->all())
            : 'Нет тегов';

    }
}
