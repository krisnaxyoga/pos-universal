<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Payment Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="text-center">
                <div class="text-green-500 text-6xl mb-4">✓</div>
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Mock Payment Success</h1>
                <p class="text-gray-600 mb-6">{{ $message }}</p>
                <p class="text-sm text-gray-500 mb-6">Session: {{ $session }}</p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-yellow-800 text-sm">
                        <strong>Note:</strong> This is a mock payment response because the iPaymu API is currently unavailable. 
                        In production, this would redirect to the actual payment confirmation page.
                    </p>
                </div>
                
                <div class="space-y-3">
                    <button onclick="window.close()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Close Window
                    </button>
                    <a href="{{ route('dashboard') }}" class="block w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-center">
                        Return to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>