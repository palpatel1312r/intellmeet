<?php
// app/Models/TaskAttachment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'uploaded_by',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'file_extension',
        'file_size',
        'description'
    ];

    protected $appends = ['file_size_formatted', 'file_icon'];

    /**
     * Get the task that owns the attachment.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get formatted file size.
     */
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file icon based on file type.
     */
    public function getFileIconAttribute()
    {
        $icons = [
            'pdf' => 'fa-file-pdf text-red-500',
            'doc' => 'fa-file-word text-blue-500',
            'docx' => 'fa-file-word text-blue-500',
            'xls' => 'fa-file-excel text-green-500',
            'xlsx' => 'fa-file-excel text-green-500',
            'ppt' => 'fa-file-powerpoint text-orange-500',
            'pptx' => 'fa-file-powerpoint text-orange-500',
            'jpg' => 'fa-file-image text-purple-500',
            'jpeg' => 'fa-file-image text-purple-500',
            'png' => 'fa-file-image text-purple-500',
            'gif' => 'fa-file-image text-purple-500',
            'zip' => 'fa-file-archive text-yellow-500',
            'rar' => 'fa-file-archive text-yellow-500',
            'txt' => 'fa-file-alt text-gray-500',
        ];

        return $icons[$this->file_extension] ?? 'fa-file text-gray-500';
    }
}
