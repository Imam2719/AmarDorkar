<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_bn'=>'ব্যাগ ও আনুষাঙ্গিক',   'name_en'=>'Bags & Accessories',  'icon'=>'fas fa-shopping-bag', 'color'=>'#e91e63','sort_order'=>1],
            ['name_bn'=>'পোশাক ও পরিধান',         'name_en'=>'Clothing & Fashion',  'icon'=>'fas fa-tshirt',       'color'=>'#9c27b0','sort_order'=>2],
            ['name_bn'=>'ইলেকট্রনিক্স',           'name_en'=>'Electronics',         'icon'=>'fas fa-laptop',       'color'=>'#1565c0','sort_order'=>3],
            ['name_bn'=>'জুতা ও ফুটওয়্যার',      'name_en'=>'Footwear',            'icon'=>'fas fa-shoe-prints',  'color'=>'#bf360c','sort_order'=>4],
            ['name_bn'=>'গৃহস্থালি ও রান্নাঘর',  'name_en'=>'Home & Kitchen',      'icon'=>'fas fa-home',         'color'=>'#2e7d32','sort_order'=>5],
            ['name_bn'=>'মোবাইল আনুষাঙ্গিক',     'name_en'=>'Mobile Accessories',  'icon'=>'fas fa-mobile-alt',   'color'=>'#283593','sort_order'=>6],
            ['name_bn'=>'স্বাস্থ্য ও সৌন্দর্য',  'name_en'=>'Health & Beauty',     'icon'=>'fas fa-heartbeat',    'color'=>'#c62828','sort_order'=>7],
            ['name_bn'=>'খেলাধুলা ও ফিটনেস',     'name_en'=>'Sports & Fitness',    'icon'=>'fas fa-futbol',       'color'=>'#558b2f','sort_order'=>8],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name_en' => $cat['name_en']], array_merge($cat, ['status' => true]));
        }
    }
}