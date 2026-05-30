<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'profile_image', 'permissions'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password'    => 'hashed',
        'permissions' => 'array',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Super admin সব কিছুতে access পাবে।
     * Regular admin শুধু assigned permission গুলোতে।
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $perms = $this->permissions;

        if (empty($perms)) {
            return false;
        }

        return in_array($permission, $perms);
    }

    /**
     * সব available permissions এর definition।
     */
    public static function allPermissions(): array
    {
        return [
            'dashboard'   => ['label_en' => 'Dashboard',         'label_bn' => 'ড্যাশবোর্ড',       'icon' => 'fa-chart-pie'],
            'orders'      => ['label_en' => 'Orders',            'label_bn' => 'অর্ডার',            'icon' => 'fa-shopping-bag'],
            'products'    => ['label_en' => 'Products',          'label_bn' => 'পণ্য',              'icon' => 'fa-box'],
            'categories'  => ['label_en' => 'Categories',        'label_bn' => 'ক্যাটাগরি',         'icon' => 'fa-tags'],
            'discounts'   => ['label_en' => 'Discounts',         'label_bn' => 'ছাড়',              'icon' => 'fa-percent'],
            'flash_sales' => ['label_en' => 'Flash Sales',       'label_bn' => 'ফ্ল্যাশ সেল',      'icon' => 'fa-bolt'],
            'coupons'     => ['label_en' => 'Coupons',           'label_bn' => 'কুপন',              'icon' => 'fa-ticket-alt'],
            'banners'     => ['label_en' => 'Banners',           'label_bn' => 'ব্যানার',           'icon' => 'fa-image'],
            'sales'       => ['label_en' => 'Sales History',     'label_bn' => 'বিক্রয় ইতিহাস',   'icon' => 'fa-chart-line'],
            'customers'   => ['label_en' => 'Customers',         'label_bn' => 'গ্রাহক',            'icon' => 'fa-users'],
            'reviews'     => ['label_en' => 'Reviews',           'label_bn' => 'রিভিউ',             'icon' => 'fa-star'],
            'returns'     => ['label_en' => 'Returns & Refunds', 'label_bn' => 'রিটার্ন ও রিফান্ড', 'icon' => 'fa-undo'],
        ];
    }
}