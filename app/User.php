<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     *
     */
    const IS_BANNED = 1;
    /**
     *
     */
    const IS_ACTIVE = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @param $fields
     * @return User
     */
    public static function add($fields)
    {
        $user = new static;
        $user->fill($fields);
        $user->save();

        return $user;
    }

    /**
     * @param $fields
     */
    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    public function generatePassword($password)
    {
        if ($password != null) {
            $this->password = bcrypt($password);
            $this->save();
        }
    }

    /**
     * @throws \Exception
     */
    public function remove()
    {
        $this->removeAvatar();
        $this->delete();
    }

    /**
     * @param $image
     */
    public function uploadAvatar($image)
    {
        if ($image == null) { return; }

        $this->removeAvatar();

        $filename = str_random(10) . '.' . $image->extension();
        $image->storeAs('uploads', $filename);
        $this->avatar = $filename;
        $this->save();
    }

    public function removeAvatar()
    {
        if ($this->avatar != null) {
            Storage::delete('uploads/' . $this->avatar);
        }
    }

    /**
     * @return string
     */
    public function getImage()
    {
        if ($this->avatar == null) {
            return '/img/no-image.png';
        }

        return '/uploads/' . $this->avatar;
    }

    /**
     *
     */
    public function makeAdmin()
    {
        $this->is_admin = 1;
        $this->save();
    }

    /**
     * @param string $value
     */
    public function makeNormal($value = '')
    {
        $this->is_admin = 0;
        $this->save();
    }

    /**
     * @param $value
     */
    public function toggleAdmin($value)
    {
        if ($value == null) {
            return $this->makeNormal();
        }

        return $this->makeAdmin();
    }

    /**
     *
     */
    public function ban()
    {
        $this->status = User::IS_BANNED;
        $this->save();
    }

    /**
     *
     */
    public function unban()
    {
        $this->status = User::IS_ACTIVE;
        $this->save();
    }

    /**
     * @param $value
     */
    public function toggleBan($value)
    {
        if ($value == null) {
            return $this->unban();
        }

        return $this->ban();
    }
}
