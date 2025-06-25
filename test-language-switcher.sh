#!/bin/bash

echo "🔄 Starting Language Switcher Test..."

# Function to check if PHP artisan is available
check_artisan() {
    if ! command -v php &> /dev/null || [ ! -f "artisan" ]; then
        echo "❌ PHP or Laravel artisan not found. Please run this from the Laravel project root."
        exit 1
    fi
}

# Function to start development server in background
start_server() {
    echo "🚀 Starting Laravel development server..."
    php artisan serve --host=localhost --port=8000 &
    SERVER_PID=$!
    echo "⏱️  Waiting for server to start..."
    sleep 3
    
    # Check if server is running
    if curl -s http://localhost:8000 > /dev/null; then
        echo "✅ Server started successfully on http://localhost:8000"
        return 0
    else
        echo "❌ Failed to start server"
        return 1
    fi
}

# Function to stop server
stop_server() {
    if [ ! -z "$SERVER_PID" ]; then
        echo "🛑 Stopping development server (PID: $SERVER_PID)..."
        kill $SERVER_PID 2>/dev/null
        wait $SERVER_PID 2>/dev/null
        echo "✅ Server stopped"
    fi
}

# Function to test language switching
test_language_switching() {
    echo "🔍 Testing language switching..."
    
    # Test English (default)
    echo "📝 Testing English locale..."
    response=$(curl -s -L http://localhost:8000/language-test)
    if [[ $response == *"Language Switcher Test"* ]]; then
        echo "✅ English locale working"
    else
        echo "❌ English locale test failed"
    fi
    
    # Test Vietnamese switching
    echo "📝 Testing Vietnamese locale switch..."
    response=$(curl -s -L -c /tmp/cookies.txt -b /tmp/cookies.txt http://localhost:8000/language/switch/vi)
    if curl -s -L -b /tmp/cookies.txt http://localhost:8000/language-test | grep -q "Kiểm tra bộ chuyển đổi ngôn ngữ"; then
        echo "✅ Vietnamese locale working"
    else
        echo "❌ Vietnamese locale test failed"
    fi
    
    # Clean up cookies
    rm -f /tmp/cookies.txt
}

# Function to test route accessibility
test_routes() {
    echo "🔍 Testing route accessibility..."
    
    routes=(
        "/language-test"
        "/localization-test" 
        "/localization-example"
    )
    
    for route in "${routes[@]}"; do
        status=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000$route)
        if [ "$status" -eq 200 ]; then
            echo "✅ $route - OK ($status)"
        else
            echo "❌ $route - Failed ($status)"
        fi
    done
}

# Function to check file existence
check_files() {
    echo "🔍 Checking important files..."
    
    files=(
        "app/Livewire/LanguageSwitcher.php"
        "resources/views/livewire/language-switcher.blade.php"
        "lang/en/app.php"
        "lang/vi/app.php"
        "app/Http/Controllers/LanguageController.php"
        "app/Http/Middleware/LocalizationMiddleware.php"
    )
    
    for file in "${files[@]}"; do
        if [ -f "$file" ]; then
            echo "✅ $file exists"
        else
            echo "❌ $file missing"
        fi
    done
}

# Function to test Livewire component
test_livewire() {
    echo "🔍 Testing Livewire component..."
    
    # Check if Livewire component is registered
    if php artisan route:list | grep -q "livewire"; then
        echo "✅ Livewire routes registered"
    else
        echo "❌ Livewire routes not found"
    fi
    
    # Test component class
    if php artisan tinker --execute="echo class_exists('App\\Livewire\\LanguageSwitcher') ? 'EXISTS' : 'NOT_FOUND';" 2>/dev/null | grep -q "EXISTS"; then
        echo "✅ LanguageSwitcher component class found"
    else
        echo "❌ LanguageSwitcher component class not found"
    fi
}

# Trap to ensure server is stopped on script exit
trap stop_server EXIT

# Main execution
main() {
    echo "🧪 Laravel Language Switcher Test Suite"
    echo "========================================"
    
    check_artisan
    check_files
    test_livewire
    
    if start_server; then
        test_routes
        test_language_switching
        echo ""
        echo "🎉 Test completed! Check the results above."
        echo "💡 You can manually test the language switcher at:"
        echo "   📍 http://localhost:8000/language-test"
        echo "   📍 http://localhost:8000/localization-test"
        echo ""
        echo "⏹️  Press Ctrl+C to stop the server and exit"
        
        # Keep server running for manual testing
        read -p "Press Enter to stop the server..."
    else
        echo "❌ Could not start server for testing"
        exit 1
    fi
}

# Run the main function
main
