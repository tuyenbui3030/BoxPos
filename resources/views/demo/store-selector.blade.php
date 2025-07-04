<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoxPos - Demo Applications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">🎯 BoxPos Demo Applications</h1>
                <p class="text-xl text-gray-600">Chọn ứng dụng để xem demo</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Vật liệu xây dựng -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="bg-gradient-to-r from-orange-500 to-red-600 p-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            🏗️ Quản lý Vật liệu Xây dựng
                        </h2>
                        <p class="text-orange-100 mt-2">Công ty ABC - Vật liệu xây dựng</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-gray-600">
                                <span class="w-4 h-4 bg-orange-500 rounded-full mr-3"></span>
                                <span>8 danh mục vật liệu</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <span class="w-4 h-4 bg-orange-500 rounded-full mr-3"></span>
                                <span>12 sản phẩm (Xi măng, Cát, Đá, Thép...)</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <span class="w-4 h-4 bg-orange-500 rounded-full mr-3"></span>
                                <span>Batch tracking & Quality control</span>
                            </div>
                        </div>
                        <a href="{{ route('demo.materials') }}" 
                           class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block text-center">
                            Xem Demo Vật liệu →
                        </a>
                    </div>
                </div>

                <!-- Kho cà phê -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="bg-gradient-to-r from-amber-600 to-yellow-700 p-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            ☕ Kho Cà phê Highlands
                        </h2>
                        <p class="text-amber-100 mt-2">Highlands Coffee - Quản lý kho</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-gray-600">
                                <span class="w-4 h-4 bg-amber-600 rounded-full mr-3"></span>
                                <span>8 danh mục cà phê</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <span class="w-4 h-4 bg-amber-600 rounded-full mr-3"></span>
                                <span>12 sản phẩm (Arabica, Robusta, Blend...)</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <span class="w-4 h-4 bg-amber-600 rounded-full mr-3"></span>
                                <span>Origin tracking & Roast management</span>
                            </div>
                        </div>
                        <a href="{{ route('demo.coffee') }}" 
                           class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block text-center">
                            Xem Demo Cà phê →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick access URLs -->
            <div class="mt-12 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Quick Access URLs:</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong>Vật liệu xây dựng:</strong><br>
                        <code class="bg-gray-100 px-2 py-1 rounded">http://localhost/demo/vat-lieu</code>
                    </div>
                    <div>
                        <strong>Kho cà phê:</strong><br>
                        <code class="bg-gray-100 px-2 py-1 rounded">http://localhost/demo/ca-phe</code>
                    </div>
                </div>
            </div>

            <!-- Login info -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">🔑 Login Credentials:</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm text-blue-800">
                    <div>
                        <strong>Vật liệu:</strong> admin@vat-lieu-xay-dung.com
                    </div>
                    <div>
                        <strong>Cà phê:</strong> admin@kho-ca-phe.com
                    </div>
                </div>
                <p class="text-blue-700 mt-2"><strong>Password:</strong> password</p>
            </div>
        </div>
    </div>
</body>
</html>
