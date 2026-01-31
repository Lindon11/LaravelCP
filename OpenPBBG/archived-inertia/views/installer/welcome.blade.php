@extends('installer.layout')

@section('title', 'Welcome')

@section('content')
<div class="p-12 text-center">
    <div class="text-6xl mb-6">🎮</div>
    
    <h2 class="text-4xl font-bold text-white mb-4">Welcome to OpenPBBG</h2>
    
    <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">
        This installer will guide you through setting up your game engine in just a few minutes.
        No technical knowledge required!
    </p>

    <div class="bg-white/5 rounded-xl p-6 mb-8 max-w-2xl mx-auto text-left">
        <h3 class="text-xl font-semibold text-white mb-4">Before you begin, make sure you have:</h3>
        <ul class="space-y-3 text-gray-300">
            <li class="flex items-start">
                <span class="text-green-400 mr-3 mt-1">✓</span>
                <span>A MySQL or MariaDB database created</span>
            </li>
            <li class="flex items-start">
                <span class="text-green-400 mr-3 mt-1">✓</span>
                <span>Database username and password</span>
            </li>
            <li class="flex items-start">
                <span class="text-green-400 mr-3 mt-1">✓</span>
                <span>Your desired admin email and password</span>
            </li>
            <li class="flex items-start">
                <span class="text-green-400 mr-3 mt-1">✓</span>
                <span>5-10 minutes of your time</span>
            </li>
        </ul>
    </div>

    <a href="{{ route('installer.requirements') }}" 
       class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
        Let's Get Started →
    </a>
</div>
@endsection
