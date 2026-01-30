@extends('installer.layout')

@section('title', 'Installation Complete')

@php
    $hideProgress = true;
@endphp

@section('content')
<div class="p-12 text-center">
    <div class="text-6xl mb-6">🎉</div>
    
    <h2 class="text-4xl font-bold text-white mb-4">Installation Complete!</h2>
    
    <p class="text-gray-300 text-lg mb-8">
        Your Gangster Legends game engine is ready to go!
    </p>

    <div class="bg-white/5 rounded-xl p-8 mb-8 text-left max-w-2xl mx-auto">
        <h3 class="text-xl font-semibold text-white mb-4">🚀 Next Steps:</h3>
        
        <div class="space-y-4">
            <div class="flex items-start">
                <span class="text-purple-400 mr-3 mt-1">1.</span>
                <div>
                    <strong class="text-white">Access Your Game</strong>
                    <p class="text-gray-400 text-sm">Visit your game at: <a href="/" class="text-purple-400 hover:underline">{{ config('app.url') }}</a></p>
                </div>
            </div>
            
            <div class="flex items-start">
                <span class="text-purple-400 mr-3 mt-1">2.</span>
                <div>
                    <strong class="text-white">Access Admin Panel</strong>
                    <p class="text-gray-400 text-sm">Manage your game at: <a href="/admin" class="text-purple-400 hover:underline">{{ config('app.url') }}/admin</a></p>
                </div>
            </div>
            
            <div class="flex items-start">
                <span class="text-purple-400 mr-3 mt-1">3.</span>
                <div>
                    <strong class="text-white">Install Modules</strong>
                    <p class="text-gray-400 text-sm">Go to Admin Panel → Admin Panel → Module Manager</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <span class="text-purple-400 mr-3 mt-1">4.</span>
                <div>
                    <strong class="text-white">Add Content</strong>
                    <p class="text-gray-400 text-sm">Create crimes, items, locations, and customize your game</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-4 justify-center">
        <a href="/" 
           class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
            Visit Your Game
        </a>
        <a href="/admin" 
           class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
            Go to Admin Panel
        </a>
    </div>

    <div class="mt-8 p-6 bg-yellow-500/10 border border-yellow-500/30 rounded-lg max-w-2xl mx-auto">
        <p class="text-yellow-200 text-sm">
            <strong>⚠️ Security Tip:</strong> For production environments, disable debug mode and secure your .env file permissions.
        </p>
    </div>
</div>
@endsection
