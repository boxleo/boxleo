<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject', 'description', 'author', 'publish_date', 'expiration_date',
        'is_active', 'attachment', 'priority', 'status',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        // 'publish_date' => 'datetime',
        // 'expiration_date' => 'datetime',
    ];

    //
    // The real relationship (renamed to avoid conflict)
    // public function authorUser()
    // {
    //     return $this->belongsTo(User::class, 'author', 'id');
    // }

    // // Virtual attribute to keep using `$announcement->author`
    // public function getAuthorAttribute()
    // {
    //     return $this->authorUser;
    // }


    public function attachments()
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'announcement_department');
    }

}
