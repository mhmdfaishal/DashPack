<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $table = 'toko';
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, array $filters){
        $query->when($filters['search'] ?? false, function($query, $search){
            return $query->where('kotakabupaten',  $search);
        });

        $query->when($filters['location'] ?? false, function($query, $location){
            return $query->where('kotakabupaten', $location);
        });

    }
}
