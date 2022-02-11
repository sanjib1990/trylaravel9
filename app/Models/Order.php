<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "external_order_id",
        "student_email",
        "student_id",
        "course_id",
        "course_name",
        "product_id",
        "amount",
        "currency",
        "provider",
        "order_type",
        "action",
        "status"
    ];

    public function setAttributeExternalOrderId($value)
    {
        if (empty($value))
        {
            $value = "";
        }

        $this->external_order_id = "";
    }
}
