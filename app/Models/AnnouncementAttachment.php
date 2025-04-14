<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementAttachment extends Model
{
    protected $fillable = [
        'announcement_id',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'file_size'
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
