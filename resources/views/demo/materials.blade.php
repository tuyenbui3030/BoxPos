<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $store->name }} - Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">🏗️ {{ $store->name }}</h1>
                        <p class="text-orange-100 mt-1">{{ $store->description }}</p>
                    </div>
                    <a href="{{ route('demo.stores') }}" 
                       class="bg-white text-orange-600 px-4 py-2 rounded-lg hover:bg-orange-50 transition-colors">
                        ← Quay lại
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Store Info -->
            <div class="bg-white rounded-lg shadow mb-8 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📋 Thông tin cửa hàng</h2>
                <div class="grid md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong>Địa chỉ:</strong><br>
                        {{ $store->address }}
                    </div>
                    <div>
                        <strong>Điện thoại:</strong><br>
                        {{ $store->phone }}
                    </div>
                    <div>
                        <strong>Email:</strong><br>
                        {{ $store->email }}
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="bg-white rounded-lg shadow mb-8 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📦 Danh mục vật liệu ({{ count($categories) }})</h2>
                <div class="grid md:grid-cols-4 gap-4">
                    @foreach($categories as $category)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 transition-colors">
                        <div class="font-medium text-gray-900">{{ $category->name }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ $category->code }}</div>
                        <div class="text-xs text-orange-600 mt-2">
                            {{ $products->where('category_id', $category->id)->count() }} sản phẩm
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">🛍️ Sản phẩm vật liệu ({{ count($products) }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá vốn</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá bán</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                            @php
                                $category = $categories->firstWhere('id', $product->category_id);
                                $stock = \DB::table('stocks')->where('product_id', $product->id)->first();
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $category->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <code class="bg-gray-100 px-2 py-1 rounded">{{ $product->code }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($product->cost_price) }} VND
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    {{ number_format($product->sale_price) }} VND
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($stock)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $stock->quantity > 50 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $stock->quantity }} {{ $product->unit }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Chưa có</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-8 bg-orange-50 border border-orange-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-orange-900 mb-4">✨ Tính năng đặc biệt cho Vật liệu Xây dựng:</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm text-orange-800">
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
                        <span>Batch tracking cho từng lô hàng</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
                        <span>Quality grade và warranty management</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
                        <span>Project allocation (residential, commercial, industrial)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
                        <span>Safety notes và storage requirements</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
