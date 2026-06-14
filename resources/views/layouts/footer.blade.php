<footer class="bg-white border-t border-pink-100 mt-16">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xl">🎁</span>
                    <span class="font-bold text-pink-500">Gift Shop</span>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Quà tặng yêu thương — gói trọn cảm xúc trong từng món quà.
                </p>
            </div>

            {{-- Links --}}
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Danh mục</h3>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><a href="#" class="hover:text-pink-500 transition-colors">Thú bông</a></li>
                    <li><a href="#" class="hover:text-pink-500 transition-colors">Hoa sáp</a></li>
                    <li><a href="#" class="hover:text-pink-500 transition-colors">Hộp quà</a></li>
                    <li><a href="#" class="hover:text-pink-500 transition-colors">Chocolate</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Liên hệ</h3>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li>📍 Vĩnh Long, Việt Nam</li>
                    <li>📞 0909 xxx xxx</li>
                    <li>✉️ hello@giftshop.vn</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-pink-100 mt-8 pt-6 text-center text-xs text-gray-400">
            © {{ date('Y') }} Gift Shop. Made with 💗
        </div>
    </div>
</footer>