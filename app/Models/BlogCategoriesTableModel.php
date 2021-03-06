<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BlogCategoriesTableModel extends Model
{
    use Notifiable;
    protected $table = "blog_categories";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $guarded = [];
}
