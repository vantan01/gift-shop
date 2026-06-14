<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Thú bông
            [
                'category_slug'     => 'thu-bong',
                'name'              => 'Gấu Teddy Ôm Tim Hồng',
                'slug'              => 'gau-teddy-om-tim-hong',
                'short_description' => 'Gấu teddy mềm mại ôm trái tim hồng, size 40cm. Quà sinh nhật ý nghĩa.',
                'description'       => "Chú gấu teddy đáng yêu với lớp lông mềm mịn, ôm trái tim hồng xinh xắn. Kích thước 40cm vừa đủ để ôm ấp. Chất liệu an toàn, không gây dị ứng.\n\nMàu sắc: Nâu kem, tim hồng\nChất liệu: Bông PP cao cấp\nKích thước: 40cm",
                'price'             => 199000,
                'compare_price'     => 249000,
                'stock'             => 45,
                'is_featured'       => true,
            ],
            [
                'category_slug'     => 'thu-bong',
                'name'              => 'Thỏ Bông Trắng Baby',
                'slug'              => 'tho-bong-trang-baby',
                'short_description' => 'Thỏ bông trắng tinh khôi, tai dài dễ thương. Size 30cm.',
                'description'       => "Chú thỏ bông trắng muốt với đôi tai dài đặc trưng, đôi mắt nút đen tròn. Nhồi bông đầy đặn, dáng đứng ổn định.\n\nMàu sắc: Trắng\nKích thước: 30cm\nChất liệu: Vải nhung + Bông PP",
                'price'             => 149000,
                'compare_price'     => null,
                'stock'             => 3,
                'is_featured'       => false,
            ],
            [
                'category_slug'     => 'thu-bong',
                'name'              => 'Gấu Dâu Tây Hồng Pastel',
                'slug'              => 'gau-dau-tay-hong-pastel',
                'short_description' => 'Gấu dâu tây tông hồng pastel ngọt ngào, size 35cm.',
                'description'       => "Chú gấu phiên bản dâu tây với tông hồng pastel cực kỳ ngọt ngào. Phù hợp làm quà tặng sinh nhật, lễ tình nhân.\n\nKích thước: 35cm\nMàu: Hồng pastel",
                'price'             => 179000,
                'compare_price'     => 220000,
                'stock'             => 20,
                'is_featured'       => true,
            ],

            // Hoa sáp
            [
                'category_slug'     => 'hoa-sap',
                'name'              => 'Hộp Hoa Hồng Sáp 9 Bông',
                'slug'              => 'hop-hoa-hong-sap-9-bong',
                'short_description' => 'Hộp hoa hồng sáp 9 bông tông đỏ-hồng. Không tàn, giữ mãi mãi.',
                'description'       => "Hộp hoa hồng sáp cao cấp với 9 bông hoa được xếp tinh tế trong hộp nhung. Hoa sáp không tàn, giữ nguyên vẻ đẹp theo năm tháng.\n\nSố bông: 9 bông\nTông màu: Đỏ - Hồng\nHộp: Nhung đen sang trọng",
                'price'             => 299000,
                'compare_price'     => 350000,
                'stock'             => 30,
                'is_featured'       => true,
            ],
            [
                'category_slug'     => 'hoa-sap',
                'name'              => 'Bó Hoa Sáp Pastel Mix',
                'slug'              => 'bo-hoa-sap-pastel-mix',
                'short_description' => 'Bó hoa sáp mix nhiều loại tông pastel nhẹ nhàng, thơm dịu.',
                'description'       => "Bó hoa sáp kết hợp hoa hồng, cúc, baby breath tông pastel. Mỗi bó là một tác phẩm thủ công độc đáo.\n\nSố bông: 12-15 bông mix\nHương: Nhẹ nhàng, dịu dàng\nWrapping: Giấy kraft + ribbon",
                'price'             => 249000,
                'compare_price'     => null,
                'stock'             => 15,
                'is_featured'       => false,
            ],

            // Hộp quà
            [
                'category_slug'     => 'hop-qua',
                'name'              => 'Gift Box "You & Me" Couple',
                'slug'              => 'gift-box-you-and-me-couple',
                'short_description' => 'Hộp quà đôi gồm: gấu bông đôi + thiệp + chocolate + nến thơm.',
                'description'       => "Bộ quà couple hoàn hảo bao gồm:\n- 1 cặp gấu bông đôi (20cm)\n- 1 thiệp handmade viết tay\n- 1 hộp chocolate 6 viên\n- 1 nến thơm hoa hồng\n- Hộp carton in hoa cứng cáp\n\nPhù hợp: Sinh nhật, kỷ niệm, Valentine",
                'price'             => 499000,
                'compare_price'     => 580000,
                'stock'             => 10,
                'is_featured'       => true,
            ],
            [
                'category_slug'     => 'hop-qua',
                'name'              => 'Mini Gift Box Sinh Nhật',
                'slug'              => 'mini-gift-box-sinh-nhat',
                'short_description' => 'Hộp quà mini dễ thương: thú bông nhỏ + thiệp + kẹo.',
                'description'       => "Hộp quà mini xinh xắn dành cho những dịp tặng nhẹ nhàng:\n- Thú bông mini 15cm\n- Thiệp sinh nhật\n- Kẹo viên mix màu\n- Ribbon nơ hồng",
                'price'             => 199000,
                'compare_price'     => null,
                'stock'             => 25,
                'is_featured'       => false,
            ],

            // Chocolate
            [
                'category_slug'     => 'chocolate',
                'name'              => 'Ferrero Rocher Hộp 16 Viên',
                'slug'              => 'ferrero-rocher-hop-16-vien',
                'short_description' => 'Chocolate Ferrero Rocher hộp 16 viên sang trọng, nhập khẩu chính hãng.',
                'description'       => "Hộp chocolate Ferrero Rocher 16 viên trong hộp nhựa trong sang trọng. Vị chocolate đậm đà, nhân hạt phỉ giòn tan.\n\nSố viên: 16\nXuất xứ: Ý\nHạn sử dụng: 12 tháng",
                'price'             => 279000,
                'compare_price'     => 320000,
                'stock'             => 50,
                'is_featured'       => true,
            ],
            [
                'category_slug'     => 'chocolate',
                'name'              => 'Socola Handmade Trái Tim',
                'slug'              => 'socola-handmade-trai-tim',
                'short_description' => 'Socola handmade hình trái tim, 12 viên, vị đa dạng.',
                'description'       => "Socola handmade được làm thủ công từ chocolate Bỉ cao cấp. 12 viên hình trái tim với 4 vị: Sữa, Đen, Dâu tây, Matcha.\n\nSố viên: 12\nChocolate: Callebaut Belgium\nBảo quản: Dưới 25°C",
                'price'             => 189000,
                'compare_price'     => null,
                'stock'             => 0,
                'is_featured'       => false,
            ],

            // Phụ kiện couple
            [
                'category_slug'     => 'phu-kien-couple',
                'name'              => 'Vòng Tay Đôi Bạc 925',
                'slug'              => 'vong-tay-doi-bac-925',
                'short_description' => 'Vòng tay đôi bạc 925 khắc tên theo yêu cầu.',
                'description'       => "Vòng tay đôi được làm từ bạc 925 thật, có thể khắc tên hoặc ngày kỷ niệm theo yêu cầu.\n\nChất liệu: Bạc 925\nKhắc tên: Miễn phí\nĐóng gói: Hộp nhung đỏ",
                'price'             => 349000,
                'compare_price'     => 420000,
                'stock'             => 20,
                'is_featured'       => true,
            ],

            // Thiệp
            [
                'category_slug'     => 'thiep',
                'name'              => 'Thiệp Handmade 3D Pop-up',
                'slug'              => 'thiep-handmade-3d-pop-up',
                'short_description' => 'Thiệp 3D pop-up handmade, mở ra bông hoa nở. Kèm phong bì.',
                'description'       => "Thiệp pop-up 3D handmade độc đáo — khi mở ra, bông hoa nở ra bất ngờ. Kèm phong bì kraft và sticker trang trí.\n\nKích thước: 15x10cm\nKèm: Phong bì kraft + sticker\nCó thể viết thêm lời nhắn",
                'price'             => 49000,
                'compare_price'     => null,
                'stock'             => 100,
                'is_featured'       => false,
            ],

            // Nến thơm
            [
                'category_slug'     => 'nen-thom',
                'name'              => 'Nến Thơm Hoa Hồng Jasmine',
                'slug'              => 'nen-thom-hoa-hong-jasmine',
                'short_description' => 'Nến thơm soy wax hương hoa hồng + nhài. Cháy 40 giờ.',
                'description'       => "Nến thơm làm từ soy wax tự nhiên, hương hoa hồng kết hợp nhài tinh tế. Thời gian cháy lên đến 40 giờ.\n\nChất liệu: Soy wax 100%\nHương: Hoa hồng + Nhài\nThời gian cháy: 40h\nHũ: Thủy tinh tái sử dụng được",
                'price'             => 159000,
                'compare_price'     => 199000,
                'stock'             => 35,
                'is_featured'       => false,
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category_slug'])->first();

            if (! $category) {
                $this->command->warn("Category not found: {$data['category_slug']}");
                continue;
            }

            Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id'         => $category->id,
                    'name'                => $data['name'],
                    'slug'                => $data['slug'],
                    'short_description'   => $data['short_description'],
                    'description'         => $data['description'],
                    'price'               => $data['price'],
                    'compare_price'       => $data['compare_price'],
                    'stock'               => $data['stock'],
                    'is_active'           => true,
                    'is_featured'         => $data['is_featured'],
                    'low_stock_threshold' => 5,
                    'sold_count'          => 0,
                ]
            );
        }

        $this->command->info('✅ Products seeded!');
    }
}