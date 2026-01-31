<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - OpenPBBG Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-2xl">
                    <h1 class="text-5xl font-bold text-white mb-2">
                        🎮 OpenPBBG
                    </h1>
                    <p class="text-gray-300 text-lg">Game Engine Installer</p>
                </div>
            </div>

            <!-- Progress Steps -->
            @if(!isset($hideProgress))
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    @php
                        $steps = [
                            'welcome' => 'Welcome',
                            'requirements' => 'Requirements',
                            'database' => 'Database',
                            'settings' => 'Settings',
                            'install' => 'Install',
                            'admin' => 'Admin',
                            'complete' => 'Complete'
                        ];
                        $currentRoute = Route::currentRouteName();
                        $currentStep = str_replace('installer.', '', $currentRoute);
                        $stepKeys = array_keys($steps);
                        $currentIndex = array_search($currentStep, $stepKeys);
                    @endphp

                    @foreach($steps as $key => $label)
                        @php
                            $stepIndex = array_search($key, $stepKeys);
                            $isActive = $stepIndex === $currentIndex;
                            $isCompleted = $stepIndex < $currentIndex;
                        @endphp
                        
                        <div class="flex-1 flex items-center">
                            <div class="flex items-center justify-center">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                        {{ $isCompleted ? 'bg-green-500 text-white' : ($isActive ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-400') }}">
                                        @if($isCompleted)
                                            ✓
                                        @else
                                            {{ $stepIndex + 1 }}
                                        @endif
                                    </div>
                                    <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                        <span class="text-xs {{ $isActive ? 'text-purple-400 font-semibold' : 'text-gray-500' }}">
                                            {{ $label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="flex-1 h-1 mx-2 {{ $isCompleted ? 'bg-green-500' : 'bg-gray-700' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Content Card -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl overflow-hidden mt-12">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-400 text-sm">
                <p>OpenPBBG &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
