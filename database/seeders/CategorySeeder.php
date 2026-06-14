<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Thú bông',
                'slug'        => 'thu-bong',
                'description' => 'Những chú thú bông đáng yêu — gấu teddy, thỏ bông, mèo bông... món quà không bao giờ lỗi thời.',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Hoa sáp',
                'slug'        => 'hoa-sap',
                'description' => 'Hoa sáp thơm giữ được vẻ đẹp mãi mãi — không héo, không tàn, đẹp như ngày đầu.',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Hộp quà',
                'slug'        => 'hop-qua',
                'description' => 'Hộp quà được thiết kế tinh tế, kết hợp nhiều món — hoàn hảo cho mọi dịp đặc biệt.',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Chocolate',
                'slug'        => 'chocolate',
                'description' => 'Chocolate nhập khẩu cao cấp — ngọt ngào như tình yêu, tan chảy trong từng khoảnh khắc.',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Phụ kiện couple',
                'slug'        => 'phu-kien-couple',
                'description' => 'Vòng tay đôi, móc khóa đôi, áo đôi — lưu giữ kỷ niệm theo cách dễ thương nhất.',
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Thiệp',
                'slug'        => 'thiep',
                'description' => 'Thiệp handmade và thiệp in cao cấp — gửi gắm lời yêu thương qua từng nét chữ.',
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Nến thơm',
                'slug'        => 'nen-thom',
                'description' => 'Nến thơm tạo không gian lãng mạn, thư giãn — hương thơm nhẹ nhàng, tinh tế.',
                'sort_order'  => 7,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['is_active' => true])
            );
        }

        $this->command->info('✅ Categories seeded!');
    }
}