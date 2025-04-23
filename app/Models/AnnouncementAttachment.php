<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnouncementAttachment extends Model
{


    use HasFactory, SoftDeletes;
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
